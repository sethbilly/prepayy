<?php

use App\Entities\LoanProduct;
use App\Entities\LoanType;

class LoanTypeTest extends TestCase
{
    /**
     * @var LoanType
     */
    private $loanType;

    public function setUp()
    {
        parent::setUp();
        $this->loanType = factory(LoanType::class)->create();
    }

    public function test_returns_zero_if_has_no_associated_loans()
    {
        $this->assertEquals(0, $this->loanType->loans_count);
    }

    public function test_returns_count_of_associated_loans()
    {
        factory(LoanProduct::class, 2)->make()
            ->each(function (LoanProduct $product) {
                $product->loanType()->associate($this->loanType);
                $product->save();
            });

        $this->assertEquals(2, $this->loanType->loans_count);
    }
}
