<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'status' => Order::STATUS_PENDING,
            'quantity' => fake()->numberBetween(1, 10),
            'total' => fake()->randomFloat(2, 10, 300),
        ];
    }
}
