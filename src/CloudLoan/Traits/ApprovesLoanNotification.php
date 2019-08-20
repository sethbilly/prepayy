<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 20/02/2017
 * Time: 15:38
 */

namespace CloudLoan\Traits;

use App\Entities\LoanApplication;
use App\Entities\User;

trait ApprovesLoanNotification
{
    private function getDeclinedSubject()
    {
        return config('app.name') . ': Loan Application Declined';
    }

    private function getApprovedSubject()
    {
        return config('app.name') . ': Loan Application Approved';
    }

    private function getGreeting(User $user)
    {
        return "Hi! {$user->firstname},";
    }

    private function getActionText()
    {
        return 'Review Application';
    }

    private function getActionLink(LoanApplication $application)
    {
        return route('loan_applications.show', ['application' => $application]);
    }

    private function getLoginPrompt()
    {
        return 'Note: You will be required to login to your ' . config('app.name') . ' account';
    }

    private function getSignature()
    {
        return 'Thank you for using ' . config('app.name') . '!';
    }
}