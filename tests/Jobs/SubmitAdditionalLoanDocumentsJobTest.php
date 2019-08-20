<?php

use App\Entities\RequestedLoanDocument;
use App\Jobs\SubmitAdditionalLoanDocumentsJob;
use App\Notifications\LoanDocumentSubmitted;

class SubmitAdditionalLoanDocumentsJobTest extends TestCase
{
    use GetMockUploadedFileTrait;
    
    /**
     * @var RequestedLoanDocument
     */
    private $document;
    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->document = factory(RequestedLoanDocument::class)->create();
        $this->faker = Faker\Factory::create();
    }

    public function test_can_send_text_only_response()
    {
        $req = $this->getAuthenticatedRequest(['response' => $this->faker->sentence], $this->document->application->user);

        $this->expectsNotification($this->document->user, LoanDocumentSubmitted::class);

        $wasUpdated = dispatch(new SubmitAdditionalLoanDocumentsJob($req, $this->document));
        
        $this->assertTrue($wasUpdated);
        $this->assertEquals($req->get('response'), $this->document->response);
    }

    public function test_can_send_files_only_response()
    {
        $mockFiles[] = $this->getMockUploadedFile(__FILE__);
        $mockFiles[] = $this->getMockUploadedFile(__FILE__);
        
        $mockRequest = Mockery::mock($this->getAuthenticatedRequest([], $this->document->application->user));
        $mockRequest->shouldReceive('hasFile')->with('files')->once()->andReturn(true);
        $mockRequest->shouldReceive('file')->with('files')->once()->andReturn($mockFiles);

        $this->expectsNotification($this->document->user, LoanDocumentSubmitted::class);
        
        $wasUpdated = dispatch(new SubmitAdditionalLoanDocumentsJob($mockRequest, $this->document));

        $this->assertTrue($wasUpdated);
        $this->assertCount(2, $this->document->files);
    }
}
