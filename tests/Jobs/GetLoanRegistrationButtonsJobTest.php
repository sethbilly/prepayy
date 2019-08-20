<?php

use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Jobs\GetLoanRegistrationButtonsJob;

class GetLoanRegistrationButtonsJobTest extends TestCase
{
    public function test_will_return_employer_button_for_new_application()
    {
        $buttons = dispatch(new GetLoanRegistrationButtonsJob($this->getAuthenticatedRequest()));

        $this->assertCount(2, $buttons);
        $this->assertTrue($buttons[0]['label'] == 'Save draft');
        $this->assertTrue($buttons[0]['value'] == '');
        $this->assertTrue($buttons[0]['icons'] == 'fa-save');
        $this->assertTrue($buttons[0]['classes'] == 'btn-secondary');
        $this->assertTrue($buttons[1]['label'] == 'Request employers approval');
        $this->assertTrue($buttons[1]['value'] == 1);
        $this->assertTrue($buttons[1]['icons'] == '');
        $this->assertTrue($buttons[1]['classes'] == 'btn-success');
    }

    public function test_will_return_employer_button_for_application_pending_employer_approval()
    {
        $application = factory(LoanApplication::class)->make();
        $application->loanApplicationStatus()->associate(LoanApplicationStatus::getEmployerPendingStatus());
        $application->save();

        $req = $this->getAuthenticatedRequest([], $application->user);

        $buttons = dispatch(new GetLoanRegistrationButtonsJob($req, $application));

        $this->assertCount(2, $buttons);
        $this->assertTrue($buttons[0]['label'] == 'Save draft');
        $this->assertTrue($buttons[1]['label'] == 'Request employers approval');
    }

    public function test_will_return_partner_button_for_application_approved_by_employer()
    {
        $application = factory(LoanApplication::class)->make();
        $application->loanApplicationStatus()->associate(LoanApplicationStatus::getEmployerApprovedStatus());
        $application->save();

        $req = $this->getAuthenticatedRequest([], $application->user);

        $buttons = dispatch(new GetLoanRegistrationButtonsJob($req, $application));

        $this->assertCount(2, $buttons);
        $this->assertTrue($buttons[0]['label'] == 'Save draft');
        $this->assertTrue($buttons[0]['value'] == '');
        $this->assertTrue($buttons[0]['icons'] == 'fa-save');
        $this->assertTrue($buttons[0]['classes'] == 'btn-secondary');
        $this->assertTrue($buttons[1]['label'] == 'Submit application');
        $this->assertTrue($buttons[1]['value'] == 2);
        $this->assertTrue($buttons[1]['icons'] == '');
        $this->assertTrue($buttons[1]['classes'] == 'btn-success');
    }

    public function test_will_return_partner_button_for_application_pending_partner_approval()
    {
        $application = factory(LoanApplication::class)->make();
        $application->loanApplicationStatus()->associate(LoanApplicationStatus::getPartnerPendingStatus());
        $application->save();

        $req = $this->getAuthenticatedRequest([], $application->user);

        $buttons = dispatch(new GetLoanRegistrationButtonsJob($req, $application));

        $this->assertCount(2, $buttons);
        $this->assertTrue($buttons[0]['label'] == 'Save draft');
        $this->assertTrue($buttons[1]['label'] == 'Submit application');
    }

    public function test_will_return_no_buttons_for_employer_declined_application()
    {
        $application = factory(LoanApplication::class)->make();
        $application->loanApplicationStatus()->associate(LoanApplicationStatus::getEmployerDeclinedStatus());
        $application->save();

        $req = $this->getAuthenticatedRequest([], $application->user);

        $buttons = dispatch(new GetLoanRegistrationButtonsJob($req, $application));

        $this->assertCount(0, $buttons);
    }

    public function test_will_return_no_buttons_for_partner_declined_application()
    {
        $application = factory(LoanApplication::class)->make();
        $application->loanApplicationStatus()->associate(LoanApplicationStatus::getPartnerDeclinedStatus());
        $application->save();

        $req = $this->getAuthenticatedRequest([], $application->user);

        $buttons = dispatch(new GetLoanRegistrationButtonsJob($req, $application));

        $this->assertCount(0, $buttons);
    }

    public function test_will_return_no_buttons_for_disbursed_application()
    {
        $application = factory(LoanApplication::class)->make();
        $application->loanApplicationStatus()->associate(LoanApplicationStatus::getPartnerDisbursedStatus());
        $application->save();

        $req = $this->getAuthenticatedRequest([], $application->user);

        $buttons = dispatch(new GetLoanRegistrationButtonsJob($req, $application));

        $this->assertCount(0, $buttons);
    }
}
