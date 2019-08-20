<?php

use App\Entities\BrandStyle;
use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\LoanApplication;
use App\Entities\LoanProduct;
use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class FinancialInstitutionTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var FinancialInstitution
     */
    private $institution;

    public function setUp()
    {
        parent::setUp();

        $this->institution = factory(FinancialInstitution::class)->create();
    }

    public function test_can_slugify_slug_attribute()
    {
        $slug = 'Barclays Bank';
        $this->institution->slug = $slug;

        $this->assertEquals(str_slug($slug), $this->institution->slug);
    }

    public function test_can_get_account_owner()
    {
        $this->assertNull($this->institution->accountOwner);

        $user = $this->createInstitutionAccountOwner($this->institution);

        $this->institution = $this->institution->fresh();
        $this->assertInstanceOf(User::class, $this->institution->accountOwner);
        $this->assertEquals($user->id, $this->institution->accountOwner->id);
    }

    public function test_can_get_dashboard_branding()
    {
        $this->assertNull($this->institution->dashboardBranding);

        factory(BrandStyle::class, 'partnerStyle')->create(['institutable_id' => $this->institution->id]);

        $this->assertInstanceOf(BrandStyle::class, $this->institution->fresh()->dashboardBranding);
    }

    public function test_can_get_route_key_name()
    {
        $this->assertEquals('slug', $this->institution->getRouteKeyName());
    }

    public function test_can_get_partner_employers()
    {
        $this->assertCount(0, $this->institution->partnerEmployers);

        $emp = factory(Employer::class)->create();

        DB::table(FinancialInstitution::TABLE_PARTNER_EMPLOYERS)->insert([
            'financial_institution_id' => $this->institution->id,
            'employer_id' => $emp->id
        ]);

        $this->institution = $this->institution->fresh();
        $this->assertCount(1, $this->institution->partnerEmployers);
        $this->assertEquals($emp->id, $this->institution->partnerEmployers->first()->id);
    }

    public function test_can_get_loan_products()
    {
        $this->assertCount(0, $this->institution->loanProducts);

        factory(LoanProduct::class, 2)->create(['financial_institution_id' => $this->institution->id]);

        $this->assertCount(2, $this->institution->fresh()->loanProducts);
    }

    public function test_can_get_loan_applications()
    {
        $this->assertCount(0, $this->institution->loanApplications);
        
        $applications = factory(LoanProduct::class, 2)->create(['financial_institution_id' => $this->institution->id])
            ->map(function(LoanProduct $product) {
                return factory(LoanApplication::class)->create(['loan_product_id' => $product->id]);
            });

        $this->assertCount($applications->count(), $this->institution->fresh()->loanApplications);
    }

    public function test_can_get_staff_members()
    {
        $this->assertCount(0, $this->institution->staffMembers);

        factory(User::class, 2)->create([
            'institutable_id' => $this->institution->id,
            'institutable_type' => $this->institution->getMorphClass()
        ]);

        $this->assertCount(2, $this->institution->fresh()->staffMembers);
    }
}
