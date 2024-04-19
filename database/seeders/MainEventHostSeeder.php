<?php

namespace Database\Seeders;

use App\Models\MainEventHost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MainEventHostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {                               //1    2    3    4    5   6   7   8   9  10  11  12  13  14  15  16  17  18  19  20  21
        $main_event_id             = [1 ,  1 ,  2 ,  2 ,  3 , 4 , 4 , 4 , 4 , 5 , 5 , 6 , 6 , 7 , 8 , 8 , 8 , 8 , 8 , 8 , 8];
        $host_id                   = [4 ,  5 ,  4 ,  5 ,  4 , 1 , 2 , 3 , 4 , 4 , 5 , 1 , 4 , 7 , 1 , 2 , 3 , 4 , 5 , 6 , 7];

        for($i = 0 ; $i < count($main_event_id) ; $i++)
        {
            MainEventHost::query()->create([
                'host_id' => $host_id[$i] ,
                'main_event_id' => $main_event_id[$i] ,
            ]);
        }
    }
}
