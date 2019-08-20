<?php

use App\Jobs\CalculateMonthlyAmountPayableJob;

class CalculateMonthlyAmountPayableJobTest extends TestCase
{
    public function test_will_calculate_loan_monthly_payable()
    {
        $tenureInYears = 5;
        $principal = 20000;
        $annualInterestRate = 7.5;

        $monthlyPayable = dispatch(new CalculateMonthlyAmountPayableJob(
            $principal, $annualInterestRate, $tenureInYears)
        );

        self::assertEquals(400.76, round($monthlyPayable, 2));
    }
}
