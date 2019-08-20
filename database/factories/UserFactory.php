<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\User;

$factory->define(User::class, function (Faker\Generator $faker) {
    $email = $faker->unique()->safeEmail;

    return [
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'othernames' => $faker->firstName,
        'email' => $email,
        'password' => $email,
        'ssnit' => $faker->creditCardNumber,
        'contact_number' => $faker->phoneNumber,
        'remember_token' => str_random(10),
        'institutable_id' => null,
        'institutable_type' => null,
        'approval_level_id' => null
    ];
});

$factory->defineAs(User::class, 'appOwner', function(Faker\Generator $faker) use ($factory) {
    return array_merge(
        $factory->raw(User::class),
        ['is_app_owner' => 1, 'is_account_owner' => 1]
    );
});

$factory->defineAs(User::class, 'partner', function(Faker\Generator $faker) use ($factory) {
    $partner = factory(FinancialInstitution::class)->create();
    
    return array_merge(
        $factory->raw(User::class),
        [
            'institutable_id' => $partner->id,
            'institutable_type' => $partner->getMorphClass()
        ]
    );
});

$factory->defineAs(User::class, 'employer', function(Faker\Generator $faker) use ($factory) {
    $employer = factory(Employer::class)->create();

    return array_merge(
        $factory->raw(User::class),
        [
            'institutable_id' => $employer->id,
            'institutable_type' => $employer->getMorphClass()
        ]
    );
});
