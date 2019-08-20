<?php

use App\Entities\FileEntry;
use App\Entities\FinancialInstitution;
use App\Entities\LoanProduct;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoanProductTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var LoanProduct
     */
    private $loanProduct;

    public function setUp()
    {
        parent::setUp();
        $this->loanProduct = factory(LoanProduct::class)->create();
    }

    public function test_can_get_institution()
    {
        $this->assertInstanceOf(FinancialInstitution::class, $this->loanProduct->institution);
    }

    public function test_will_slugify_slug()
    {
        $name = 'Name of product';
        $this->loanProduct->slug = $name;

        $this->assertEquals(str_slug($name), $this->loanProduct->slug);
    }

    public function test_can_get_images()
    {
        $this->assertCount(0, $this->loanProduct->images);

        factory(FileEntry::class, 2)->create([
            'fileable_id' => $this->loanProduct->id,
            'fileable_type' => $this->loanProduct->getMorphClass(),
        ]);

        $this->loanProduct = $this->loanProduct->fresh();
        $this->assertCount(2, $this->loanProduct->images);
    }

    public function test_can_get_route_key_name()
    {
        $this->assertEquals('slug', $this->loanProduct->getRouteKeyName());
    }

    public function test_can_find_by_institution_and_slug()
    {
        $product = LoanProduct::findByInstitutionAndSlug($this->loanProduct->institution, $this->loanProduct->slug);

        $this->assertInstanceOf(LoanProduct::class, $product);
        $this->assertEquals($this->loanProduct->id, $product->id);
    }

    public function test_can_get_min_loan_amount()
    {
        LoanProduct::where('id', '>', 0)->delete();

        factory(LoanProduct::class, 4)->make()
            ->map(function (LoanProduct $product, $i) {
                $product->min_amount = 200 + $i;
                $product->save();

                return $product;
            });

        self::assertEquals(200, LoanProduct::getMinLoanAmount());
    }

    public function test_can_get_max_loan_amount()
    {
        LoanProduct::where('id', '>', 0)->delete();

        factory(LoanProduct::class, 4)->make()
            ->map(function (LoanProduct $product, $i) {
                $product->max_amount = 200 + ($i * 100);
                $product->save();

                return $product;
            });

        self::assertEquals(500, LoanProduct::getMaxLoanAmount());
    }
}
