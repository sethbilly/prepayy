<?php

use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Jobs\IsEligibleForLoanJob;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class IsEligibleForLoanJobTest extends TestCase
{
    private function getRequest(Employer $employer): Request
    {
        return $this->getAuthenticatedRequest([
            'employer_id' => $employer->id
        ]);
    }

    public function test_is_eligible_for_loan_from_partner_employer()
    {
        $emp = factory(Employer::class)->create();
        $partner = factory(FinancialInstitution::class)->create();

        $partner->partnerEmployers()->attach($emp->id);

        $this->assertTrue(dispatch(new IsEligibleForLoanJob($this->getRequest($emp), $partner)));
    }

    public function test_is_illeligible_for_loan_from_non_partner_employer()
    {
        $emp = factory(Employer::class)->create();
        $partner = factory(FinancialInstitution::class)->create();

        $this->assertFalse(dispatch(new IsEligibleForLoanJob($this->getRequest($emp), $partner)));
    }
}
