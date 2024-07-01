<?php

namespace Database\Seeders;

use App\Models\FoodCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FoodCategorySeeder extends Seeder
{

    public function run(): void
    {               //1                 2                  3          4           5                 6                   7             8         9          10           11                 12
        $category = ['Oriental Meals' , 'Western Meals' , 'Grills' , 'Seafood' , 'Hot Appetizers' , 'Cold Appetizers' , 'Pastries' , 'Soups' , 'Salads' , 'Desserts' , 'Eastern Sweets' , 'Cake' ];

        $logo = [
            'Food/Logo/1.png' ,
            'Food/Logo/2.png' ,
            'Food/Logo/3.png' ,
            'Food/Logo/4.png' ,
            'Food/Logo/5.png' ,
            'Food/Logo/6.png' ,
            'Food/Logo/7.png' ,
            'Food/Logo/8.png' ,
            'Food/Logo/9.png' ,
            'Food/Logo/10.png' ,
            'Food/Logo/11.png' ,
            'Food/Logo/12.png' ,
        ];

        for($i = 0 ; $i < count($category) ; $i++)
        {
            FoodCategory::query()->create([
                'category' => $category[$i] ,
                'logo' => $logo[$i]
            ]);
        }
    }
}
