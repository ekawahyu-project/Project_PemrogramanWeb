<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => null,
            'tanggal' => fake()->date(),
            'keterangan' => fake()->sentence(3),
            'jenis' => fake()->randomElement(Transaction::TYPES),
            'jumlah' => fake()->numberBetween(10000, 500000),
        ];
    }

    public function income(?Product $product = null): static
    {
        return $this->state(fn () => [
            'jenis' => Transaction::TYPE_INCOME,
            'product_id' => $product?->id,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn () => [
            'jenis' => Transaction::TYPE_EXPENSE,
            'product_id' => null,
        ]);
    }
}
