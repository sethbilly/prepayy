<?php

namespace App\Jobs;

use App\Entities\LoanProduct;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class GetLoanPayablesJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Paginator $paginator
     */
    public function __construct(Request $request, Paginator $paginator)
    {
        $this->request = $request;
        $this->paginator = $paginator;
    }

    /**
     * Execute the job.
     *
     * @return Paginator
     */
    public function handle()
    {
        return $this->getPayables();
    }

    private function getPayables(): Paginator
    {
        $principal = $this->request->get('min_amount', LoanProduct::getMinLoanAmount());
        $tenureInYears = $this->request->get('tenure', 1);

        foreach ($this->paginator->items() as &$item) {
            $annualInterestRate = $item->interest_per_year;

            $item->monthly_payable = dispatch(new CalculateMonthlyAmountPayableJob(
                $principal, $annualInterestRate, $tenureInYears
            ));

            $item->tenure_in_months = $tenureInYears * 12;
            $item->total_payable = $item->monthly_payable * $tenureInYears * 12;
            $item->can_be_borrowed = $principal >= $item->min_amount;
        }

        return $this->paginator;
    }
}
