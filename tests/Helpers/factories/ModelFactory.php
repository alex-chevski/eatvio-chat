<?php

use Eatvio\Chat\Models\Conversation;
use Eatvio\Chat\Tests\Helpers\Models\Bot;
use Eatvio\Chat\Tests\Helpers\Models\Client;
use Eatvio\Chat\Tests\Helpers\Models\User;
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

$factory->define(User::class, function (Faker $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => 'xahja87ahjahajhajhja',
    ];
});

$factory->define(Client::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(Bot::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(Conversation::class, function (Faker $faker) {
    return [
        'data' => ['title' => $faker->sentence],
    ];
});
