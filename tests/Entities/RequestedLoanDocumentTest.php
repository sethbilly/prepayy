<?php

use App\Entities\FileEntry;
use App\Entities\LoanApplication;
use App\Entities\RequestedLoanDocument;
use App\Entities\User;

class RequestedLoanDocumentTest extends TestCase
{
    /**
     * @var RequestedLoanDocument
     */
    private $document;

    public function setUp()
    {
        parent::setUp();
        $this->document = factory(RequestedLoanDocument::class)->create();
    }

    public function test_can_get_loan_application()
    {
        $this->assertInstanceOf(LoanApplication::class, $this->document->application);
    }

    public function test_can_get_document_files()
    {
        $this->assertCount(0, $this->document->files);

        factory(FileEntry::class, 2)->create([
            'fileable_id' => $this->document->id,
            'fileable_type' => $this->document->getMorphClass()
        ]);

        $this->assertCount(2, $this->document->fresh()->files);
    }

    public function test_can_get_user_who_made_request()
    {
        $this->assertInstanceOf(User::class, $this->document->user);
    }
}
