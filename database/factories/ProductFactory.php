<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hargaBeli = fake()->numberBetween(5000, 50000);

        return [
            'user_id' => User::factory(),
            'nama' => fake()->unique()->words(3, true),
            'kategori' => fake()->randomElement(Product::CATEGORIES),
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaBeli + fake()->numberBetween(5000, 30000),
            'satuan' => fake()->randomElement(Product::UNITS),
            'stok' => fake()->numberBetween(5, 50),
            'stok_min' => 5,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'stok' => 3,
            'stok_min' => 10,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'stok' => 0,
            'stok_min' => 5,
        ]);
    }
}
