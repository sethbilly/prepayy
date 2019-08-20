<?php
namespace CloudLoan\Libraries\Xds;

/**
 * Created by PhpStorm.
 * User: kwabena
 * Date: 11/20/17
 * Time: 6:28 AM
 */
class ConsumerFullCreditResponse
{
    use ConvertFirstLetterToLowerCase, ParseXmlToArrayOrObject;

    /**
     * @var \stdClass
     */
    private $report;

    /**
     * ConsumerFullCreditResponse constructor.
     * @param \stdClass $xmlResponse
     */
    public function __construct($xmlResponse)
    {
        $parsedResponse = $this->parseXmlToObject(
            $xmlResponse->GetConsumerFullCreditReportResult
        );

        $this->report = !empty($parsedResponse) ?
            $this->convertFirstLetterOfObjectPropertyToLowerCase($parsedResponse) : null;

        $this->setReportSegments();
    }

    private function setReportSegments()
    {
        $this->setPersonalDetails();
        $this->setEmploymentHistory();
        $this->setContactHistory();
        $this->setIdentificationHistory();
        $this->setAddressHistory();
        $this->setAccountRating();
        $this->setDelinquencyInformation();
        $this->setCreditAccountSummary();
    }

    private function setPersonalDetails()
    {
        if (empty($this->report->personalDetailsSummary)) {
            return;
        }

        $personalDetails = $this->convertFirstLetterOfObjectPropertyToLowerCase(
            $this->report->personalDetailsSummary
        );
        $personalDetails->fullName = $this->getFullName($personalDetails);

        $this->report->personalDetailsSummary = $personalDetails;
    }

    private function getFullName($personalDetails)
    {
        $title = $personalDetails->title;
        $firstName = $personalDetails->firstName;
        $lastName = $personalDetails->surname;

        return $title . ' ' . $firstName . ' ' . $lastName;
    }

    private function setEmploymentHistory()
    {
        if (empty($this->report->employmentHistory)) {
            return;
        }

        $histories = [];
        foreach ($this->report->employmentHistory as $history) {
            $histories[] = $this->convertFirstLetterOfObjectPropertyToLowerCase($history);
        }

        $this->report->employmentHistory = $histories;
    }

    private function setContactHistory()
    {
        if (empty($this->report->telephoneHistory)) {
            return;
        }
        $histories = [];

        foreach ($this->report->telephoneHistory as $history) {
            $histories[] = $this->convertFirstLetterOfObjectPropertyToLowerCase($history);
        }

        $this->report->telephoneHistory = $histories;
    }

    private function setAddressHistory()
    {
        if (empty($this->report->addressHistory)) {
            return;
        }
        $histories = [];

        foreach ($this->report->addressHistory as $history) {
            $histories[] = $this->convertFirstLetterOfObjectPropertyToLowerCase($history);
        }

        $this->report->addressHistory = $histories;
    }

    private function setIdentificationHistory()
    {
        if (empty($this->report->identificationHistory)) {
            return;
        }
        $histories = [];

        foreach ($this->report->identificationHistory as $history) {
            $histories[] = $this->convertFirstLetterOfObjectPropertyToLowerCase($history);
        }

        $this->report->identificationHistory = $histories;
    }

    private function setAccountRating()
    {
        if (empty($this->report->accountRating)) {
            return;
        }
        $rating = $this->convertFirstLetterOfObjectPropertyToLowerCase(
            $this->report->accountRating
        );
        $rating->noOfAutoLoanAccountsGood = $rating->noOfAutoLoanccountsGood;

        $this->report->accountRating = $rating;
    }

    private function setDelinquencyInformation()
    {
        if (empty($this->report->deliquencyInformation)) {
            return;
        }

        $information = $this->convertFirstLetterOfObjectPropertyToLowerCase(
            $this->report->deliquencyInformation
        );

        unset($this->report->deliquencyInformation);
        $this->report->delinquencyInformation = $information;
    }

    private function setCreditAccountSummary()
    {
        if (empty($this->report->creditAccountSummary)) {
            return;
        }
        $summary = $this->convertFirstLetterOfObjectPropertyToLowerCase(
            $this->report->creditAccountSummary
        );

        $summary->totalOutstandingDebt = $summary->totalOutstandingdebt;
        unset($summary->totalOutstandingdebt);

        $summary->totalAccountArrears = $summary->totalAccountarrear;
        unset($summary->totalAccountarrear);

        $summary->amountInArrears = $summary->amountarrear;
        unset($summary->amountarrear);

        $summary->totalAccountsInGoodCondition = $summary->totalaccountinGodcondition;
        unset($summary->totalaccountinGodcondition);

        $summary->totalNumberOfJudgement = $summary->totalNumberofJudgement;
        unset($summary->totalNumberofJudgement);

        $summary->totalNumberOfDishonouredCheques = $summary->totalNumberofDishonoured;
        unset($summary->totalNumberofDishonoured);

        $this->report->creditAccountSummary = $summary;
    }


    /**
     * @return \stdClass. Returns object with the following fields:
     * stdClass $referenceNo
     * string $nationality
     * stdClass $nationalIDNo
     * stdClass $passportNo
     * stdClass $driversLicenseNo
     * string $birthDate
     * int $dependants
     * string $gender
     * string $maritalStatus
     * string $cellularNo
     * stdClass $emailAddress
     * string $fullName
     * string $firstName
     * string $surname
     * string $otherNames
     * string $title
     * string $employerDetail
     * string $consumerID
     */
    public function getPersonalDetails()
    {
        return $this->report->personalDetails ?? new \stdClass();
    }

    /**
     * @return array. Array of employment detail objects. Each object has the following
     * fields:
     * string $employerDetail
     * string $occupation
     */
    public function getEmploymentHistory(): array
    {
        return $this->report->employmentHistory ?? [];
    }

    /**
     * @return array. Array of contact objects. Each object has the following fields:
     * string $mobileTelephoneNumber
     */
    public function getContactHistory(): array
    {
        return $this->report->telephoneHistory ?? [];
    }

    /**
     * @return array. Each object of the array has the following fields:
     * string $address1
     * string $address2
     * string $address3
     * stdClass $address4
     * string $addressTypeInd (Postal, Residential)
     */
    public function getAddressHistory(): array
    {
        return $this->report->addressHistory ?? [];
    }

    /**
     * @return array. Each object of the array has the following fields:
     * string $identificationNumber
     * string $identificationType
     */
    public function getIdentificationHistory(): array
    {
        return $this->report->identificationHistory ?? [];
    }

    /**
     * @return \stdClass. Object has the following properties:
     * int $noOfHomeLoanAccountsGood
     * int $noOfHomeLoanAccountsBad
     * int $noOfAutoLoanccountsGood
     * int $noOfAutoLoanAccountsBad
     * int $noOfStudyLoanAccountsGood
     * int $noOfStudyLoanAccountsBad
     * int $noOfPersonalLoanAccountsGood
     * int $noOfPersonalLoanAccountsBad
     * int $noOfCreditCardAccountsGood
     * int $noOfCreditCardAccountsBad
     * int $noOfRetailAccountsGood
     * int $noOfRetailAccountsBad
     * int $noOfJointLoanAccountsGood
     * int $noOfJointLoanAccountsBad
     * int $noOfTelecomAccountsGood
     * int $noOfTelecomAccountsBad
     * int $noOfOtherAccountsGood
     * int $noOfOtherAccountsBad
     */
    public function getAccountRating()
    {
        return $this->report->accountRating ?? new \stdClass();
    }

    /**
     * @return \stdClass. Object with the following fields:
     * string $highestDelinquencyRating
     */
    public function getDelinquencyInformation()
    {
        return $this->report->delinquencyInformation ?? new \stdClass();
    }

    /**
     * @return \stdClass. Object with the following fields:
     * int $totalMonthlyInstalment
     * int $totalOutstandingDebt
     * int $totalAccountArrears
     * int $amountInArrears
     * int $totalAccountsInGoodCondition
     * int $totalNumberOfJudgements
     * int $totalJudgementAmount
     * string $lastJudgementDate
     * int $totalNumberOfDishonouredCheques
     * int $totalDishonouredAmount
     * string $lastBouncedChequesDate
     * int $rating
     */
    public function getCreditAccountSummary()
    {
        return $this->report->creditAccountSummary ?? new \stdClass();
    }

    public function getReport()
    {
        return $this->report ?? new \stdClass();
    }
}