<?php

namespace App\Jobs;

use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\User;
use App\Notifications\LoanApplicationDisbursed;
use App\Notifications\LoanApprovalRequest;
use App\Notifications\LoanApprovedByEmployer;
use App\Notifications\LoanApprovedByFinancialInstitution;
use App\Notifications\LoanDeclinedByEmployer;
use App\Notifications\LoanDeclinedByFinancialInstitution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ApproveLoanApplicationJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var LoanApplication
     */
    private $application;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanApplication $application
     */
    public function __construct(Request $request, LoanApplication $application)
    {
        $this->request = $request;
        $this->user = $request->user();
        $this->application = $application;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return $this->approveApplication();
    }

    private function approveApplication(): bool
    {
        /**
         * Add the order approver or update the approver if approved/declined at that level
         * Mark the order as approved if approved at all approval levels
         * Send order approved or declined notification to the borrower
         * Notify next level approvers, if present
         */
        return DB::transaction(function () {
            $status = LoanApplicationStatus::find($this->request->get('status_id'));

            $this->addApprover();

            $this->updateApplicationStatus($status);

            return true;
        });
    }

    /**
     * Add the approver
     */
    private function addApprover()
    {
        $approvers = $this->application->approvers()
            ->where('approval_level_id', $this->user->getApprovalLevelId())
            ->get();

        // Exactly one approver is allowed for each approval level
        if ($approvers->count()) {
            $this->application->approvers()->detach([$approvers->pluck('id')->all()]);
        }

        $this->application->approvers()->attach($this->user->id, [
            'loan_application_status_id' => $this->request->get('status_id'),
            'reason' => $this->request->get('reason')
        ]);
    }

    /**
     * Update the application status
     * @param LoanApplicationStatus $status
     */
    private function updateApplicationStatus(LoanApplicationStatus $status)
    {
        $isApproved = $this->user->isEmployerStaff() ?
            $status->isEmployerApproved() :
            $status->isPartnerApproved();

        if (!$isApproved) {
            $this->updateStatusIfNotApprovingLoan($status);
            return;
        }

        // If approved at all levels, notify borrower
        // Else, notify the next level approvers
        if ($this->hasNextApprovalLevel()) {
            // Notify next level approvers
            $this->notifyNextLevelApprovers();
        } else {
            $this->markLoanAsApproved($status);

            $this->sendLoanApprovedNotification();
        }
    }

    /**
     * @param LoanApplicationStatus $status
     */
    private function updateStatusIfNotApprovingLoan(LoanApplicationStatus $status)
    {
        $this->application->loanApplicationStatus()->associate($status);
        $this->application->save();

        // Sent only if loan status is disbursed
        $this->sendLoanDisbursedNotification($status);

        // Sent only if loan status is declined
        $this->sendLoanDeclinedNotification($status);
    }

    /**
     * Notify borrower if loan application is disbursed
     * @param LoanApplicationStatus $status
     */
    private function sendLoanDisbursedNotification(LoanApplicationStatus $status)
    {
        if (!$status->isDisbursed()) {
            return;
        }

        $this->application->user->notify(
            new LoanApplicationDisbursed($this->application, $this->user)
        );
    }

    /**
     * Notify borrower if application is declined
     * @param LoanApplicationStatus $status
     */
    private function sendLoanDeclinedNotification(LoanApplicationStatus $status)
    {
        $isDeclined = $this->user->isEmployerStaff() ?
            $status->isEmployerDeclined() :
            $status->isPartnerDeclined();

        if (!$isDeclined) {
            return;
        }

        $notification = $this->user->isEmployerStaff() ?
            new LoanDeclinedByEmployer($this->application, $this->user) :
            new LoanDeclinedByFinancialInstitution($this->application, $this->user);

        $this->application->user->notify($notification);
    }

    /**
     * Returns true if there is a next approval level
     * @return bool
     */
    private function hasNextApprovalLevel(): bool
    {
        $nextLevelsCount = $this->user->getApprovalLevelId() ?
            $this->user->institutable->approvalLevels()
                ->where('id', '>', $this->user->getApprovalLevelId())->count() :
            $this->user->institutable->approvalLevels()->count();

        return $nextLevelsCount > 0;
    }

    /**
     * @param LoanApplicationStatus $status
     * @return bool
     */
    private function markLoanAsApproved(LoanApplicationStatus $status): bool
    {
        $this->application->loanApplicationStatus()->associate($status);
        return $this->application->save();
    }

    /**
     * Send loan approved notification
     */
    private function sendLoanApprovedNotification()
    {
        $notification = $this->user->isEmployerStaff() ?
                new LoanApprovedByEmployer($this->application, $this->user) :
                new LoanApprovedByFinancialInstitution($this->application, $this->user);

        $this->application->user->notify($notification);
    }

    /**
     * Notify the next level approvers for the loan
     */
    private function notifyNextLevelApprovers()
    {
        $nextApprovalLevelId = ($this->user->getApprovalLevelId() ?? 0) + 1;

        $approvers = $this->user->institutable->staffMembers()
            ->where('approval_level_id', $nextApprovalLevelId)
            ->get();

        if ($approvers->isEmpty()) {
            return;
        }

        Notification::send($approvers, new LoanApprovalRequest($this->application));
    }
}
