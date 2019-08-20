<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 16/01/2017
 * Time: 14:16
 */
use App\Entities\BrandStyle;
use App\Entities\FinancialInstitution;

$factory->define(BrandStyle::class, function(Faker\Generator $faker) {
    return [
        'style' => $faker->sentence(30)
    ];
});

$factory->defineAs(BrandStyle::class, 'partnerStyle', function() use ($factory) {
    $userable = factory(FinancialInstitution::class)->create();
    $rec = $factory->raw(BrandStyle::class);

    return array_merge($rec, ['institutable_id' => $userable->id, 'institutable_type' => $userable->getMorphClass()]);
});