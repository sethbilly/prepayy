<?php

use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\LoanProduct;
use App\Entities\RequestedLoanDocument;
use App\Entities\User;
use App\Jobs\RequestLoanDocumentJob;
use App\Notifications\LoanDocumentRequested;
use Faker\Factory;

class RequestLoanDocumentJobTest extends TestCase
{
    /**
     * @var Faker\Generator
     */
    private $faker;
    /**
     * @var LoanApplication
     */
    private $application;
    /**
     * @var User
     */
    private $partnerUser;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
        $this->partnerUser = factory(User::class, 'partner')->create();

        $product = factory(LoanProduct::class)->create([
            'financial_institution_id' => $this->partnerUser->id
        ]);
        $this->application = factory(LoanApplication::class)->create([
            'loan_product_id' => $product->id
        ]);
    }

    public function test_can_request_changes_or_additional_information()
    {
        $req = $this->getAuthenticatedRequest([
            'request' => $this->faker->sentence
        ], $this->partnerUser);

        $this->expectsNotification($this->application->user, LoanDocumentRequested::class);
        
        $document = dispatch(new RequestLoanDocumentJob($req, $this->application));

        $this->assertInstanceOf(RequestedLoanDocument::class, $document);
        $this->assertCount(1, $this->application->requestedDocuments);
        $this->assertEquals(
            $req->get('request'),
            $this->application->requestedDocuments->first()->request
        );
    }

    public function test_will_change_loan_status_if_user_is_employer()
    {
        $user = factory(User::class, 'employer')->create();

        $req = $this->getAuthenticatedRequest([
            'request' => $this->faker->sentence
        ], $user);

        $this->expectsNotification($this->application->user, LoanDocumentRequested::class);

        dispatch(new RequestLoanDocumentJob($req, $this->application));

        $this->application = $this->application->fresh();

        $loanStatus = LoanApplicationStatus::getEmployerRequestedInformationStatus();
        $this->assertEquals(
            $loanStatus->status,
            $this->application->loanApplicationStatus->status
        );
    }

    public function test_will_change_loan_status_if_user_is_partner()
    {
        $req = $this->getAuthenticatedRequest([
            'request' => $this->faker->sentence
        ], $this->partnerUser);

        $this->expectsNotification($this->application->user, LoanDocumentRequested::class);

        dispatch(new RequestLoanDocumentJob($req, $this->application));

        $this->application = $this->application->fresh();

        $loanStatus = LoanApplicationStatus::getPartnerRequestedInformationStatus();
        $this->assertEquals(
            $loanStatus->status,
            $this->application->loanApplicationStatus->status
        );
    }
}
