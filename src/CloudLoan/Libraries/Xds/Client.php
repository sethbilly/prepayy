<?php

namespace CloudLoan\Libraries\Xds;

/**
 * Created by PhpStorm.
 * User: kwabena
 * Date: 10/31/17
 * Time: 8:26 PM
 */
class Client
{
    /**
     * @var \SoapClient
     */
    protected $client;
    /**
     * The XDS request ticket
     * @var string
     */
    protected $requestTicket;
    /**
     * The XDS account username
     * @var string
     */
    private $username;
    /**
     * The XDS account password
     * @var string
     */
    private $password;

    /**
     * Client constructor.
     * @param \SoapClient $client
     * @param string $username
     * @param string $password
     */
    public function __construct(\SoapClient $client, string $username, string $password)
    {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;

        $this->login();
    }

    /**
     * Set the request ticket
     * @param string $ticket
     */
    private function setRequestTicket(string $ticket)
    {
        $this->requestTicket = $ticket;
    }

    /**
     * Returns the XDS request ticket
     * @return string|null
     */
    public function getRequestTicket()
    {
        return $this->requestTicket;
    }

    /**
     * Determine if a valid request ticket is available
     * @return bool
     */
    private function hasValidTicket(): bool
    {
        if (!$this->requestTicket) {
            return false;
        }

        $result = $this->client->isTicketValid([
            'XDSGhanaWebServiceTicket' => $this->requestTicket
        ]);

        return (bool)$result->IsTicketValidResult;
    }

    /**
     * Login to xds and set the request ticket
     * @return string
     */
    private function login(): string
    {
        if ($this->hasValidTicket()) {
            return $this->getRequestTicket();
        }

        $result = $this->client->login([
            'UserName' => $this->username,
            'Password' => $this->password
        ]);

        $this->setRequestTicket($result->LoginResult);

        return $this->getRequestTicket();
    }

    /**
     * @param ConsumerMatchRequest $request
     * @return ConsumerMatchResponse
     * @throws ConsumerMatchNotFoundException
     */
    public function getConsumerMatch(ConsumerMatchRequest $request)
    {
        $result = $this->client->connectConsumerMatch([
            'DataTicket' => $this->getRequestTicket(),
            'EnquiryReason' => $request->getEnquiryReason(),
            'ProductID' => $request->getProductID(),
            'ConsumerName' => $request->getConsumerName(),
            'AccountNumber' => $request->getAccountNumber(),
            'DateOfBirth' => $request->getDateOfBirth(),
            'Identification' => $request->getIdentification()
        ]);

        $consumer = new ConsumerMatchResponse($result);

        if ($consumer->isNotFoundResponse()) {
            throw new ConsumerMatchNotFoundException($request);
        }

        return $consumer;
    }

    /**
     * @param ConsumerMatchResponse $consumer
     * @return ConsumerFullCreditResponse
     * @throws ConsumerMatchNotFoundException
     */
    public function getConsumerCreditReport(ConsumerMatchResponse $consumer)
    {
        if ($consumer->isNotFoundResponse()) {
            throw new ConsumerMatchNotFoundException();
        }

        $result = $this->client->getConsumerFullCreditReport([
            'DataTicket' => $this->requestTicket,
            'ConsumerID' => $consumer->getConsumerID(),
            'SubscriberEnquiryEngineID' => $consumer->getMatchingEngineID(),
            'enquiryID' => $consumer->getEnquiryID(),
            'consumerMergeList' => ''
        ]);

        return new ConsumerFullCreditResponse($result);
    }
}