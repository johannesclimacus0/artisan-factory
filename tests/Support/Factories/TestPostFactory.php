<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JohannesClimacus\ArtisanFactory\Tests\Support\Models\TestPost;
use JohannesClimacus\ArtisanFactory\Tests\Support\Models\TestUser;

/** @extends Factory<TestPost> */
final class TestPostFactory extends Factory
{
    protected $model = TestPost::class;

    public function definition(): array
    {
        return [
            'test_user_id' => TestUser::factory(),
            'sender_id' => TestUser::factory(),
            'title' => fake()->sentence()
        ];
    }
}
