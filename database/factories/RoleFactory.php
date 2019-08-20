<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 16/01/2017
 * Time: 14:44
 */
use App\Entities\Role;

$factory->define(Role::class, function(Faker\Generator $faker) {
    return [
        'name' => str_slug($faker->name),
        'display_name' => $faker->name,
        'description' => $faker->sentence,
        'institutable_id' => null,
        'institutable_type' => null,
    ];
});