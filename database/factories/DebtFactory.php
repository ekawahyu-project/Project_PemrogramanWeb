<?php

namespace Database\Factories;

use App\Models\Debt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jumlah = fake()->numberBetween(50000, 500000);
        $dibayar = fake()->randomElement([0, (int) round($jumlah / 2), $jumlah]);

        return [
            'user_id' => User::factory(),
            'nama_pihak' => fake()->name(),
            'no_hp' => '08123456789',
            'jenis' => fake()->randomElement(Debt::TYPES),
            'jumlah' => $jumlah,
            'dibayar' => $dibayar,
            'tanggal' => now()->subDays(5)->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => Debt::resolveStatus($jumlah, $dibayar),
            'keterangan' => fake()->sentence(4),
        ];
    }
}
