<?php

namespace App\Jobs;

use App\Entities\Guarantor;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\LoanProduct;
use App\Entities\User;
use App\Notifications\LoanApprovalRequest;
use App\Notifications\PartnerApprovalRequest;
use CloudLoan\Traits\RequestsLoanApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SubmitLoanApplicationJob
{
    use RequestsLoanApproval;

    /**
     * Session/cache key for partner submission
     */
    const PARTNER_SUBMIT_TOKEN_KEY = 'partner_submit_confirmation';
    const SUBMIT_TOKEN_LENGTH = 6;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    private $isSubmitToEmployer = false;
    /**
     * @var bool
     */
    private $isSubmitToPartner = false;
    /**
     * @var LoanProduct
     */
    private $loanProduct;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanProduct $loanProduct
     */
    public function __construct(Request $request, LoanProduct $loanProduct)
    {
        $this->request = $request;
        $this->user = $request->user();
        // Determine if user is submitting to the employer for approval
        $this->isSubmitToEmployer = $this->request->get('submit') == 1;
        // Determine if user is submitting to the financial institution
        $this->isSubmitToPartner = $this->request->get('submit') == 2;
        $this->loanProduct = $loanProduct;
    }

    /**
     * Execute the job.
     *
     * @return LoanApplication
     */
    public function handle()
    {
        return $this->submitLoanApplication();
    }

    /**
     * @return LoanApplication
     */
    private function submitLoanApplication(): LoanApplication
    {
        // Update the user's details
        // Create or update the loan application
        // If submitting to employer, notify employer's approvers
        // Else generate submission token for the user

        $application = DB::transaction(function () {
            dispatch(new UpdateBorrowerProfileJob($this->request));

            return $this->createLoanApplication($this->addGuarantor());
        });

        if ($this->isSubmitToEmployer) {
            $this->requestOrganizationApproval($application, $application->employer);
        }

        $this->sendRequestPartnerConfirmationToken($application);

        return $application;
    }

    /**
     * @return Guarantor|null
     */
    private function addGuarantor()
    {
        if (!$this->request->has('guarantor') || !$this->request->input('guarantor.name')) {
            return null;
        }

        // If the guarantor has already been added, update the record, else create new record
        $guarantor = $this->request->has('guarantor.id') ?
            $this->user->guarantors()->where('id', $this->request->input('guarantor.id'))->first() :
            new Guarantor(['user_id' => $this->user->id]);

        foreach ($guarantor->getFillable() as $fillable) {
            if ($this->request->has("guarantor.{$fillable}")) {
                $guarantor->{$fillable} = $this->request->input("guarantor.{$fillable}");
            }
        }

        $guarantor->save();

        return $guarantor;
    }

    /**
     * @param Guarantor|null $guarantor
     * @return LoanApplication
     */
    private function createLoanApplication(Guarantor $guarantor = null): LoanApplication
    {
        $key = 'loan_application_id';

        if ($this->request->has($key)) {
            $application = $this->user->loanApplications()->where('id', $this->request->get($key))->first();
        } else {
            $application = new LoanApplication([
                'user_id' => $this->user->id,
                'loan_product_id' => $this->loanProduct->id
            ]);
            $application->loanApplicationStatus()->associate(LoanApplicationStatus::getDraftStatus());
        }

        // Refresh the current employer and id card relations
        $this->user->load(['currentEmployerRelation', 'currentIdCardRelation']);

        if ($this->user->currentEmployer()) {
            $application->employer()->associate($this->user->currentEmployer());
        }

        if ($this->user->currentIdCard()) {
            $application->identificationCard()->associate($this->user->currentIdCard());
        }

        if ($guarantor) {
            $application->guarantor()->associate($guarantor);
        }

        $this->updateApplicationStatus($application);

        $application->amount = $this->request->get('amount');
        $application->tenure_in_years = $this->request->get('tenure');
        $application->interest_per_year = $this->loanProduct->interest_per_year;
        $application->save();

        return $application;
    }

    /**
     * @param LoanApplication $application
     */
    private function updateApplicationStatus(LoanApplication $application)
    {
        // If application is in draft status, upgrade to pending employer approval
        if ($this->isSubmitToEmployer && $application->loanApplicationStatus->isDraft()) {
            $application->loanApplicationStatus()->associate(LoanApplicationStatus::getEmployerPendingStatus());
        }

        if (!$application->loanApplicationStatus) {
            $application->loanApplicationStatus()->associate(LoanApplicationStatus::getDraftStatus());
        }
    }

    /**
     * @param LoanApplication $application
     * @return bool
     * @throws \Exception
     */
    private function sendRequestPartnerConfirmationToken(LoanApplication $application)
    {
        if (!$this->isSubmitToPartner) {
            return false;
        }

        // Generate and send submission confirmation token
        $token = strtoupper(str_random(self::SUBMIT_TOKEN_LENGTH));
        cache()->put(self::PARTNER_SUBMIT_TOKEN_KEY, [
            'token' => $token, 'application_id' => $application->id
        ], 60);

        $this->user->notify(new PartnerApprovalRequest($application, $token));
    }

    /**
     * @return bool
     */
    public function isSubmitForPartnerApproval(): bool
    {
        return $this->isSubmitToPartner;
    }

    /**
     * @return bool
     */
    public function isSubmitForEmployerApproval(): bool
    {
        return $this->isSubmitToEmployer;
    }
}
