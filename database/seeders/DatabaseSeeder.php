<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Location;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class ,
            MainEventSeeder::class ,
            HostSeeder::class ,
            MainEventHostSeeder::class ,
            LocationSeeder::class ,
            LocationPictureSeeder::class ,
            FoodCategorySeeder::class ,
            FoodSeeder::class ,
            DrinkCategorySeeder::class ,
            DrinkSeeder::class ,
            AccessorCategorySeeder::class ,
            AccessorSeeder::class ,
            WarehouseSeeder::class ,
            HostDrinkCategorySeeder::class ,
            HostFoodCategorySeeder::class ,
            WarehouseAccessorSeeder::class ,
            MehacsSeeder::class ,
            ProfileSeeder::class
        ]);
    }
}
