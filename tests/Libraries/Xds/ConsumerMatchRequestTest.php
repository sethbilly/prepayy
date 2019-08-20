<?php

use Carbon\Carbon;
use CloudLoan\Libraries\Xds\ConsumerMatchRequest;

class ConsumerMatchRequestTest extends TestCase
{
    /**
     * @var ConsumerMatchRequest
     */
    private $matchRequest;

    public function setUp()
    {
        parent::setUp();
        $this->matchRequest = new ConsumerMatchRequest();
    }

    public function test_will_set_date_of_birth_to_iso_date()
    {
        $dateString = '24th Jan 2018';
        $carbonDate = Carbon::parse($dateString);

        $this->matchRequest->setDateOfBirth($dateString);
        $this->assertEquals($carbonDate->format('Y-m-d'), $this->matchRequest->getDateOfBirth());
    }

    public function test_date_of_birth_setter_returns_object()
    {
        $dateString = 'Jun 4, 2006';

        $obj = $this->matchRequest->setDateOfBirth($dateString);

        $this->assertInstanceOf(ConsumerMatchRequest::class, $obj);
    }

    public function test_set_enquiry_reason_returns_object()
    {
        $obj = $this->matchRequest->setEnquiryReason('The enquiry reason');

        $this->assertInstanceOf(ConsumerMatchRequest::class, $obj);
    }

    public function test_set_consumer_name_returns_object()
    {
        $obj = $this->matchRequest->setConsumerName('John Doe');

        $this->assertInstanceOf(ConsumerMatchRequest::class, $obj);
    }

    public function test_set_account_number_returns_object()
    {
        $obj = $this->matchRequest->setAccountNumber('114389187273');

        $this->assertInstanceOf(ConsumerMatchRequest::class, $obj);
    }

    public function test_set_identification_type_returns_object()
    {
        $obj = $this->matchRequest->setIdentification('Voters ID');

        $this->assertInstanceOf(ConsumerMatchRequest::class, $obj);
    }

    public function test_returns_default_product_id()
    {
        $this->assertEquals(45, $this->matchRequest->getProductID());
    }

    public function test_returns_default_enquiry_reason()
    {
        $this->assertEquals('Application for credit by a borrower',
            $this->matchRequest->getEnquiryReason());
    }

    public function test_returns_false_if_has_no_account_number()
    {
        $this->assertFalse($this->matchRequest->hasAccountNumber());
    }

    public function test_returns_false_if_has_no_identification_type()
    {
        $this->assertFalse($this->matchRequest->hasIdentification());
    }
}
