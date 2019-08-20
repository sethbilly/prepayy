<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 07/02/2017
 * Time: 18:24
 */
use App\Entities\Employer;
use App\Entities\Guarantor;
use App\Entities\IdentificationCard;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\LoanProduct;
use App\Entities\User;

$factory->define(LoanApplication::class, function(Faker\Generator $faker) {
    $user = factory(User::class)->create();
    $employer = factory(Employer::class)->create();
    $user->employers()->attach($employer->id, [
        'position' => $faker->jobTitle,
        'contract_type' => 'Part Time',
        'salary' => $faker->numberBetween(2000, 4000)
    ]);
    $user->guarantors()->save(factory(Guarantor::class)->make());
    $user->idCards()->save(factory(IdentificationCard::class)->make());
    $status = LoanApplicationStatus::getDraftStatus();

    $product = factory(LoanProduct::class)->create();
    $product->institution->partnerEmployers()->attach($employer->id);

    return [
        'employer_id' => $employer->id,
        'guarantor_id' => $user->guarantors->first()->id,
        'identification_card_id' => $user->idCards->first()->id,
        'user_id' => $user->id,
        'loan_application_status_id' => $status->id,
        'loan_product_id' => $product->id,
        'interest_per_year' => $product->interest_per_year,
        'amount' => $faker->numberBetween($product->min_amount, $product->max_amount),
        'tenure_in_years' => $faker->numberBetween(1, 10)
    ];
});