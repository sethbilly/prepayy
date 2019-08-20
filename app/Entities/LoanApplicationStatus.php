<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LoanApplicationStatus extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['status'];


    /**
     * Loan statuses
     * Save status keys in database and use translation files to display label for the status
     */
    const STATUS = [
        'DRAFT_SAVED' => 'draft',
        // Employer states
        'EMPLOYER_PENDING' => 'employer_pending',
        'EMPLOYER_APPROVED' => 'employer_approved',
        'EMPLOYER_DECLINED' => 'employer_declined',
        'EMPLOYER_REQUESTED_INFORMATION' => 'employer_information_requested',
        // Financial institution states
        'PARTNER_PENDING' => 'partner_pending',
        'PARTNER_DECLINED' => 'partner_declined',
        'PARTNER_APPROVED' => 'partner_approved',
        'PARTNER_REQUESTED_INFORMATION' => 'partner_information_requested',
        'PARTNER_DISBURSED' => 'partner_disbursed'
    ];

    /**
     * @param string $status
     * @return LoanApplicationStatus|null
     */
    public static function getStatus(string $status)
    {
        return self::where(['status' => $status])->first();
    }

    /**
     * @return array
     */
    public static function getLoanStatuses(): array
    {
        return self::STATUS;
    }

    /**
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->isStatus(self::STATUS['DRAFT_SAVED']);
    }

    /**
     * @return bool
     */
    public function isPendingEmployerApproval(): bool
    {
        return $this->isStatus(self::STATUS['EMPLOYER_PENDING']);
    }

    public function isPendingPartnerApproval(): bool
    {
        return $this->isStatus(self::STATUS['PARTNER_PENDING']);
    }

    /**
     * @return bool
     */
    public function isDisbursed(): bool
    {
        return $this->isStatus(self::STATUS['PARTNER_DISBURSED']);
    }

    /**
     * @return bool
     */
    public function isPartnerDeclined(): bool
    {
        return $this->isStatus(self::STATUS['PARTNER_DECLINED']);
    }

    /**
     * @return bool
     */
    public function isEmployerDeclined(): bool
    {
        return $this->isStatus(self::STATUS['EMPLOYER_DECLINED']);
    }

    /**
     * @return bool
     */
    public function isEmployerApproved(): bool
    {
        return $this->isStatus(self::STATUS['EMPLOYER_APPROVED']);
    }

    /**
     * @return bool
     */
    public function isPartnerApproved(): bool
    {
        return $this->isStatus(self::STATUS['PARTNER_APPROVED']);
    }

    public function isPartnerRequestForInformation(): bool
    {
        return $this->isStatus(self::STATUS['PARTNER_REQUESTED_INFORMATION']);
    }

    public function isEmployerRequestForInformation(): bool
    {
        return $this->isStatus(self::STATUS['EMPLOYER_REQUESTED_INFORMATION']);
    }

    private function isStatus(string $expected): bool
    {
        return $expected == $this->status;
    }
    
    /**
     * @return LoanApplicationStatus
     */
    public static function getDraftStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['DRAFT_SAVED']);
    }

    /**
     * @return LoanApplicationStatus
     */
    public static function getEmployerPendingStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['EMPLOYER_PENDING']);
    }

    /**
     * @return LoanApplicationStatus
     */
    public static function getEmployerApprovedStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['EMPLOYER_APPROVED']);
    }

    /**
     * @return LoanApplicationStatus
     */
    public static function getEmployerDeclinedStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['EMPLOYER_DECLINED']);
    }

    /**
     * @return LoanApplicationStatus
     */
    public static function getPartnerPendingStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['PARTNER_PENDING']);
    }
    
    /**
     * @return LoanApplicationStatus
     */
    public static function getPartnerApprovedStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['PARTNER_APPROVED']);
    }

    /**
     * @return LoanApplicationStatus
     */
    public static function getPartnerDeclinedStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['PARTNER_DECLINED']);
    }

    /**
     * @return LoanApplicationStatus
     */
    public static function getPartnerRequestedInformationStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['PARTNER_REQUESTED_INFORMATION']);
    }

    /**
     * @return LoanApplicationStatus
     */
    public static function getEmployerRequestedInformationStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['EMPLOYER_REQUESTED_INFORMATION']);
    }


    /**
     * @return LoanApplicationStatus
     */
    public static function getPartnerDisbursedStatus(): LoanApplicationStatus
    {
        return self::getStatus(self::STATUS['PARTNER_DISBURSED']);
    }

    /**
     * Get non-disbursed loan applications statuses for partner
     * @return Collection
     */
    public static function getPartnerNonDisbursedStatuses(): Collection
    {
        $statuses = collect(self::STATUS)
            ->filter(function(string $status) {
                return starts_with($status, 'partner_') && $status !== 'partner_disbursed';
            });

        return self::whereIn('status', $statuses->all())->get();
    }

    /**
     * @return string
     */
    public function getDisplayStatusAttribute(): string
    {
        $key = 'display_status';

        if (!array_key_exists($key, $this->attributes)) {
            $this->attributes[$key] = config('cloudloan.' . $this->status, $this->status);
        }

        return $this->attributes[$key];
    }

    /**
     * Returns true if the loan application status is an employer status
     * @return bool
     */
    public function isEmployerStatus(): bool
    {
        $statuses = collect(self::STATUS)
            ->filter(function(string $status) {
                return starts_with($status, 'employer_');
            });

        return $statuses->contains($this->status);
    }

    /**
     * Returns true if loan application status is any partner status except disbursed
     * @return bool
     */
    public function isPartnerNonDisbursedStatus(): bool
    {
        $statuses = collect(self::STATUS)
            ->filter(function(string $status) {
                return starts_with($status, 'partner_') && $status !== 'partner_disbursed';
            });

        return $statuses->contains($this->status);
    }
    
}
