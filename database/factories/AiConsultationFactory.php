<?php

namespace Database\Factories;

use App\Models\AiConsultation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiConsultation>
 */
class AiConsultationFactory extends Factory
{
    protected $model = AiConsultation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'role' => AiConsultation::ROLE_USER,
            'content' => $this->faker->sentence(),
        ];
    }

    public function fromModel(): static
    {
        return $this->state(fn (): array => [
            'role' => AiConsultation::ROLE_MODEL,
        ]);
    }
}
