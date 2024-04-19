<?php

namespace Database\Seeders;

use App\Models\AccessoryCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccessorCategorySeeder extends Seeder
{

    public function run(): void
    {              //1              2           3             4                        5                        6                      7
        $category = ['Decoration' , 'Basics' , 'Religious' , 'Visual Presentations' , 'Electrical Equipment' , 'Lighting Equipment' , 'Audio Equipment'];

        for($i = 0 ; $i < count($category) ; $i++)
        {
            AccessoryCategory::query()->create([
                'category' => $category[$i] ,
            ]);
        }
    }
}
