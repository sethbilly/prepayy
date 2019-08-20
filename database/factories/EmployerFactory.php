<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 17/01/2017
 * Time: 13:42
 */
use App\Entities\Employer;
use App\Entities\User;

$factory->define(Employer::class, function(Faker\Generator $faker) {
    $company = $faker->company . ' ' . str_random();

    return [
        'name' => $company,
        'slug' => $company,
        'address' => $faker->address,
        'user_id' => factory(User::class, 'appOwner')->create()->id
    ];
});