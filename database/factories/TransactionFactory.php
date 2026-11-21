<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
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
            'user_id' => User::factory(),
            'product_id' => null,
            'tanggal' => fake()->date(),
            'keterangan' => fake()->sentence(3),
            'jenis' => fake()->randomElement(Transaction::TYPES),
            'jumlah' => fake()->numberBetween(10000, 500000),
        ];
    }

    public function income(?Product $product = null): static
    {
        return $this->state(function () use ($product): array {
            $state = [
                'jenis' => Transaction::TYPE_INCOME,
                'product_id' => $product?->id,
            ];

            if ($product?->user_id !== null) {
                $state['user_id'] = $product->user_id;
            }

            return $state;
        });
    }

    public function expense(): static
    {
        return $this->state(fn () => [
            'jenis' => Transaction::TYPE_EXPENSE,
            'product_id' => null,
        ]);
    }
}
