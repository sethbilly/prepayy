<?php

use App\Entities\LoanProduct;
use App\Jobs\GetLoanPayablesJob;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class GetLoanPayablesJobTest extends TestCase
{
    /**
     * @var Paginator
     */
    private $paginator;

    public function setUp()
    {
        parent::setUp();
        factory(LoanProduct::class, 4)->create();
        $this->paginator = LoanProduct::paginate(5);
    }

    public function test_will_add_payable_information()
    {
        $req = new Request();
        $products = dispatch(new GetLoanPayablesJob($req, $this->paginator));

        $products->each(function (LoanProduct $product) {
            self::assertInternalType('float', $product->monthly_payable);
            self::assertInternalType('float', $product->total_payable);
            self::assertInternalType('int', $product->tenure_in_months);
        });
    }
}
