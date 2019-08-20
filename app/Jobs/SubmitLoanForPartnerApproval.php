<?php

namespace App\Jobs;

use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use CloudLoan\Traits\RequestsLoanApproval;
use Illuminate\Http\Request;

class SubmitLoanForPartnerApproval
{
    use RequestsLoanApproval;

    /**
     * @var Request
     */
    private $request;
    /**
     * @var LoanApplication
     */
    private $application;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanApplication $application
     */
    public function __construct(Request $request, LoanApplication $application)
    {
        $this->request = $request;
        $this->application = $application;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return $this->submitApplication();
    }

    /**
     * @return bool
     */
    private function submitApplication(): bool
    {
        if (!$this->isValidToken()) {
            return false;
        }

        // If the application is still marked as draft or employer approved, upgrade to pending partner approval
        if ($this->application->loanApplicationStatus->isDraft() ||
            $this->application->loanApplicationStatus->isEmployerApproved()
        ) {
            $this->application->loanApplicationStatus()->associate(
                LoanApplicationStatus::getPartnerPendingStatus()
            );
            $this->application->save();
        }

        return $this->requestOrganizationApproval(
            $this->application, $this->application->loanProduct->institution
        );
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isValidToken(): bool
    {
        $cacheEntry = cache()->pull(SubmitLoanApplicationJob::PARTNER_SUBMIT_TOKEN_KEY);

        return $this->isValidCacheEntry($cacheEntry) &&
        $cacheEntry['token'] == $this->request->get('submission_token') &&
        $cacheEntry['application_id'] == $this->application->id;
    }

    /**
     * @param $cacheEntry
     * @return bool
     */
    private function isValidCacheEntry($cacheEntry): bool
    {
        return is_array($cacheEntry) &&
        array_key_exists('token', $cacheEntry) &&
        array_key_exists('application_id', $cacheEntry);
    }
}
