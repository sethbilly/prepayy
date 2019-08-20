<?php
namespace CloudLoan\Libraries\Xds;

/**
 * Created by PhpStorm.
 * User: kwabena
 * Date: 11/20/17
 * Time: 6:28 AM
 */
class ConsumerMatchResponse
{
    use ParseXmlToArrayOrObject, ConvertFirstLetterToLowerCase;

    /**
     * @var \stdClass
     */
    private $consumer;

    /**
     * ConsumerMatchResponse constructor.
     * @param \stdClass $xmlResponse
     */
    public function __construct($xmlResponse)
    {
        logger()->debug('consumer match response: ', [$xmlResponse]);
        $parsedResponse = $this->parseXmlToObject(
            $xmlResponse->ConnectConsumerMatchResult
        );

        $this->consumer = !empty($parsedResponse->MatchedConsumer) ?
            $this->convertFirstLetterOfObjectPropertyToLowerCase(
                $parsedResponse->MatchedConsumer
            ) : null;
    }

    /**
     * @return bool
     */
    public function isNotFoundResponse()
    {
        return empty($this->consumer);
    }

    /**
     * @return string
     */
    public function getConsumerID()
    {
        return $this->consumer->consumerID ?? '';
    }

    /**
     * @return string
     */
    public function getEnquiryID()
    {
        return $this->consumer->enquiryID ?? '';
    }

    /**
     * @return string
     */
    public function getMatchingEngineID()
    {
        return $this->consumer->matchingEngineID ?? '';
    }

    /**
     * @return null|\stdClass
     */
    public function getConsumer()
    {
        return $this->consumer;
    }
}