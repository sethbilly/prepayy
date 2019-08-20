<?php

use App\Entities\LoanProduct;
use App\Entities\LoanType;
use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

class LoanProductsControllerTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait, GetMockUploadedFileTrait;

    /**
     * @var User
     */
    private $user;
    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->user = $this->createInstitutionAccountOwner();
        $this->faker = Faker\Factory::create();
    }

    private function setupProducts(): Collection
    {
        return factory(LoanProduct::class, 4)->make(['min_amount' => 800])
            ->map(function(LoanProduct $product, $i) {
                if ($i < 2) {
                    $product->institution()->associate($this->user->institutable);
                }

                $product->save();

                return $product;
            });
    }

    public function test_can_get_institution_products()
    {
        $products = $this->setupProducts();

        $this->actingAs($this->user)
            ->visitRoute('loan_products.index')
            ->assertResponseOk()
            ->seeText('Name')
            ->seeText('Description')
            ->seeText('Min Amount')
            ->seeText('Max Amount')
            ->seeLink('Add New Product', route('loan_products.create'))
            ->seeLink($products[0]->name, route('loan_products.edit', ['product' => $products[0]]))
            ->seeText($products[0]->description)
            ->seeText($products[0]->min_amount)
            ->seeText($products[0]->max_amount)
            ->seeLink($products[1]->name, route('loan_products.edit', ['product' => $products[1]]))
            ->seeText($products[1]->description)
            ->seeText($products[1]->min_amount)
            ->seeText($products[1]->max_amount);
    }

    public function test_displays_add_product_link_if_has_no_products()
    {
        $this->actingAs($this->user)
            ->visitRoute('loan_products.index')
            ->assertResponseOk()
            ->seeText('Your loan products list')
            ->seeText('Create products')
            ->seeLink('Add New Product', route('loan_products.create'));
    }

    public function test_can_browse_product_list()
    {
        $products = $this->setupProducts();
        $minAmount = $products->min('min_amount');

        $this->visitRoute('loan_products.browse')
            ->assertResponseOk()
            ->seeText($products[0]->institution->name)
            ->seeText($products[0]->loanType->name)
            ->seeText($products[0]->interest_per_year . '% APR')
            ->seeText('Total Payable')
            ->seeText('Monthly Payable')
            ->seeText($products[1]->institution->name)
            ->seeText($products[1]->loanType->name)
            ->seeText($products[1]->interest_per_year . '% APR')
            ->seeLink('Apply', route('loan_applications.guidelines', [
                'partner' => $products[0]->institution,
                'product' => $products[0],
                'amount' => $minAmount,
                'tenure' => 1
            ]))
            ->seeLink('Apply', route('loan_applications.guidelines', [
                'partner' => $products[1]->institution,
                'product' => $products[1],
                'amount' => $minAmount,
                'tenure' => 1
            ]))
            ->seeText('I want to borrow')
            ->seeText('I want it for')
            ->seeText('Financial Institutions (Banks)')
            ->seeText('Loan Types')
            ->seeElement('select[name="institution_ids[]"]')
            ->seeElement('select[name="loan_type_id"]')
            ->seeElement('input[name="min_amount"]')
            ->seeElement('input[name="tenure"]')
            ->seeText('Representative example');
    }

    public function test_no_products_to_browse()
    {
        $this->visitRoute('loan_products.browse')
            ->seeText('I want to borrow')
            ->seeText('I want it for')
            ->seeText('Financial Institutions (Banks)')
            ->seeText('Loan Types')
            ->seeElement('select[name="institution_ids[]"]')
            ->seeElement('select[name="loan_type_id"]')
            ->seeElement('input[name="min_amount"]')
            ->seeElement('input[name="tenure"]')
            ->seeText('No loan products were found');
    }

    public function test_can_create_loan_product()
    {
        $productName = $this->faker->name;
        $loanTypes = factory(LoanType::class, 3)->create();

        $this->actingAs($this->user)
            ->visitRoute('loan_products.create')
            ->assertResponseOk()
            ->type($productName, 'name')
            ->type($this->faker->sentence, 'description')
            ->type($this->faker->numberBetween(200, 500), 'min_amount')
            ->type($this->faker->numberBetween(1000, 2000), 'max_amount')
            ->type($this->faker->numberBetween(2, 10), 'interest_per_year')
            ->select($loanTypes->first()->id, 'loan_type_id')
            ->press('Save changes')
            ->seeInDatabase('loan_products', [
                'name' => $productName, 'financial_institution_id' => $this->user->institutable->id
            ])
            ->seeRouteIs('loan_products.index');
    }

    public function test_will_populate_data_to_edit()
    {
        $product = factory(LoanProduct::class)->create(['financial_institution_id' => $this->user->institutable->id]);

        $this->actingAs($this->user)
            ->visitRoute('loan_products.edit', ['product' => $product])
            ->assertResponseOk()
            ->seeInField('name', $product->name)
            ->seeInField('description', $product->description)
            ->seeInField('min_amount', $product->min_amount)
            ->seeInField('max_amount', $product->max_amount)
            ->seeInElement('select[name="loan_type_id"]', $product->loan_type_id)
            ->seeInField('interest_per_year', $product->interest_per_year);
    }

    public function test_can_update_loan_product()
    {
        // todo: fix broken test
//        $product = factory(LoanProduct::class)->create([
//            'financial_institution_id' => $this->user->institutable->id
//        ]);
//
//        $this->assertTrue(true);
//        $this->actingAs($this->user)
//            ->visitRoute('loan_products.edit', ['product' => $product])
//            ->assertResponseOk()
//            ->press('Save changes')
//            ->seeRouteIs('loan_products.index');
    }
}
