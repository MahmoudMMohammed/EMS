<?php

namespace Database\Seeders;

use App\Models\DrinkCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DrinkCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {               //1              2              3             4              5                 6
        $category = ['Cold Drinks' , 'Hot Drinks' , 'Milk shake' , 'Cocktails' , 'Natural juices' , 'Coffee'];
        for($i = 0 ; $i<count($category) ; $i++)
        {
            DrinkCategory::query()->create([
                'category' => $category[$i]
            ]);
        }
    }
}
