<?php
namespace CloudLoan\Libraries\Xds;

use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: kwabena
 * Date: 11/20/17
 * Time: 6:28 AM
 */
class ConsumerMatchRequest
{
    /**
     * @var string
     */
    private $enquiryReason = 'Application for credit by a borrower';
    /**
     * @var int
     */
    private $productID = 45;
    /**
     * @var string
     */
    private $consumerName;
    /**
     * @var string
     */
    private $accountNumber;
    /**
     * @var string (format = dd/mm/yyyy)
     */
    private $dateOfBirth;
    /**
     * @var string
     */
    private $identification;

    /**
     * @return string
     */
    public function getEnquiryReason()
    {
        return $this->enquiryReason;
    }

    /**
     * @param $enquiryReason
     * @return $this
     */
    public function setEnquiryReason($enquiryReason)
    {
        $this->enquiryReason = $enquiryReason;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductID(): int
    {
        return $this->productID;
    }

    /**
     * @return string
     */
    public function getConsumerName()
    {
        return $this->consumerName;
    }

    /**
     * @param string $consumerName
     * @return $this
     */
    public function setConsumerName($consumerName)
    {
        $this->consumerName = $consumerName;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAccountNumber(): bool
    {
        return !empty($this->getAccountNumber());
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber ?? '';
    }

    /**
     * @param string $accountNumber
     * @return $this
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasDateOfBirth(): bool
    {
        return !empty($this->getDateOfBirth());
    }

    /**
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth ?? '';
    }

    /**
     * @param string $dateOfBirth
     * @return $this
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = !empty($dateOfBirth) ?
            Carbon::parse($dateOfBirth)->format('Y-m-d') : $dateOfBirth;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasIdentification(): bool
    {
        return !empty($this->getIdentification());
    }

    /**
     * @return string
     */
    public function getIdentification()
    {
        return $this->identification ?? '';
    }

    /**
     * @param string $identification
     * @return $this
     */
    public function setIdentification($identification)
    {
        $this->identification = $identification;
        return $this;
    }
}