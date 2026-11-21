<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockLog>
 */
class StockLogFactory extends Factory
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
            'product_id' => Product::factory(),
            'tanggal' => fake()->date(),
            'jenis' => fake()->randomElement(StockLog::TYPES),
            'jumlah' => fake()->numberBetween(1, 25),
            'keterangan' => fake()->sentence(3),
        ];
    }
}
