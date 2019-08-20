<?php

use App\Entities\ApprovalLevel;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\GetLoanApprovalLevelLabel;
use Illuminate\Support\Collection;

class GetLoanApprovalLevelLabelTest extends TestCase
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

    private function createUser($institutable): User
    {
        return factory(User::class)->create([
            'institutable_id' => $institutable->id,
            'institutable_type' => $institutable->getMorphClass()
        ]);
    }

    private function createLoanApprover($institutable): User
    {
        $permission = Permission::where('name', 'approve-loan-application')->first();
        $role = factory(Role::class)->create();
        $role->attachPermission($permission);

        $user = $this->createUser($institutable);
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

    private function updateLoanApplicationStatus(LoanApplicationStatus $status
    ): LoanApplication {
        $this->application->loanApplicationStatus()->associate($status);
        $this->application->save();

        return $this->application;
    }

    private function approveAtUsersLevel(
        User $user,
        LoanApplicationStatus $statusApproved
    ) {
        $this->application->approvers()->attach($user->id, [
            'loan_application_status_id' => $statusApproved->id
        ]);
    }

    private function updateUsersApprovalLevel(User $user, ApprovalLevel $level): User
    {
        $user->approvalLevel()->associate($level);
        $user->save();

        return $user;
    }

    public function test_returns_null_if_loan_is_not_at_employer_level()
    {
        $status = LoanApplicationStatus::getPartnerPendingStatus();
        $this->updateLoanApplicationStatus($status);

        $user = $this->createUser($this->application->employer);
        $req = $this->getAuthenticatedRequest([], $user);
        $loanLabel = dispatch(new GetLoanApprovalLevelLabel($req, $this->application));

        $this->assertNull($loanLabel);
    }

    public function test_returns_null_if_loan_is_not_at_partner_level()
    {
        $status = LoanApplicationStatus::getEmployerDeclinedStatus();
        $this->updateLoanApplicationStatus($status);

        $user = $this->createUser($this->application->loanProduct->institution);
        $req = $this->getAuthenticatedRequest([], $user);
        $loanLabel = dispatch(new GetLoanApprovalLevelLabel($req, $this->application));

        $this->assertNull($loanLabel);
    }

    public function test_returns_null_if_employer_has_no_approval_levels()
    {
        $status = LoanApplicationStatus::getEmployerPendingStatus();
        $this->updateLoanApplicationStatus($status);

        $user = $this->createLoanApprover($this->application->employer);
        $req = $this->getAuthenticatedRequest([], $user);

        $loanLabel = dispatch(new GetLoanApprovalLevelLabel($req, $this->application));

        $this->assertNull($loanLabel);
    }

    public function test_returns_null_if_partner_has_no_approval_levels()
    {
        $status = LoanApplicationStatus::getPartnerPendingStatus();
        $this->updateLoanApplicationStatus($status);

        $user = $this->createLoanApprover($this->application->loanProduct->institution);
        $req = $this->getAuthenticatedRequest([], $user);

        $loanLabel = dispatch(new GetLoanApprovalLevelLabel($req, $this->application));

        $this->assertNull($loanLabel);
    }

    public function test_returns_pending_at_employer_approval_level()
    {
        $status = LoanApplicationStatus::getEmployerPendingStatus();
        $this->updateLoanApplicationStatus($status);

        $approvalLevels = $this->createApprovalLevels($this->application->employer);

        $user = $this->createLoanApprover($this->application->employer);
        $this->updateUsersApprovalLevel($user, $approvalLevels->last());

        $user2 = $this->createLoanApprover($this->application->employer);
        $this->updateUsersApprovalLevel($user2, $approvalLevels->first());

        $this->approveAtUsersLevel($user2,
            LoanApplicationStatus::getEmployerApprovedStatus());

        $req = $this->getAuthenticatedRequest([], $user);

        $loanLabel = dispatch(new GetLoanApprovalLevelLabel($req, $this->application));

        $this->assertInstanceOf(stdClass::class, $loanLabel);
        $this->assertEquals('Pending', $loanLabel->label);
        $this->assertEquals('at ' . $approvalLevels->last()->name, $loanLabel->level);
    }

    public function test_returns_pending_at_partner_approval_level()
    {
        $status = LoanApplicationStatus::getPartnerPendingStatus();
        $this->updateLoanApplicationStatus($status);

        $institution = $this->application->loanProduct->institution;
        $approvalLevels = $this->createApprovalLevels($institution);

        $user = $this->createLoanApprover($institution);
        $this->updateUsersApprovalLevel($user, $approvalLevels->last());

        $user2 = $this->createLoanApprover($institution);
        $this->updateUsersApprovalLevel($user2, $approvalLevels->first());

        $this->approveAtUsersLevel($user2,
            LoanApplicationStatus::getPartnerApprovedStatus());

        $req = $this->getAuthenticatedRequest([], $user);

        $loanLabel = dispatch(new GetLoanApprovalLevelLabel($req, $this->application));

        $this->assertInstanceOf(stdClass::class, $loanLabel);
        $this->assertEquals('Pending', $loanLabel->label);
        $this->assertEquals('at ' . $approvalLevels->last()->name, $loanLabel->level);
    }

    public function test_returns_declined_at_employer_approval_level()
    {
        $status = LoanApplicationStatus::getEmployerDeclinedStatus();
        $this->updateLoanApplicationStatus($status);

        $approvalLevels = $this->createApprovalLevels($this->application->employer);

        $user = $this->createLoanApprover($this->application->employer);
        $this->updateUsersApprovalLevel($user, $approvalLevels->last());

        $user2 = $this->createLoanApprover($this->application->employer);
        $this->updateUsersApprovalLevel($user2, $approvalLevels->first());

        $this->approveAtUsersLevel($user2,
            LoanApplicationStatus::getEmployerDeclinedStatus());

        $req = $this->getAuthenticatedRequest([], $user);

        $loanLabel = dispatch(new GetLoanApprovalLevelLabel($req, $this->application));

        $this->assertInstanceOf(stdClass::class, $loanLabel);
        $this->assertEquals('Declined', $loanLabel->label);
        $this->assertEquals('at ' . $approvalLevels->first()->name, $loanLabel->level);
    }

    public function test_returns_declined_at_partner_approval_level()
    {
        $status = LoanApplicationStatus::getPartnerDeclinedStatus();
        $this->updateLoanApplicationStatus($status);

        $institution = $this->application->loanProduct->institution;
        $approvalLevels = $this->createApprovalLevels($institution);

        $user = $this->createLoanApprover($institution);
        $this->updateUsersApprovalLevel($user, $approvalLevels->last());

        $user2 = $this->createLoanApprover($institution);
        $this->updateUsersApprovalLevel($user2, $approvalLevels->first());

        $this->approveAtUsersLevel($user2,
            LoanApplicationStatus::getPartnerDeclinedStatus());

        $req = $this->getAuthenticatedRequest([], $user);

        $loanLabel = dispatch(new GetLoanApprovalLevelLabel($req, $this->application));

        $this->assertInstanceOf(stdClass::class, $loanLabel);
        $this->assertEquals('Declined', $loanLabel->label);
        $this->assertEquals('at ' . $approvalLevels->first()->name, $loanLabel->level);
    }
}
