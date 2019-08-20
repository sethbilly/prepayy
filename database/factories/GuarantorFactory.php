<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 07/02/2017
 * Time: 18:22
 */
use App\Entities\Guarantor;
use App\Entities\User;

$factory->define(Guarantor::class, function(Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'contact_number' => $faker->phoneNumber,
        'employer' => $faker->company,
        'position' => $faker->jobTitle,
        'years_known' => 2,
        'relationship' => 'Colleague',
        'user_id' => factory(User::class)->create()->id
    ];
});