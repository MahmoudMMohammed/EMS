<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => $this->faker->numberBetween(1, 10),
            'itemable_id' => $this->faker->numberBetween(1, 30),
            'itemable_type' => $this->faker->randomElement(['App\Models\Food', 'App\Models\Drink', 'App\Models\Accessory']),
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}
