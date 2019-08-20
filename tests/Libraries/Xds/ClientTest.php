<?php

use CloudLoan\Libraries\Xds\Client;
use CloudLoan\Libraries\Xds\ConsumerMatchRequest;
use CloudLoan\Libraries\Xds\ConsumerMatchResponse;
use Mockery\Mock;

class ClientTest extends TestCase
{
    /**
     * @var Mock
     */
    private $clientMock;

    /**
     * @var string
     */
    private $xdsUsername;

    /**
     * @var string
     */
    private $xdsPassword;

    public function tearDown()
    {
        Mockery::close();
    }

    public function setUp()
    {
        parent::setUp();

        $client = new SoapClient(config('services.xds.api_url'));
        $this->clientMock = Mockery::mock($client);
        $this->xdsUsername = config('services.xds.username');
        $this->xdsPassword = config('services.xds.password');
    }

    public static function getLoginResponse(): stdClass
    {
        $loginResponse = new stdClass();
        $loginResponse->LoginResult = '199ad8810038773899277399';

        return $loginResponse;
    }

    public static function getConsumerMatchResponse(): stdClass
    {
        $matchResponse = new stdClass();
        $matchResponse->ConnectConsumerMatchResult = <<<EOD
    <ConsumerMtaching><MatchedConsumer><MatchingEngineID>362669325</MatchingEngineID>
    <EnquiryID>5589362</EnquiryID><ConsumerID>304634</ConsumerID>
    <Reference>C5589362-304634</Reference><SocialSecurityNo>D017003240034</SocialSecurityNo>
    <VoterIDNo>23924795IG</VoterIDNo><FirstName>PROSPER</FirstName><Surname>KLU</Surname>
    <OtherNames>KOML</OtherNames><Address>PUBLIC HEALTH DISEASE CONTROL</Address>
    <BirthDate>1970-03-24T00:00:00+02:00</BirthDate><AccountNo /></MatchedConsumer>
    </ConsumerMtaching>
EOD;
        return $matchResponse;
    }

    public static function getConsumerCreditResponse(): stdClass
    {
        $response = new stdClass();

        $response->GetConsumerFullCreditReportResult = '';

        return $response;
    }

    public function test_it_should_invoke_login_with_the_correct_arguments()
    {
        $this->clientMock->shouldReceive('login')->once()->with([
            'UserName' => $this->xdsUsername,
            'Password' => $this->xdsPassword
        ])->andReturn(self::getLoginResponse());

        new Client($this->clientMock, $this->xdsUsername, $this->xdsPassword);
    }

    public function test_it_should_pass_correct_options_to_consumer_match_request()
    {
        $matchRequest = new ConsumerMatchRequest();
        $matchRequest->setConsumerName('John Doe')->setDateOfBirth('2017-02-02');

        $loginResponse = self::getLoginResponse();
        $this->clientMock->shouldReceive('login')->andReturn($loginResponse);

        $this->clientMock->shouldReceive('connectConsumerMatch')->once()->with([
            'DataTicket' => $loginResponse->LoginResult,
            'EnquiryReason' => $matchRequest->getEnquiryReason(),
            'ProductID' => $matchRequest->getProductID(),
            'ConsumerName' => $matchRequest->getConsumerName(),
            'AccountNumber' => $matchRequest->getAccountNumber(),
            'DateOfBirth' => $matchRequest->getDateOfBirth(),
            'Identification' => $matchRequest->getIdentification()
        ])->andReturn(self::getConsumerMatchResponse());

        $client = new Client($this->clientMock, $this->xdsUsername, $this->xdsPassword);

        $client->getConsumerMatch($matchRequest);
    }

    public function test_it_should_pass_correct_options_to_consumer_credit_request()
    {
        $matchResponse = new ConsumerMatchResponse(self::getConsumerMatchResponse());

        $loginResponse = self::getLoginResponse();
        $this->clientMock->shouldReceive('login')->andReturn($loginResponse);

        $this->clientMock->shouldReceive('getConsumerFullCreditReport')->once()->with([
            'DataTicket' => $loginResponse->LoginResult,
            'ConsumerID' => $matchResponse->getConsumerID(),
            'SubscriberEnquiryEngineID' => $matchResponse->getMatchingEngineID(),
            'enquiryID' => $matchResponse->getEnquiryID(),
            'consumerMergeList' => ''
        ])->andReturn(self::getConsumerCreditResponse());

        $client = new Client($this->clientMock, $this->xdsUsername, $this->xdsPassword);

        $client->getConsumerCreditReport($matchResponse);
    }
}

