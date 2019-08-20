<?php

namespace App\Jobs;

class CalculateMonthlyAmountPayableJob
{
    /**
     * @var float
     */
    private $principal;
    /**
     * @var float
     */
    private $monthlyInterestRate;
    /**
     * @var int
     */
    private $tenureInMonths;

    /**
     * Create a new job instance.
     *
     * @param float $principal
     * @param float $yearlyInterestRate - the annual interest rate. Example: 7.5 means
     * the annual interest used in calculations will be 7.5/100
     * @param int $tenureInYears
     */
    public function __construct(
        float $principal,
        float $yearlyInterestRate,
        int $tenureInYears
    ) {
        $this->principal = $principal;
        $this->monthlyInterestRate = ($yearlyInterestRate / (12 * 100));
        $this->tenureInMonths = $tenureInYears * 12;
    }

    /**
     * Execute the job.
     *
     * @return float
     */
    public function handle()
    {
        return $this->calculateAmortization();
    }

    private function calculateAmortization(): float
    {
        // A = P*r*(1 + r)^n/[(1 + r)^n - 1]
        $interestExponentiated = pow((1 + $this->monthlyInterestRate), $this->tenureInMonths);

        $numerator = $this->principal * $this->monthlyInterestRate * $interestExponentiated;
        $denominator = $interestExponentiated - 1;

        return $denominator > 0 ? $numerator / $denominator : 0;
    }
}
