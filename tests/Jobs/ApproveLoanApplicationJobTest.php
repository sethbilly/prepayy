<?php

use App\Entities\ApprovalLevel;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\ApproveLoanApplicationJob;
use App\Notifications\LoanApplicationDisbursed;
use App\Notifications\LoanApprovalRequest;
use App\Notifications\LoanApprovedByEmployer;
use App\Notifications\LoanApprovedByFinancialInstitution;
use App\Notifications\LoanDeclinedByEmployer;
use App\Notifications\LoanDeclinedByFinancialInstitution;
use Illuminate\Support\Facades\Notification;

class ApproveLoanApplicationJobTest extends TestCase
{

    /**
     * @var LoanApplication
     */
    private $application;

    public function setUp()
    {
        parent::setUp();
        $this->application = factory(LoanApplication::class)->create();
    }

    /**
     * @param string $type
     * @param array $data
     * @return User
     */
    private function createLoanApprover(string $type, array $data = []): User
    {
        $user = factory(User::class, $type)->create($data);

        $role = factory(Role::class)->create();
        $role->attachPermission(Permission::where('name', 'approve-loan-application')->first());

        $user->attachRole($role);

        return $user;
    }

    public function test_will_notify_borrower_if_approved_by_no_approval_level_employer()
    {
        // Create a loan approver not explicitly assigned an approval level because the employer has 0 approval levels
        $user = $this->createLoanApprover('employer', ['institutable_id' => $this->application->employer_id]);

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getEmployerApprovedStatus()->id
        ], $user);

        $this->expectsNotification($this->application->user, LoanApprovedByEmployer::class);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        $this->assertTrue($this->application->loanApplicationStatus->isEmployerApproved());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);
    }
    
    public function test_will_notify_borrower_if_approved_by_single_approval_level_employer()
    {
        /*
         * Add single approval level for the employer
         * Assign the loan approver to this approval level
         */
        $user = $this->createLoanApprover('employer', ['institutable_id' => $this->application->employer_id]);
        $level = factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);
        $user->approvalLevel()->associate($level);
        $user->save();

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getEmployerApprovedStatus()->id
        ], $user);

        $this->expectsNotification($this->application->user, LoanApprovedByEmployer::class);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        $this->assertTrue($this->application->loanApplicationStatus->isEmployerApproved());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);
    }

    public function test_will_notify_borrower_if_declined_by_single_approval_level_employer()
    {
        /*
         * Add single approval level for the employer
         * Assign the loan approver to this approval level
         */
        $user = $this->createLoanApprover('employer', ['institutable_id' => $this->application->employer_id]);
        $level = factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);
        $user->approvalLevel()->associate($level);
        $user->save();

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getEmployerDeclinedStatus()->id
        ], $user);

        $this->expectsNotification($this->application->user, LoanDeclinedByEmployer::class);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        $this->assertTrue($this->application->loanApplicationStatus->isEmployerDeclined());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);
    }

    public function test_will_notify_next_approvers_if_approved_by_multi_approval_level_employer()
    {
        Notification::fake();
        /*
         * Add single approval level for the employer
         * Assign the loan approver to this approval level
         */
        $user = $this->createLoanApprover('employer', ['institutable_id' => $this->application->employer_id]);
        $levelTwoStaff = $this->createLoanApprover('employer', ['institutable_id' => $this->application->employer_id]);

        $levels = factory(ApprovalLevel::class, 2)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);
        // Assign the approval levels
        $user->approvalLevel()->associate($levels->first());
        $user->save();

        $levelTwoStaff->approvalLevel()->associate($levels->last());
        $levelTwoStaff->save();

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getEmployerApprovedStatus()->id
        ], $user);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        // Assert that the loan status is still draft
        $this->assertTrue($this->application->loanApplicationStatus->isDraft());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);

        Notification::assertSentTo($levelTwoStaff, LoanApprovalRequest::class);
    }

    public function test_will_notify_borrower_if_declined_by_multi_approval_level_employer()
    {
        /*
         * Add single approval level for the employer
         * Assign the loan approver to this approval level
         */
        $user = $this->createLoanApprover('employer', ['institutable_id' => $this->application->employer_id]);
        $levelTwoStaff = $this->createLoanApprover('employer', ['institutable_id' => $this->application->employer_id]);

        $levels = factory(ApprovalLevel::class, 2)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);
        // Assign the approval levels
        $user->approvalLevel()->associate($levels->first());
        $user->save();

        $levelTwoStaff->approvalLevel()->associate($levels->last());
        $levelTwoStaff->save();

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getEmployerDeclinedStatus()->id
        ], $user);

        $this->expectsNotification($this->application->user, LoanDeclinedByEmployer::class);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        // Assert that the loan status is still draft
        $this->assertTrue($this->application->loanApplicationStatus->isEmployerDeclined());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);
    }

    public function test_will_notify_borrower_if_approved_by_single_approval_level_institution()
    {
        /*
         * Add single approval level for the financial institution
         * Assign the loan approver to this approval level
         */
        $user = $this->createLoanApprover('partner', [
            'institutable_id' => $this->application->loanProduct->institution->id
        ]);
        $level = factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);
        $user->approvalLevel()->associate($level);
        $user->save();

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getPartnerApprovedStatus()->id
        ], $user);

        $this->expectsNotification($this->application->user, LoanApprovedByFinancialInstitution::class);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        $this->assertTrue($this->application->loanApplicationStatus->isPartnerApproved());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);
    }

    // TESTS FOR FINANCIAL INSTITUTIONS
    public function test_will_notify_borrower_if_declined_by_single_approval_level_institution()
    {
        /*
         * Add single approval level for the financial institution
         * Assign the loan approver to this approval level
         */
        $user = $this->createLoanApprover('partner', [
            'institutable_id' => $this->application->loanProduct->institution->id
        ]);
        $level = factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);
        $user->approvalLevel()->associate($level);
        $user->save();

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getPartnerDeclinedStatus()->id
        ], $user);

        $this->expectsNotification($this->application->user, LoanDeclinedByFinancialInstitution::class);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        $this->assertTrue($this->application->loanApplicationStatus->isPartnerDeclined());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);
    }

    public function test_will_notify_next_approvers_if_approved_by_multi_approval_level_institution()
    {
        Notification::fake();
        /*
         * Add single approval level for the employer
         * Assign the loan approver to this approval level
         */
        $user = $this->createLoanApprover('partner', [
            'institutable_id' => $this->application->loanProduct->institution->id
        ]);
        $levelTwoStaff = $this->createLoanApprover('partner', ['institutable_id' => $user->institutable_id]);

        $levels = factory(ApprovalLevel::class, 2)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);
        // Assign the approval levels
        $user->approvalLevel()->associate($levels->first());
        $user->save();

        $levelTwoStaff->approvalLevel()->associate($levels->last());
        $levelTwoStaff->save();

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getPartnerApprovedStatus()->id
        ], $user);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        // Assert that the loan status is still draft
        $this->assertTrue($this->application->loanApplicationStatus->isDraft());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);

        Notification::assertSentTo($levelTwoStaff, LoanApprovalRequest::class);
        Notification::assertNotSentTo($user, LoanApprovalRequest::class);
    }

    public function test_will_notify_borrower_if_declined_by_multi_approval_level_institution()
    {
        /*
         * Add single approval level for the employer
         * Assign the loan approver to this approval level
         */
        $user = $this->createLoanApprover('partner', [
            'institutable_id' => $this->application->loanProduct->institution->id
        ]);
        $levelTwoStaff = $this->createLoanApprover('partner', ['institutable_id' => $user->institutable_id]);

        $levels = factory(ApprovalLevel::class, 2)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);
        // Assign the approval levels
        $user->approvalLevel()->associate($levels->first());
        $user->save();

        $levelTwoStaff->approvalLevel()->associate($levels->last());
        $levelTwoStaff->save();

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getPartnerDeclinedStatus()->id
        ], $user);

        $this->expectsNotification($this->application->user, LoanDeclinedByFinancialInstitution::class);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        // Assert that the loan status is still draft
        $this->assertTrue($this->application->loanApplicationStatus->isPartnerDeclined());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);
    }

    public function test_will_notify_borrower_if_disbursed_by_institution()
    {
        /*
         * Add single approval level for the employer
         * Assign the loan approver to this approval level
         */
        $user = $this->createLoanApprover('partner', [
            'institutable_id' => $this->application->loanProduct->institution->id
        ]);

        $level = factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);
        // Assign the approval levels
        $user->approvalLevel()->associate($level);
        $user->save();

        $req = $this->getAuthenticatedRequest([
            'status_id' => LoanApplicationStatus::getPartnerDisbursedStatus()->id
        ], $user);

        $this->expectsNotification($this->application->user, LoanApplicationDisbursed::class);

        dispatch(new ApproveLoanApplicationJob($req, $this->application));

        // Assert that the loan status is still draft
        $this->assertTrue($this->application->loanApplicationStatus->isDisbursed());
        $this->assertCount(1, $this->application->approvers);
        $this->assertEquals($user->id, $this->application->approvers->first()->id);
    }
}
