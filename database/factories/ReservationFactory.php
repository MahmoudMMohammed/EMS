<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\UserEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 29;
        return [
            'user_id' => $counter++,
            'user_event_id' => UserEvent::factory(),
            'verified' => false,
        ];
    }
}
