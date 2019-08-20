<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 24/01/2017
 * Time: 14:31
 */
$factory->define(\App\Entities\FileEntry::class, function (Faker\Generator $faker) {
    return [
        'filename' => str_random(20),
        'original_filename' => $faker->sentence,
        'description' => $faker->sentence,
        'mime' => $faker->mimeType,
        'bucket' => 'providers',
        'fileable_id' => null,
        'fileable_type' => null
    ];
});