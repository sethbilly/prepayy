<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 24/02/2017
 * Time: 13:36
 */

namespace CloudLoan\Traits;


use App\Entities\LoanApplication;
use App\Entities\User;
use App\Notifications\LoanApprovalRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

trait RequestsLoanApproval
{
    /**
     * Notify approvers of the given organization of a borrower's request for loan approval
     * @param LoanApplication $application
     * @param Model $organization
     * @return bool
     */
    private function requestOrganizationApproval(LoanApplication $application, Model $organization)
    {
        // Notify approval level 1 staff of the user's current employer
        $approvalLevelOne = $organization->approvalLevels()->orderBy('created_at', 'desc')->first();

        $approvers = $organization->staffMembers(['approvalLevel'])->get()
            // Get all users with approval permission
            ->filter(function (User $user) {
                return $user->can('approve-loan-application');
            })
            // Get level one approvers or all approvers if there are no approval levels
            ->filter(function (User $user) use ($approvalLevelOne) {
                return !$approvalLevelOne || ($user->getApprovalLevelId() == $approvalLevelOne->id);
            });

        if ($approvers->count()) {
            Notification::send($approvers, new LoanApprovalRequest($application));
        }

        return true;
    }
}