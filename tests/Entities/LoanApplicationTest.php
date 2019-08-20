<?php

use App\Entities\Employer;
use App\Entities\Guarantor;
use App\Entities\IdentificationCard;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\LoanProduct;
use App\Entities\RequestedLoanDocument;
use App\Entities\User;
use CloudLoan\Traits\UuidModel;

class LoanApplicationTest extends TestCase
{
    /**
     * @var LoanApplication
     */
    private $application;

    public function setUp()
    {
        parent::setUp();
        $this->application = factory(LoanApplication::class)->create();
    }

    public function test_can_get_uuid()
    {
        $this->assertNotNull($this->application->uuid);
        $this->assertTrue(preg_match(UuidModel::$uuid4Pattern,
                $this->application->uuid) == 1);
    }

    public function test_can_get_guarantor()
    {
        $this->assertInstanceOf(Guarantor::class, $this->application->guarantor);
    }

    public function test_can_get_employer()
    {
        $this->assertInstanceOf(Employer::class, $this->application->employer);
    }

    public function test_can_get_application_owner()
    {
        $this->assertInstanceOf(User::class, $this->application->user);
        $this->assertTrue($this->application->user->isBorrower());
    }

    public function test_can_get_owners_identification_card()
    {
        $this->assertInstanceOf(IdentificationCard::class,
            $this->application->identificationCard);
    }

    public function test_can_get_application_status()
    {
        $this->assertInstanceOf(LoanApplicationStatus::class,
            $this->application->loanApplicationStatus);
    }

    public function test_can_get_loan_product()
    {
        $this->assertInstanceOf(LoanProduct::class, $this->application->loanProduct);
    }

    public function test_can_get_requested_documents()
    {
        $this->assertCount(0, $this->application->requestedDocuments);

        factory(RequestedLoanDocument::class, 2)->create([
            'loan_application_id' => $this->application->id
        ]);

        $this->application = $this->application->fresh();
        $this->assertCount(2, $this->application->requestedDocuments);
    }

    public function test_can_get_partner_requested_information()
    {
        $user = factory(User::class, 'partner')->create([
            'institutable_id' => $this->application->loanProduct->financial_institution_id
        ]);

        $this->application->save();

        factory(RequestedLoanDocument::class, 3)
            ->make([
                'loan_application_id' => $this->application->id
            ])
            ->map(function (RequestedLoanDocument $doc, $i) use ($user) {
                if ($i < 2) {
                    $doc->user()->associate($user);
                }
                $doc->save();

                return $doc;
            });

        $this->application = $this->application->fresh();
        $this->assertCount(2, $this->application->getPartnerRequestedInformation());
    }

    public function test_can_get_employer_requested_information()
    {
        $user = factory(User::class, 'employer')->create([
            'institutable_id' => $this->application->employer_id
        ]);

        factory(RequestedLoanDocument::class, 3)
            ->make([
                'loan_application_id' => $this->application->id
            ])
            ->map(function (RequestedLoanDocument $doc, $i) use ($user) {
                if ($i < 2) {
                    $doc->user()->associate($user);
                }
                $doc->save();

                return $doc;
            });

        $this->application = $this->application->fresh();
        $this->assertCount(2, $this->application->getEmployerRequestedInformation());
    }
}
