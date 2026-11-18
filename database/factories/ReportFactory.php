<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'judul' => fake()->sentence(3),
            'kategori' => fake()->randomElement(Report::CATEGORIES),
            'tanggal_dari' => '2026-07-01',
            'tanggal_sampai' => '2026-07-31',
            'status' => fake()->randomElement(Report::STATUSES),
            'catatan' => fake()->sentence(),
            'pembuat' => 'Administrator',
        ];
    }
}
