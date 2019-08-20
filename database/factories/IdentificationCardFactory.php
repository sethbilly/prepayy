<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 31/01/2017
 * Time: 16:20
 */
use App\Entities\IdentificationCard;
use App\Entities\User;
use Carbon\Carbon;

$factory->define(IdentificationCard::class, function(Faker\Generator $faker) {
    $types = IdentificationCard::getIdentificationTypes();

    return [
        'type' => $types[array_rand($types)],
        'number' => $faker->creditCardNumber,
        'issue_date' => Carbon::now()->subMonths(6),
        'expiry_date' => Carbon::now()->addYears(2),
        'user_id' => factory(User::class)->create()->id
    ];
});