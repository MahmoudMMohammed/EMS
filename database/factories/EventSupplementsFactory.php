<?php

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\Drink;
use App\Models\EventSupplement;
use App\Models\Food;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

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

        $food_details = $this->addItemsWithQuantity(Food::class, 127);
        $drinks_details = $this->addItemsWithQuantity(Drink::class, 56);
        $accessories_details = $this->addItemsWithQuantity(Accessory::class, 71);

        return [
            'user_event_id' => $counter++,
            'warehouse_id' => $this->faker->numberBetween(1,7),
            'food_details' => json_encode($food_details),
            'drinks_details' => json_encode($drinks_details),
            'accessories_details' => json_encode($accessories_details),
            'total_price' => $this->faker->randomFloat('2', 100000, 10000000),
        ];
    }
    /////////////////////////////////////////////////////////////////////////////
    private function addItemsWithQuantity($modelClass, $maxId): array
    {
        $items = [];
        for ($i = 0; $i < 3; $i++) {
            // Retrieve the model without triggering accessors
            $itemId = $this->faker->numberBetween(1, $maxId);

            $item = DB::table((new $modelClass)->getTable())
                ->where('id', $itemId)
                ->first();

            // Manually create the item array with the raw price
            $items[] = $item;
        }
        return $items;
    }
    /////////////////////////////////////////////////////////////////////////////

}
