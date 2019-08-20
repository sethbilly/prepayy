<?php

namespace App\Jobs;

use App\Entities\LoanApplication;
use CloudLoan\Libraries\Xds\Client;
use CloudLoan\Libraries\Xds\ConsumerFullCreditResponse;
use CloudLoan\Libraries\Xds\ConsumerMatchRequest;
use Illuminate\Support\Facades\Cache;

class GetLoanApplicationCreditReportJob
{
    /**
     * @var LoanApplication
     */
    private $application;
    /**
     * @var Client
     */
    private $xdsClient;

    /**
     * Create a new job instance.
     *
     * @param LoanApplication $application
     */
    public function __construct(LoanApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Execute the job.
     *
     * @param Client $xdsClient
     * @return \stdClass
     */
    public function handle(Client $xdsClient)
    {
        $this->xdsClient = $xdsClient;
        return $this->getReport();
    }

    private function getReport(): \stdClass
    {
        $minsInHour = 60;
        $hoursInDay = 24;
        $daysInWeek = 7;

        $minsInWeek = $minsInHour * $hoursInDay * $daysInWeek;

        return Cache::remember($this->application->getCreditReportCacheKey(), $minsInWeek,
            function () {
                logger()->debug('Getting credit report from XDS', [$this->application]);
                return $this->getReportFromXds();
            });
    }

    private function getReportFromXds(): \stdClass
    {
        $matchRequest = new ConsumerMatchRequest();
        $matchRequest->setConsumerName($this->application->getUser()->getFullName())
            ->setDateOfBirth($this->application->getUser()->getDob('Y-m-d'));

        $consumer = $this->xdsClient->getConsumerMatch($matchRequest);

        return $this->xdsClient->getConsumerCreditReport($consumer)->getReport();
    }
}
