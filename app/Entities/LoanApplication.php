<?php

namespace App\Entities;

use App\Jobs\CalculateMonthlyAmountPayableJob;
use CloudLoan\Traits\UuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class LoanApplication extends Model
{
    use UuidModel;

    /**
     * @var array
     */
    protected $fillable = [
        'employer_id', 'identification_card_id', 'guarantor_id', 'user_id', 'loan_application_status_id',
        'loan_product_id', 'amount', 'tenure_in_years', 'interest_per_year'
    ];

    /**
     * The borrower who created this request
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A given id card can be used to apply for different loan_applications (one ID to many loan_applications)
     * @return BelongsTo
     */
    public function identificationCard(): BelongsTo
    {
        return $this->belongsTo(IdentificationCard::class, 'identification_card_id');
    }

    /**
     * Several loan applications can be created while working for a given employer
     * @return BelongsTo
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }

    /**
     * @return BelongsTo
     */
    public function guarantor(): BelongsTo
    {
        return $this->belongsTo(Guarantor::class, 'guarantor_id');
    }

    /**
     * @return BelongsTo
     */
    public function loanApplicationStatus(): BelongsTo
    {
        return $this->belongsTo(LoanApplicationStatus::class, 'loan_application_status_id');
    }

    /**
     * @return BelongsTo
     */
    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }

    public function isDraft(): bool
    {
        return $this->loanApplicationStatus && $this->loanApplicationStatus->isDraft();
    }

    public function isPendingEmployerApproval(): bool
    {
        return $this->loanApplicationStatus && $this->loanApplicationStatus->isPendingEmployerApproval();
    }

    /**
     * @return BelongsToMany
     */
    public function approvers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, 'approver_loan_application', 'loan_application_id', 'user_id')
            ->withPivot(['loan_application_status_id'])
            ->withTimestamps();
    }

    /**
     * @return bool
     */
    public function hasBeenDisbursed(): bool
    {
        return $this->loanApplicationStatus && $this->loanApplicationStatus->isDisbursed();
    }

    /**
     * @return HasMany
     */
    public function requestedDocuments(): HasMany
    {
        return $this->hasMany(RequestedLoanDocument::class, 'loan_application_id');
    }

    /**
     * Returns all changes requested by the employer for this loan
     * @return Collection
     */
    public function getEmployerRequestedInformation()
    {
        return $this->requestedDocuments->filter(function (RequestedLoanDocument $doc) {
           return $doc->user->institutable_id == $this->employer_id;
        });
    }

    /**
     * Returns all changes requested by the financial institution for this loan
     * @return Collection
     */
    public function getPartnerRequestedInformation()
    {
        return $this->requestedDocuments->filter(function (RequestedLoanDocument $doc) {
            return $doc->user->institutable_id ==
                $this->loanProduct->financial_institution_id;
        });
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUsersEmployer()
    {
        return $this->getUser()->employers()->where('id', $this->employer_id)->first();
    }

    private function getLoanProduct(): LoanProduct
    {
        return $this->loanProduct;
    }

    public function getInstitution(): FinancialInstitution
    {
        return $this->getLoanProduct()->institution;
    }

    /**
     * Set monthly payable, tenure in months and total payable fields for loan
     */
    public function setRepaymentInformation()
    {
        $hasRepaymentInfo = $this->tenure_in_years && $this->amount;

        if (!$hasRepaymentInfo) {
            $this->monthly_payable = '';
            $this->total_payable = '';
            $this->tenure_in_months = '';

            return;
        }

        $this->monthly_payable = dispatch(new CalculateMonthlyAmountPayableJob(
            $this->amount,
            $this->interest_per_year,
            $this->tenure_in_years
        ));
        $this->tenure_in_months = $this->tenure_in_years * 12;
        $this->total_payable = $this->tenure_in_months * $this->monthly_payable;
    }

    /**
     * @return LoanApplicationStatus
     */
    public function getLoanApplicationStatus(): LoanApplicationStatus
    {
        return $this->loanApplicationStatus;
    }

    public function isApprovedOrDeclined(): bool
    {
        $status = $this->getLoanApplicationStatus();

        return $status->isEmployerApproved() || $status->isEmployerDeclined()
            || $status->isPartnerApproved() || $status->isPartnerDeclined();
    }

    public function getCreditReportCacheKey(): string
    {
        return class_basename(self::class) . ':' . 'credit_report:' . $this->id;
    }
}
