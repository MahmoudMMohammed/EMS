<?php

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\Drink;
use App\Models\EventSupplement;
use App\Models\Food;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EventSupplementsFactory extends Factory
{
    protected $model = EventSupplement::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        $food_details = [
        Food::find($this->faker->numberBetween(1,127)),
        Food::find($this->faker->numberBetween(1,127)),
        Food::find($this->faker->numberBetween(1,127))
        ];

        $drinks_details = [
        Drink::find($this->faker->numberBetween(1,48)),
        Drink::find($this->faker->numberBetween(1,48)),
        Drink::find($this->faker->numberBetween(1,48)),
        ];

        $accessories_details = [
        Accessory::find($this->faker->numberBetween(1,71)),
        Accessory::find($this->faker->numberBetween(1,71)),
        Accessory::find($this->faker->numberBetween(1,71)),
        ];
        return [
            'user_event_id' => $counter++,
            'warehouse_id' => $this->faker->numberBetween(1,7),
            'food_details' => json_encode($food_details),
            'drinks_details' => json_encode($drinks_details),
            'accessories_details' => json_encode($accessories_details),
            'total_price' => $this->faker->randomFloat('2', 100000, 10000000),
        ];
    }
}
