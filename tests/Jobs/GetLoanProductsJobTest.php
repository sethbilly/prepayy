<?php

use App\Entities\FinancialInstitution;
use App\Entities\LoanProduct;
use App\Jobs\GetLoanProductsJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetLoanProductsJobTest extends TestCase
{
    use DatabaseTransactions;

    private $institutions;

    private $products;

    public function setUp()
    {
        parent::setUp();

        // Setup 6 products belonging to 2 different institutions
        $this->institutions = factory(FinancialInstitution::class, 2)->create();

        $this->products = factory(LoanProduct::class, 6)->make()
            ->map(function (LoanProduct $product, $i) {
                $institution = $i < 3 ? $this->institutions->first() : $this->institutions->last();

                // Min amount range = 100 to 600
                $product->min_amount = 100 + ($i * 100);
                // Max amount range = 300 to 800
                $product->max_amount = $product->min_amount + 200;
                $product->financial_institution_id = $institution->id;
                $product->save();

                return $product;
            });
    }

    public function test_can_get_products_of_institution()
    {
        $req = $this->getAuthenticatedRequest();

        $expectedProducts = $this->products->slice(0, 3);
        $actualProducts = dispatch(new GetLoanProductsJob($req, $this->institutions->first()));

        $this->assertCount(3, $expectedProducts);
        $this->assertCount($expectedProducts->count(), $actualProducts);

        $expectedProducts->each(function (LoanProduct $product, $i) use ($actualProducts) {
            $this->assertEquals($product->id, $actualProducts->items()[$i]->id);
        });
    }

    public function test_can_search_products_by_name()
    {
        $req = $this->getAuthenticatedRequest(['search' => $this->products->first()->name]);

        $actualProducts = dispatch(new GetLoanProductsJob($req));

        $this->assertCount(1, $actualProducts);
        $this->assertEquals($this->products->first()->id, $actualProducts->items()[0]->id);
    }

    public function test_can_search_products_by_min_amount()
    {
        $min = 600;
        $req = $this->getAuthenticatedRequest(['min_amount' => $min]);


        $expectedProducts = $this->products
            ->filter(function (LoanProduct $product) use ($min) {
                return $min <= $product->max_amount;
            })
            // Reset the collection keys
            ->values();
        $actualProducts = dispatch(new GetLoanProductsJob($req));

        $this->assertCount(3, $expectedProducts);
        $this->assertCount($expectedProducts->count(), $actualProducts);

        $expectedProducts->each(function (LoanProduct $product, $i) use ($actualProducts, $min) {
            $this->assertEquals($product->id, $actualProducts->items()[$i]->id);
            $this->assertTrue($actualProducts->items()[$i]->max_amount >= $min);
        });
    }


}
