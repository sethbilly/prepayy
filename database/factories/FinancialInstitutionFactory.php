<?php
/**
 * Created by PhpStorm.
 * User: benjaminmanford
 * Date: 1/12/17
 * Time: 3:38 PM
 */

use App\Entities\FinancialInstitution;

$factory->define(FinancialInstitution::class, function (Faker\Generator $faker) {
    $name = $faker->company . ' ' . str_random();

    return [
        'name' => $name,
        'abbr' => $faker->languageCode,
        'code' => $faker->countryCode,
        'address' => $faker->address,
        'contact_number' => $faker->phoneNumber,
        'email' => $faker->email,
        'slug' => str_slug($name)
    ];
});