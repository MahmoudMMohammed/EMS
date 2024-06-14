<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use App\Services\GenderService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 29;
        $user = User::find($counter);
        $gender = GenderService::getGenderByName($user->name);
        return [
            'user_id' => $counter++,
            'phone_number' => $this->faker->regexify('09[0-9]{8}'),
            'balance' => $this->faker->randomFloat(2,10000,100000),
            'birth_date' => $this->faker->dateTimeThisCentury("2000-01-01"),
            'profile_picture' => "ProfilePictures/Users/Defaults/$gender.png",
            'preferred_language' => "en",
            'about_me' => $this->faker->paragraph,
            'place_of_residence' => $this->faker->address,
            'gender' => $gender,
        ];
    }
}
