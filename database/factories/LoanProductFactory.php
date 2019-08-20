<?php

use App\Entities\FinancialInstitution;
use App\Entities\LoanProduct;
use App\Entities\LoanType;

$factory->define(LoanProduct::class, function(Faker\Generator $faker) {
    $name = $faker->name . ' ' . str_random();

    return [
        'name' => $name,
        'slug' => $name,
        'description' => $faker->sentence,
        'min_amount' => $faker->numberBetween(200, 1000),
        'max_amount' => $faker->numberBetween(2000, 5000),
        'interest_per_year' => $faker->numberBetween(1, 6),
        'financial_institution_id' => factory(FinancialInstitution::class)->create()->id,
        'loan_type_id' => factory(LoanType::class)->create()->id
    ];
});