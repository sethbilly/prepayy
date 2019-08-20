<?php

use App\Entities\ApprovalLevel;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\CanApproveLoanApplicationJob;
use Illuminate\Support\Collection;

class CanApproveLoanApplicationJobTest extends TestCase
{
    use CreatesUsersTrait;

    /**
     * @var LoanApplication
     */
    private $application;

    public function setUp()
    {
        parent::setUp();
        $this->application = factory(LoanApplication::class)->create();
    }

    private function createUser($institutable): User
    {
        return factory(User::class)->create([
            'institutable_id' => $institutable->id,
            'institutable_type' => $institutable->getMorphClass()
        ]);
    }

    private function createLoanApprover($institutable): User
    {
        $user = $this->createUser($institutable);

        $permission = Permission::where('name', 'approve-loan-application')->first();
        $role = factory(Role::class)->create();
        $role->attachPermission($permission);

        $user->attachRole($role);

        return $user;
    }

    private function createApprovalLevels($institutable, $numLevels = 2): Collection
    {
        return factory(ApprovalLevel::class, $numLevels)->create([
            'institutable_id' => $institutable->id,
            'institutable_type' => $institutable->getMorphClass()
        ]);
    }

    private function updateLoanStatus(LoanApplicationStatus $status)
    {
        $this->application->loanApplicationStatus()->associate($status);
        $this->application->save();
    }

    public function test_returns_false_if_not_pending_employer_approval()
    {
        $user = $this->createUser($this->application->employer);
        $this->updateLoanStatus(LoanApplicationStatus::getDraftStatus());

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertFalse($canApprove);
    }

    public function test_returns_false_if_not_pending_partner_approval()
    {
        $user = $this->createUser($this->application->loanProduct->institution);

        $this->updateLoanStatus(LoanApplicationStatus::getEmployerApprovedStatus());

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertFalse($canApprove);
    }
    
    public function test_returns_false_if_partner_user_is_not_an_approver()
    {
        $user = $this->createUser($this->application->loanProduct->institution);

        $this->updateLoanStatus(LoanApplicationStatus::getPartnerPendingStatus());

        $canApprove = dispatch(new CanApproveLoanApplicationJob($user, $this->application));

        $this->assertFalse($canApprove);
    }

    public function test_returns_false_if_employer_user_is_not_an_approver()
    {
        $user = $this->createUser($this->application->employer);

        $this->updateLoanStatus(LoanApplicationStatus::getEmployerPendingStatus());

        $canApprove = dispatch(new CanApproveLoanApplicationJob($user, $this->application));

        $this->assertFalse($canApprove);
    }

    public function test_returns_true_if_employer_has_no_approval_levels()
    {
        $user = $this->createLoanApprover($this->application->employer);

        $this->updateLoanStatus(LoanApplicationStatus::getEmployerPendingStatus());

        $canApprove = dispatch(new CanApproveLoanApplicationJob($user, $this->application));

        $this->assertTrue($canApprove);
    }

    public function test_returns_true_if_partner_has_no_approval_levels()
    {
        $user = $this->createLoanApprover(
            $this->application->loanProduct->institution
        );

        $this->updateLoanStatus(LoanApplicationStatus::getPartnerPendingStatus());

        $canApprove = dispatch(new CanApproveLoanApplicationJob($user, $this->application));

        $this->assertTrue($canApprove);
    }

    public function test_returns_false_if_employer_user_is_not_assigned_approval_level()
    {
        $this->createApprovalLevels($this->application->employer);
        $user = $this->createLoanApprover($this->application->employer);
        $this->updateLoanStatus(LoanApplicationStatus::getEmployerPendingStatus());

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertFalse($canApprove);
    }

    public function test_returns_false_if_partner_user_is_not_assigned_approval_level()
    {
        $this->createApprovalLevels($this->application->loanProduct->institution);
        $user = $this->createLoanApprover($this->application->loanProduct->institution);
        $this->updateLoanStatus(LoanApplicationStatus::getPartnerPendingStatus());

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertFalse($canApprove);
    }

    public function test_returns_false_if_not_pending_at_employer_users_approval_level()
    {
        $this->updateLoanStatus(LoanApplicationStatus::getEmployerPendingStatus());

        $levels = $this->createApprovalLevels($this->application->employer);

        $user = $this->createLoanApprover($this->application->employer);
        $user->approvalLevel()->associate($levels->first());
        $user->save();

        $user2 = $this->createLoanApprover($this->application->employer);
        $user2->approvalLevel()->associate($levels->first());
        $user2->save();

        $this->application->approvers()->attach($user2->id, [
            'loan_application_status_id' =>
                LoanApplicationStatus::getEmployerApprovedStatus()->id
        ]);

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        // Application has been approved at the user's approval level
        $this->assertFalse($canApprove);
    }

    public function test_returns_false_if_not_pending_at_partner_users_approval_level()
    {
        $this->updateLoanStatus(LoanApplicationStatus::getPartnerPendingStatus());

        $levels = $this->createApprovalLevels($this->application->loanProduct->institution);

        $user = $this->createLoanApprover($this->application->loanProduct->institution);
        $user->approvalLevel()->associate($levels->first());
        $user->save();

        // Mark application as approved/declined at user's approval level
        $user2 = $this->createLoanApprover($this->application->loanProduct->institution);
        $user2->approvalLevel()->associate($levels->first());
        $user2->save();

        $this->application->approvers()->attach($user2->id, [
            'loan_application_status_id' =>
                LoanApplicationStatus::getPartnerApprovedStatus()->id
        ]);

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertFalse($canApprove);
    }

    public function test_returns_true_if_employer_has_no_prior_approval_levels()
    {
        $levels = $this->createApprovalLevels($this->application->employer);

        $user = $this->createLoanApprover($this->application->employer);
        $user->approvalLevel()->associate($levels->first());
        $user->save();

        $this->updateLoanStatus(LoanApplicationStatus::getEmployerPendingStatus());

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertTrue($canApprove);
    }

    public function test_returns_true_if_partner_has_no_prior_approval_levels()
    {
        $levels = $this->createApprovalLevels($this->application->loanProduct->institution);

        $user = $this->createLoanApprover($this->application->loanProduct->institution);
        $user->approvalLevel()->associate($levels->first());
        $user->save();

        $this->updateLoanStatus(LoanApplicationStatus::getPartnerPendingStatus());

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertTrue($canApprove);
    }

    public function test_returns_false_if_not_approved_by_prior_employer_levels()
    {
        $this->updateLoanStatus(LoanApplicationStatus::getEmployerPendingStatus());

        $levels = $this->createApprovalLevels($this->application->employer);

        $user = $this->createLoanApprover($this->application->employer);
        $user->approvalLevel()->associate($levels->last());
        $user->save();

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertFalse($canApprove);
    }

    public function test_returns_false_if_declined_by_prior_employer_level()
    {
        $this->updateLoanStatus(LoanApplicationStatus::getEmployerPendingStatus());

        $levels = $this->createApprovalLevels($this->application->employer);

        $user = $this->createLoanApprover($this->application->employer);
        $user->approvalLevel()->associate($levels->last());
        $user->save();

        // Mark application as approved/declined at user's approval level
        $user2 = $this->createLoanApprover($this->application->employer);
        $user2->approvalLevel()->associate($levels->first());
        $user2->save();

        $this->application->approvers()->attach($user2->id, [
            'loan_application_status_id' =>
                LoanApplicationStatus::getEmployerDeclinedStatus()->id
        ]);

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertFalse($canApprove);
    }

    public function test_returns_false_if_not_approved_by_prior_partner_levels()
    {
        $this->updateLoanStatus(LoanApplicationStatus::getPartnerPendingStatus());

        $levels = $this->createApprovalLevels($this->application->loanProduct->institution);

        $user = $this->createLoanApprover($this->application->loanProduct->institution);
        $user->approvalLevel()->associate($levels->last());
        $user->save();

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertFalse($canApprove);
    }

    public function test_returns_false_if_declined_by_prior_partner_level()
    {
        $this->updateLoanStatus(LoanApplicationStatus::getPartnerPendingStatus());

        $levels = $this->createApprovalLevels($this->application->loanProduct->institution);

        $user = $this->createLoanApprover($this->application->loanProduct->institution);
        $user->approvalLevel()->associate($levels->last());
        $user->save();

        // Mark application as approved/declined at user's approval level
        $user2 = $this->createLoanApprover($this->application->loanProduct->institution);
        $user2->approvalLevel()->associate($levels->first());
        $user2->save();

        $this->application->approvers()->attach($user2->id, [
            'loan_application_status_id' =>
                LoanApplicationStatus::getPartnerDeclinedStatus()->id
        ]);

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertFalse($canApprove);
    }

    public function test_returns_true_if_approved_by_employer_staff_at_prior_levels()
    {
        $this->updateLoanStatus(LoanApplicationStatus::getEmployerPendingStatus());

        $levels = $this->createApprovalLevels($this->application->employer);

        $user = $this->createLoanApprover($this->application->employer);
        $user->approvalLevel()->associate($levels->last());
        $user->save();

        // Mark application as approved/declined at user's approval level
        $user2 = $this->createLoanApprover($this->application->employer);
        $user2->approvalLevel()->associate($levels->first());
        $user2->save();

        $this->application->approvers()->attach($user2->id, [
            'loan_application_status_id' =>
                LoanApplicationStatus::getEmployerApprovedStatus()->id
        ]);

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertTrue($canApprove);
    }

    public function test_returns_true_if_approved_by_partner_staff_at_prior_levels()
    {
        $this->updateLoanStatus(LoanApplicationStatus::getPartnerPendingStatus());

        $levels = $this->createApprovalLevels($this->application->loanProduct->institution);

        $user = $this->createLoanApprover($this->application->loanProduct->institution);
        $user->approvalLevel()->associate($levels->last());
        $user->save();

        // Mark application as approved/declined at user's approval level
        $user2 = $this->createLoanApprover($this->application->loanProduct->institution);
        $user2->approvalLevel()->associate($levels->first());
        $user2->save();

        $this->application->approvers()->attach($user2->id, [
            'loan_application_status_id' =>
                LoanApplicationStatus::getPartnerApprovedStatus()->id
        ]);

        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($user, $this->application)
        );

        $this->assertTrue($canApprove);
    }
}
