<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'password' => 'password',
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'no_hp' => fake()->numerify('08##########'),
            'nama_usaha' => fake()->company(),
            'kategori_usaha' => fake()->randomElement(User::BUSINESS_CATEGORIES),
            'alamat_usaha' => fake()->city(),
            'foto' => null,
            'remember_token' => Str::random(10),
        ];
    }
}
