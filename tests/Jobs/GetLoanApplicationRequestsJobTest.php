<?php

use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\User;
use App\Jobs\GetLoanApplicationRequestsJob;
use Illuminate\Database\Eloquent\Collection;

class GetLoanApplicationRequestsJobTest extends TestCase
{
    use CreatesUsersTrait;

    /**
     * @var Collection
     */
    private $applications;


    public function setUp()
    {
        parent::setUp();

        // Create 4 applications scattered across 2 borrowers, employers and partners
        $partners = factory(FinancialInstitution::class, 2)->create();
        $employers = factory(Employer::class, 2)->create();
        $users = factory(User::class, 2)->create();

        $this->applications = factory(LoanApplication::class, 4)->create()
            ->map(function (LoanApplication $application, $i) use (
                $partners,
                $employers,
                $users
            ) {
                // Product should belong to one of our desired mercharts
                $application->loanProduct->institution()->associate($partners->get($i < 2 ? 0 : 1));
                $application->loanProduct->save();

                $application->employer()->associate($employers->get($i < 2 ? 0 : 1));
                $application->user()->associate($users->get($i < 2 ? 0 : 1));

                $application->save();

                return $application;
            });
    }

    public function test_can_get_applications_for_borrower()
    {
        $req = $this->getAuthenticatedRequest([], $this->applications->first()->user);

        $results = dispatch(new GetLoanApplicationRequestsJob($req));

        $expected = $this->applications->filter(function (LoanApplication $application) {
            return $application->user_id === $this->applications->first()->user_id;
        });

        $this->assertCount(2, $expected);
        $this->assertCount(2, $results);

        $expected->each(function (LoanApplication $application, $i) use ($results) {
            $this->assertEquals($application->id, $results->items()[$i]->id);
        });
    }

    public function test_cannot_get_draft_applications_for_employer()
    {
        $user = $this->createEmployerAccountOwner($this->applications->first()->employer);

        $req = $this->getAuthenticatedRequest([], $user);

        // Applications which are still in draft status are inaccessible to employers
        $results = dispatch(new GetLoanApplicationRequestsJob($req));

        $this->assertCount(0, $results);
    }

    public function test_can_get_applications_for_employer()
    {
        $user = $this->createEmployerAccountOwner($this->applications->first()->employer);

        $req = $this->getAuthenticatedRequest([], $user);

        // Applications submitted for approval are accessible to employers
        $expected = $this->applications
            ->filter(function (LoanApplication $application) {
                return $application->employer_id === $this->applications->first()->employer_id;
            })
            ->map(function (LoanApplication $application) {
                $application->loanApplicationStatus()->associate(LoanApplicationStatus::getEmployerPendingStatus());
                $application->save();

                return $application;
            });

        $results = dispatch(new GetLoanApplicationRequestsJob($req));

        $this->assertCount(2, $expected);
        $this->assertCount(2, $results);

        $expected->each(function (LoanApplication $application, $i) use ($results) {
            $this->assertEquals($application->id, $results->items()[$i]->id);
        });
    }

    public function test_cannot_get_employer_unapproved_applications_for_partner()
    {
        $user = $this->createInstitutionAccountOwner($this->applications->first()->loanProduct->institution);

        $req = $this->getAuthenticatedRequest([], $user);

        $results = dispatch(new GetLoanApplicationRequestsJob($req));

        $this->assertCount(0, $results);
    }

    public function test_can_get_applications_for_partner()
    {
        $user = $this->createInstitutionAccountOwner(
            $this->applications->first()->loanProduct->institution
        );

        $req = $this->getAuthenticatedRequest([], $user);

        // Mark the application as pending partner approval
        $expected = $this->applications
            ->filter(function (LoanApplication $application) use ($user) {
                return $application->loanProduct->institution->id === $user->institutable_id;
            })
            ->map(function (LoanApplication $application) {
                $application->loanApplicationStatus()->associate(
                    LoanApplicationStatus::getPartnerPendingStatus()
                );
                $application->save();

                return $application;
            });

        $results = dispatch(new GetLoanApplicationRequestsJob($req));

        $this->assertCount(2, $expected);
        $this->assertCount(2, $results);

        $expected->each(function (LoanApplication $application, $i) use ($results) {
            $this->assertEquals($application->id, $results->items()[$i]->id);
        });
    }

    public function test_can_search_loans_by_employer()
    {
        $req = $this->getAuthenticatedRequest([
            'employer_id' => $this->applications->first()->employer_id
        ], $this->applications->first()->user);

        $expected = $this->applications
            ->filter(function (LoanApplication $application) {
                return $application->user_id === $this->applications->first()->user_id &&
                    $application->employer_id === $this->applications->first()->employer_id;
            });

        $results = dispatch(new GetLoanApplicationRequestsJob($req));

        $this->assertCount(2, $expected);
        $this->assertCount(2, $results);

        $expected->each(function (LoanApplication $application, $i) use ($results) {
            $this->assertEquals($application->id, $results->items()[$i]->id);
        });

        // Test the reverse scenario
        $req = $this->getAuthenticatedRequest([
            'employer_id' => $this->applications->last()->employer_id
        ], $this->applications->first()->user);

        $results = dispatch(new GetLoanApplicationRequestsJob($req));

        $this->assertCount(0, $results);
    }

    public function test_can_search_loans_by_borrower()
    {
        $user = $this->createEmployerAccountOwner($this->applications->first()->employer);

        $req = $this->getAuthenticatedRequest([
            'user_id' => $this->applications->first()->user_id
        ], $user);

        $expected = $this->applications
            ->filter(function (LoanApplication $application) {
                return $application->user_id === $this->applications->first()->user_id &&
                    $application->employer_id === $this->applications->first()->employer_id;
            })
            ->map(function (LoanApplication $application) {
                $application->loanApplicationStatus()->associate(LoanApplicationStatus::getPartnerDisbursedStatus());
                $application->save();

                return $application;
            });

        $results = dispatch(new GetLoanApplicationRequestsJob($req));

        $this->assertCount(2, $expected);
        $this->assertCount(2, $results);

        $expected->each(function (LoanApplication $application, $i) use ($results) {
            $this->assertEquals($application->id, $results->items()[$i]->id);
        });

        // Test the reverse scenario
        $req2 = $this->getAuthenticatedRequest([
            'borrower_id' => factory(User::class)->create()->id
        ], $user);

        $results2 = dispatch(new GetLoanApplicationRequestsJob($req2));

        $this->assertCount(0, $results2);
    }

    public function test_can_search_loans_by_institution()
    {
        $user = $this->createEmployerAccountOwner($this->applications->first()->employer);

        $req = $this->getAuthenticatedRequest([
            'institution_id' => $this->applications->first()->loanProduct->institution_id
        ], $user);

        $expected = $this->applications
            ->filter(function (LoanApplication $application) {
                return $application->employer_id === $this->applications->first()->employer_id &&
                    $application->loanProduct->institution_id === $this->applications->first()->loanProduct->institution_id;
            })
            ->map(function (LoanApplication $application) {
                $application->loanApplicationStatus()->associate(LoanApplicationStatus::getPartnerDeclinedStatus());
                $application->save();

                return $application;
            });

        $results = dispatch(new GetLoanApplicationRequestsJob($req));

        $this->assertCount(2, $expected);
        $this->assertCount(2, $results);

        $expected->each(function (LoanApplication $application, $i) use ($results) {
            $this->assertEquals($application->id, $results->items()[$i]->id);
        });

        // Test the reverse scenario
        $req2 = $this->getAuthenticatedRequest([
            'institution_id' => factory(FinancialInstitution::class)->create()->id
        ], $user);

        $results2 = dispatch(new GetLoanApplicationRequestsJob($req2));

        $this->assertCount(0, $results2);
    }
}
