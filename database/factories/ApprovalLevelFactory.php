<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 20/01/2017
 * Time: 11:52
 */
use App\Entities\ApprovalLevel;

$factory->define(ApprovalLevel::class, function(Faker\Generator $faker) {
    $name = $faker->name;
    
    return [
        'name' => $name,
        'slug' => $name,
        'institutable_id' => null,
        'institutable_type' => null
    ];
});