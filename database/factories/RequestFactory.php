<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Request;
use App\User;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\App\Request::class, function (Faker $faker) {
    return [
        'id' => null,
        'url' => $faker->url,
        'status' => $faker->randomElement([Request::STATUS_NEW, Request::STATUS_PROCESSING, Request::STATUS_ERROR, Request::STATUS_DONE]),
        'http_code' => null
    ];
});
