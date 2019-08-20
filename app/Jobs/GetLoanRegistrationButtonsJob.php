<?php

namespace App\Jobs;

use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use Illuminate\Http\Request;

class GetLoanRegistrationButtonsJob
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
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanApplication $application
     */
    public function __construct(Request $request, LoanApplication $application = null)
    {
        $this->request = $request;
        $this->application = $application ?? new LoanApplication();
    }

    /**
     * Execute the job.
     *
     * @return array
     */
    public function handle()
    {
        return $this->getButtons();
    }

    /**
     * @return array
     */
    private function getButtons(): array
    {
        /*
         * 3 buttons available: Save draft, request employers approval and request partner approval
         *
         * 1. No buttons if:
         *    a. If disbursed or approved by partner
         *    b. Declined by partner or employer
         * 2. Draft and request employer buttons if:
         *    a. New application
         *    b. Draft application
         *    c. Pending employer approval
         * 3. Draft and request institution buttons if:
         *    a. Approved by employer
         *    b. Pending partner approval
         */
        $buttons = [
            'draft' => [
                'label' => 'Save draft',
                'classes' => 'btn-secondary',
                'value' => '',
                'icons' => 'fa-save'
            ],
            'employer' => [
                'label' => 'Request employers approval',
                'classes' => 'btn-success',
                'value' => 1,
                'icons' => ''
            ],
            'partner' => [
                'label' => 'Submit application',
                'classes' => 'btn-success',
                'value' => 2,
                'icons' => ''
            ]
        ];

        $status = $this->getApplicationStatus();

        if (empty($status) || $this->canBeApprovedByEmployer($status)) {
            return [$buttons['draft'], $buttons['employer']];
        }

        $canSubmitToPartner = $status->isEmployerApproved()
            || $status->isPendingPartnerApproval()
            || $status->isPartnerRequestForInformation();

        if ($canSubmitToPartner) {
            return [$buttons['draft'], $buttons['partner']];
        }

        return [];
    }

    /**
     * @return LoanApplicationStatus|null
     */
    private function getApplicationStatus()
    {
        return $this->application ? $this->application->loanApplicationStatus : null;
    }

    /**
     * @param LoanApplicationStatus $status
     * @return bool
     */
    private function canBeApprovedByEmployer(LoanApplicationStatus $status)
    {
        return $status->isDraft() || $status->isPendingEmployerApproval()
            || $status->isEmployerRequestForInformation();
    }
}
