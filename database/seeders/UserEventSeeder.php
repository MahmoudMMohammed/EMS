<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\UserEvent;
use Database\Factories\UserEventFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserEvent::factory()
        ->count(10)
        ->create();
    }
}
