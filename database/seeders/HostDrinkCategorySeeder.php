<?php

namespace Database\Seeders;

use App\Models\HostDrinkCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HostDrinkCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $host_id              = [1 , 1 , 1 , 1 , 2 , 2 , 2 , 2 , 2 , 2 , 3 , 3 , 3 , 3 , 4 , 4 , 4 , 4 , 4 , 4 , 5 , 5 , 5 , 5 , 5 , 5 , 6 , 6 , 6 , 7 , 7 , 7 , 7 , 7 , 7];
        $drink_category_id    = [1 , 2 , 5 , 6 , 1 , 2 , 3 , 4 , 5 , 6 , 1 , 3 , 5 , 7 , 1 , 2 , 3 , 4 , 5 , 6 , 1 , 2 , 3 , 4 , 5 , 6 , 1 , 2 , 6 , 1 , 2 , 3 , 4 , 5 , 6];

        for($i = 0 ; $i < count($host_id) ; $i++)
        {
            HostDrinkCategory::query()->create([
                'drink_category_id' => $drink_category_id[$i] ,
                'host_id' => $host_id[$i] ,
            ]);
        }
    }
}
