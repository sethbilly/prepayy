<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 22/02/2017
 * Time: 16:32
 */
use App\Entities\LoanApplication;
use App\Entities\RequestedLoanDocument;
use App\Entities\User;

$factory->define(RequestedLoanDocument::class, function(Faker\Generator $faker) {
    return [
        'loan_application_id' => factory(LoanApplication::class)->create()->id,
        'request' => $faker->sentence,
        'response' => $faker->sentence,
        'user_id' => factory(User::class, 'partner')->create()->id
    ];
});