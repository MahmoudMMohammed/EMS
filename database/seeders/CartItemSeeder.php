<?php

namespace Database\Seeders;

use App\Models\CartItem;
use Database\Factories\CartFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CartItem::factory()->count(25)->create();
    }
}
