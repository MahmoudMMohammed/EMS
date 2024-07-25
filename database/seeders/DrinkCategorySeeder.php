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
    {               //1              2              3             4              5                 6            7
        $category = ['Cold Drinks' , 'Hot Drinks' , 'Milk shake' , 'Cocktails' , 'Natural juices' , 'Coffee' , 'Alcohols'];

        $logo = [
            'Drinks/Logo/13.png' ,
            'Drinks/Logo/14.png' ,
            'Drinks/Logo/15.png' ,
            'Drinks/Logo/16.png' ,
            'Drinks/Logo/17.png' ,
            'Drinks/Logo/18.png' ,
            'Drinks/Logo/19.png' ,
        ];

        for($i = 0 ; $i<count($category) ; $i++)
        {
            DrinkCategory::query()->create([
                'category' => $category[$i] ,
                'logo' => $logo[$i]
            ]);
        }
    }
}
