<?php
/**
 * Created by PhpStorm.
 * User: kwabena
 * Date: 10/10/17
 * Time: 8:05 PM
 */
use App\Entities\LoanType;
use App\Entities\User;

$factory->define(LoanType::class, function (Faker\Generator $faker) {
   return [
       'name' => $faker->sentence(10),
       'user_id' => factory(User::class, 'partner')->create()->id
   ];
});