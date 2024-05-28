<?php

namespace Database\Seeders;

use App\Models\EventSupplement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSupplementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EventSupplement::factory()
            ->count(10)
            ->create();
    }
}
