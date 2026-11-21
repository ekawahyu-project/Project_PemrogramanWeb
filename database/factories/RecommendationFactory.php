<?php

namespace Database\Factories;

use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recommendation>
 */
class RecommendationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'judul' => fake()->sentence(3),
            'isi' => fake()->paragraph(),
            'prioritas' => fake()->randomElement(Recommendation::PRIORITIES),
            'dibuat' => fake()->date(),
        ];
    }
}
