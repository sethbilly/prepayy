<?php

namespace App\Jobs;

use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\RequestedLoanDocument;
use App\Entities\User;
use App\Notifications\LoanDocumentRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestLoanDocumentJob
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
        $this->user = $this->request->user();
        $this->application = $application;
    }

    /**
     * Execute the job.
     *
     * @return RequestedLoanDocument
     */
    public function handle()
    {
        return $this->requestAdditionalInformation();
    }

    /**
     * @return RequestedLoanDocument
     */
    private function requestAdditionalInformation(): RequestedLoanDocument
    {
        $document = DB::transaction(function () {
            $doc = new RequestedLoanDocument([
                'request' => $this->request->get('request'),
                'user_id' => $this->user->id
            ]);

            $this->updateLoanApplicationStatus();

            return $this->application->requestedDocuments()->save($doc);
        });

        // Notify the borrower of the request
        $this->application->user->notify(new LoanDocumentRequested($document));

        return $document;
    }

    /**
     * Update the loan application status
     */
    private function updateLoanApplicationStatus()
    {
        $status = $this->user->isEmployerStaff()
            ? LoanApplicationStatus::getEmployerRequestedInformationStatus()
            : LoanApplicationStatus::getPartnerRequestedInformationStatus();

        $this->application->loanApplicationStatus()->associate($status);
        $this->application->save();
    }
}
