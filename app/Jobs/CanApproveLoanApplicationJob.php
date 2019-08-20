<?php

namespace App\Jobs;

use App\Entities\ApprovalLevel;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\User;

class CanApproveLoanApplicationJob
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var LoanApplication
     */
    private $application;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param LoanApplication $application
     */
    public function __construct(User $user, LoanApplication $application)
    {
        $this->user = $user;
        $this->application = $application;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return $this->canApproveLoanApplication();
    }

    /**
     * Determine if user can approve the loan application
     * @return bool
     */
    private function canApproveLoanApplication(): bool
    {
        if ($this->user->isEmployerStaff()) {
            return $this->canApproveLoanApplicationAsEmployer();
        }

        return $this->user->isEmployerStaff() ?
            $this->canApproveLoanApplicationAsEmployer() :
            $this->canApproveLoanApplicationAsPartner();
    }

    /**
     * Determine if employer staff can approve a loan application
     * @return bool
     */
    private function canApproveLoanApplicationAsEmployer(): bool
    {
        $status = $this->application->getLoanApplicationStatus();

        if (!($status->isPendingEmployerApproval() ||
            $status->isEmployerRequestForInformation())
        ) {
            return false;
        }

        if (!$this->hasApprovalLevels()) {
            return $this->user->isLoanApprover();
        }

        return $this->isAssignedToApprovalLevel() &&
            $this->isPendingApprovalAtUsersApprovalLevel() &&
            $this->hasBeenApprovedAtPriorLevels();
    }

    /**
     * Determine if partner staff can approve a loan application
     * @return bool
     */
    private function canApproveLoanApplicationAsPartner(): bool
    {
        $status = $this->application->getLoanApplicationStatus();

        if (!($status->isPendingPartnerApproval() ||
            $status->isPartnerRequestForInformation())
        ) {
            return false;
        }

        if (!$this->hasApprovalLevels()) {
            return $this->user->isLoanApprover();
        }

        return $this->isAssignedToApprovalLevel() &&
            $this->isPendingApprovalAtUsersApprovalLevel() &&
            $this->hasBeenApprovedAtPriorLevels();
    }

    /**
     * Returns true if institution (user belongs to) has approval levels
     * @return bool
     */
    private function hasApprovalLevels(): bool
    {
        return ApprovalLevel::where([
            'institutable_id' => $this->user->institutable_id,
            'institutable_type' => $this->user->institutable_type
        ])->count() > 0;
    }

    /**
     * Returns true if user has been assigned to an approval level
     * @return bool
     */
    private function isAssignedToApprovalLevel(): bool
    {
        return !empty($this->user->getApprovalLevelId());
    }

    /**
     * Returns true if loan application is pending approval at user's approval level
     * @return bool
     */
    private function isPendingApprovalAtUsersApprovalLevel(): bool
    {
        $approvers = $this->application->approvers()
            ->where('approval_level_id', $this->user->getApprovalLevelId())
            ->get();

        return $approvers->isEmpty();
    }

    /**
     * Returns true if the loan has been approved at all approval levels prior to the
     * user's approval level
     * NB: If there are no prior levels, the method returns true
     * @return bool
     */
    private function hasBeenApprovedAtPriorLevels(): bool
    {
        // User can approve if there are no prior approval levels
        $priorLevelsCount = $this->user->institutable->approvalLevels()
            ->where('id', '<', $this->user->getApprovalLevelId())
            ->count();

        if (!$priorLevelsCount) {
            return true;
        }

        $priorApprovers = $this->application->approvers()
            ->whereIn('id', $this->user->institutable->staffMembers()->pluck('id')->all())
            ->where('approval_level_id', '<', $this->user->getApprovalLevelId())
            ->get();

        // Some prior approvers are yet to approve the loan
        if ($priorApprovers->isEmpty() || $priorApprovers->count() < $priorLevelsCount) {
            return false;
        }

        $approvedStatus = $this->user->isFinancialInstitutionStaff() ?
            LoanApplicationStatus::getPartnerApprovedStatus() :
            LoanApplicationStatus::getEmployerApprovedStatus();

        return $priorApprovers->reduce(function (bool $canApprove, User $user) use (
            $approvedStatus
        ) {
            $isApprovedByUser = $user->pivot->loan_application_status_id == $approvedStatus->id;

            return $canApprove && $isApprovedByUser;
        }, true);
    }
}
