<?php

namespace Database\Factories;

use App\Http\Controllers\UserEventController;
use App\Models\AccessoryCategory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DrinkCategory;
use App\Models\EventSupplement;
use App\Models\FoodCategory;
use App\Models\HostDrinkCategory;
use App\Models\HostFoodCategory;
use App\Models\Location;
use App\Models\MainEvent;
use App\Models\MainEventHost;
use App\Models\MEHAC;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserEvent;
use App\Models\Warehouse;
use App\Models\WarehouseAccessory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserEvent>
 */
class UserEventFactory extends Factory
{
    protected $model = UserEvent::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $startTime = $this->faker->time('H:i:s');
        $endTime = Carbon::createFromFormat('H:i:s', $startTime)->addHours(2)->format('H:i:s'); // Converting to 24-hour format and adding 2 hours
        $mainEventIds = MainEvent::query()->pluck('id')->toArray();
        $uniqueDates = $this->faker->dateTimeBetween(now()->subWeek(), now());

        return [
            'user_id' => $this->faker->numberBetween(29, 38),
            'location_id' => $this->faker->numberBetween(1, 26),
            'main_event_id'=> $this->faker->randomElement($mainEventIds),
            'date' => $uniqueDates,
            'invitation_type' => $this->faker->randomElement(['Public', 'Private']),
            'description' => $this->faker->paragraph,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'num_people_invited' => $this->faker->numberBetween(10, 50),
            'verified' => $this->faker->randomElement(['Pending', 'Confirmed', 'Rejected', 'Finished'])
        ];
    }

}
