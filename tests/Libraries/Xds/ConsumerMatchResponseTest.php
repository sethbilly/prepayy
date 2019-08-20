<?php
use CloudLoan\Libraries\Xds\ConsumerMatchResponse;

/**
 * Created by PhpStorm.
 * User: kwabena
 * Date: 11/20/17
 * Time: 9:38 PM
 */
class ConsumerMatchResponseTest extends TestCase
{
    private $notFound = '<NoResult><NotFound>No Record Found</NotFound></NoResult>';
    private $found = <<<EOD
    <ConsumerMtaching><MatchedConsumer><MatchingEngineID>362669353</MatchingEngineID>
    <EnquiryID>5589367</EnquiryID><ConsumerID>304634</ConsumerID>
    <Reference>C5589367-304634</Reference><SocialSecurityNo>D017003240034</SocialSecurityNo>
    <VoterIDNo>23924795IG</VoterIDNo><FirstName>PROSPER</FirstName><Surname>KLU</Surname>
    <OtherNames>KOML</OtherNames><Address>PUBLIC HEALTH DISEASE CONTROL</Address>
    <BirthDate>1970-03-24T00:00:00+02:00</BirthDate><AccountNo /></MatchedConsumer>
    </ConsumerMtaching>
EOD;

    public function setUp()
    {
        parent::setUp();
    }

    private function getMatchNotFoundResponse(): stdClass
    {
        $response = new stdClass();

        $response->ConnectConsumerMatchResult = $this->notFound;

        return $response;
    }

    private function getMatchFoundResponse(): stdClass
    {
        $response = new stdClass();

        $response->ConnectConsumerMatchResult = $this->found;

        return $response;
    }

    public function test_returns_true_if_match_is_not_found()
    {
        $consumer = new ConsumerMatchResponse($this->getMatchNotFoundResponse());

        $this->assertTrue($consumer->isNotFoundResponse());
    }

    public function test_returns_false_if_match_is_found()
    {
        $consumer = new ConsumerMatchResponse($this->getMatchFoundResponse());

        $this->assertFalse($consumer->isNotFoundResponse());
    }

    public function test_returns_consumer_id_from_response()
    {
        $consumer = new ConsumerMatchResponse($this->getMatchFoundResponse());

        $this->assertEquals('304634', $consumer->getConsumerID());
    }

    public function test_returns_enquiry_id_from_response()
    {
        $consumer = new ConsumerMatchResponse($this->getMatchFoundResponse());

        $this->assertEquals('5589367', $consumer->getEnquiryID());
    }

    public function test_returns_matching_engine_id_from_response()
    {
        $consumer = new ConsumerMatchResponse($this->getMatchFoundResponse());

        $this->assertEquals('362669353', $consumer->getMatchingEngineID());
    }
}