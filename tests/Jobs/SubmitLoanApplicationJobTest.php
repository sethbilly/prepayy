<?php

use App\Entities\Employer;
use App\Entities\Guarantor;
use App\Entities\IdentificationCard;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\LoanProduct;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\SubmitLoanApplicationJob;
use App\Notifications\LoanApprovalRequest;
use App\Notifications\PartnerApprovalRequest;
use Illuminate\Support\Facades\Notification;

class SubmitLoanApplicationJobTest extends TestCase
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->faker = Faker\Factory::create();
    }

    public function test_can_save_loan_application_as_draft()
    {
        $basic = $this->getBasicDetails();
        $guarantor = $this->getGuarantorDetails();
        $req = $this->getAuthenticatedRequest(
            array_merge(
                $basic,
                $guarantor,
                ['amount' => 200, 'tenure' => 2]
            ),
            $this->user);
        $product = factory(LoanProduct::class)->create();

        $application = dispatch(new SubmitLoanApplicationJob($req, $product));

        $this->assertInstanceOf(LoanApplication::class, $application);
        $this->assertInstanceOf(User::class, $application->user);
        $this->assertInstanceOf(Guarantor::class, $application->guarantor);
        $this->assertInstanceOf(LoanApplicationStatus::class,
            $application->loanApplicationStatus);

        $this->assertEquals(LoanApplicationStatus::STATUS['DRAFT_SAVED'],
            $application->loanApplicationStatus->status);
        $this->assertEquals($req->input('user.firstname'), $application->user->firstname);
        $this->assertEquals($req->input('user.lastname'), $application->user->lastname);
        $this->assertEquals($req->input('user.contact_number'),
            $application->user->contact_number);
        $this->assertEquals($req->input('guarantor.contact_number'),
            $application->guarantor->contact_number);
        $this->assertEquals($req->input('guarantor.name'), $application->guarantor->name);
        $this->assertEquals($req->input('guarantor.employer'),
            $application->guarantor->employer);
        $this->assertEquals($req->input('amount'), $application->amount);
        $this->assertEquals($req->input('tenure'), $application->tenure_in_years);
        $this->assertEquals($product->interest_per_year, $application->interest_per_year);

        $this->assertNull($application->identificationCard);
        $this->assertNull($application->employer);
    }

    private function getBasicDetails(): array
    {
        return [
            'user' => [
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastName,
                'othernames' => $this->faker->name,
                'contact_number' => $this->faker->phoneNumber,
                'dob' => $this->faker->date(),
                'ssnit' => $this->faker->creditCardNumber()
            ]
        ];
    }

    private function getGuarantorDetails(): array
    {
        return [
            'guarantor' => [
                'name' => $this->faker->name,
                'contact_number' => $this->faker->phoneNumber,
                'employer' => $this->faker->company,
                'position' => $this->faker->jobTitle,
                'years_known' => 2,
                'relationship' => $this->faker->text
            ]
        ];
    }

    public function test_can_submit_loan_application_for_employer_approval()
    {
        $basic = $this->getBasicDetails();
        $guarantor = $this->getGuarantorDetails();
        $employer = $this->getEmploymentDetails();
        $card = $this->getCardDetails();
        $data = array_merge($basic, $guarantor, $employer, $card);
        $data['submit'] = 1;

        $req = $this->getAuthenticatedRequest($data, $this->user);
        $product = factory(LoanProduct::class)->create();

        $application = dispatch(new SubmitLoanApplicationJob($req, $product));

        $this->assertInstanceOf(LoanApplication::class, $application);
        $this->assertInstanceOf(User::class, $application->user);
        $this->assertInstanceOf(Guarantor::class, $application->guarantor);
        $this->assertInstanceOf(Employer::class, $application->employer);
        $this->assertInstanceOf(IdentificationCard::class,
            $application->identificationCard);
        $this->assertInstanceOf(LoanApplicationStatus::class,
            $application->loanApplicationStatus);

        $this->assertEquals(
            LoanApplicationStatus::STATUS['EMPLOYER_PENDING'],
            $application->loanApplicationStatus->status
        );

        $this->assertEquals($req->input('user.firstname'), $application->user->firstname);
        $this->assertEquals($req->input('guarantor.name'), $application->guarantor->name);
        $this->assertEquals($req->input('employer.id'), $application->employer->id);
        $this->assertEquals($req->input('id_card.type'),
            $application->identificationCard->type);
    }

    private function getEmploymentDetails(): array
    {
        return [
            'employer' => [
                'id' => factory(Employer::class)->create()->id,
                'contract_type' => 'Full Time',
                'position' => $this->faker->company
            ]
        ];
    }

    private function getCardDetails(): array
    {
        return [
            'id_card' => [
                'type' => $this->faker->text,
                'number' => $this->faker->creditCardNumber,
                'issue_date' => $this->faker->date(),
                'expiry_date' => $this->faker->date()
            ]
        ];
    }

    public function test_can_submit_drafted_loan_application()
    {
        // Create a draft loan application
        $application = factory(LoanApplication::class)->create(['user_id' => $this->user->id]);

        $basic = $this->getBasicDetails();
        $guarantor = $this->getGuarantorDetails();
        $employer = $this->getEmploymentDetails();
        $card = $this->getCardDetails();
        $data = array_merge($basic, $guarantor, $employer, $card);
        $data['submit'] = 1;

        $req = $this->getAuthenticatedRequest($data, $this->user);
        $req->merge(['loan_application_id' => $application->id]);
        $product = factory(LoanProduct::class)->create();

        $application = dispatch(new SubmitLoanApplicationJob($req, $product));

        $this->assertInstanceOf(LoanApplication::class, $application);
        $this->assertInstanceOf(User::class, $application->user);
        $this->assertInstanceOf(Guarantor::class, $application->guarantor);
        $this->assertInstanceOf(Employer::class, $application->employer);
        $this->assertInstanceOf(IdentificationCard::class,
            $application->identificationCard);
        $this->assertInstanceOf(LoanApplicationStatus::class,
            $application->loanApplicationStatus);

        $this->assertEquals(LoanApplicationStatus::STATUS['EMPLOYER_PENDING'],
            $application->loanApplicationStatus->status);

        $this->assertEquals($req->input('user.firstname'), $application->user->firstname);
        $this->assertEquals($req->input('guarantor.name'), $application->guarantor->name);
        $this->assertEquals($req->input('employer.id'), $application->employer->id);
        $this->assertEquals($req->input('id_card.type'),
            $application->identificationCard->type);
    }

    public function test_will_notify_employer_of_request_for_approval()
    {
        $employerDetails = $this->getEmploymentDetails();

        $employer = Employer::find($employerDetails['employer']['id']);
        // Add 2 user's with loan approval permission for the employer
        $role = factory(Role::class)->create();
        $perm = Permission::where('name', 'approve-loan-application')->first();
        $role->attachPermission($perm);

        $users = factory(User::class, 'employer', 2)
            ->create([
                'institutable_id' => $employer->id
            ])
            ->map(function (User $user) use ($role) {
                $user->attachRole($role);

                return $user;
            });

        $basic = $this->getBasicDetails();
        $guarantor = $this->getGuarantorDetails();
        $card = $this->getCardDetails();
        $data = array_merge($basic, $guarantor, $employerDetails, $card);
        $data['submit'] = 1;

        $req = $this->getAuthenticatedRequest($data, $this->user);
        $product = factory(LoanProduct::class)->create();

        Notification::fake();

        $application = dispatch(new SubmitLoanApplicationJob($req, $product));

        $this->assertTrue($application->loanApplicationStatus->isPendingEmployerApproval());
        Notification::assertSentTo($users->all(), LoanApprovalRequest::class);
    }

    public function test_can_submit_application_for_partner_approval()
    {
        $application = factory(LoanApplication::class)->create();

        $employerDetails = $this->getEmploymentDetails();
        $basic = $this->getBasicDetails();
        $guarantor = $this->getGuarantorDetails();
        $card = $this->getCardDetails();
        $data = array_merge($basic, $guarantor, $employerDetails, $card);
        $data['submit'] = 2;
        $data['loan_application_id'] = $application->id;

        $req = $this->getAuthenticatedRequest($data, $application->user);

        Notification::fake();

        $this->expectsNotification($application->user, PartnerApprovalRequest::class);
        $rec = dispatch(new SubmitLoanApplicationJob($req, $application->loanProduct));

        // The application status should not be changed
        $this->assertFalse($rec->loanApplicationStatus->isPendingPartnerApproval());

        $this->assertTrue(cache()->has(SubmitLoanApplicationJob::PARTNER_SUBMIT_TOKEN_KEY));

        $cacheEntry = cache()->get(SubmitLoanApplicationJob::PARTNER_SUBMIT_TOKEN_KEY);
        $this->assertTrue(is_array($cacheEntry));
        $this->assertArrayHasKey('token', $cacheEntry);
        $this->assertArrayHasKey('application_id', $cacheEntry);
        $this->assertEquals($application->id, $cacheEntry['application_id']);
    }
}
