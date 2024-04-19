<?php

namespace Database\Seeders;

use App\Models\MEHAC;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MehacsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {                             //wedding in lounge      //wedding in park       //engagement in lounge  //engagement in park   //graduation in lounge   //birthday in r-c-b-l               //Condolences in l-p               //Dining Invitation in r-l    //sport event           //custom
        $main_event_host_id    = [ 1 , 1 , 1 , 1 , 1 , 1 , 2 , 2 , 2 , 2 , 2 , 2 , 3 , 3 , 3 , 3 , 3 , 3 , 4 , 4 , 4 , 4 , 4 , 4 , 5 , 5 , 5 , 5 , 5 , 5 , 6 , 7 , 8 , 8 , 9 , 9 , 9 , 9 , 9 , 10 , 10 , 10 , 11 , 11 , 11 , 11 , 12 , 13 , 13 , 13 , 13 , 13 , 14 , 14 , 14 , 14 , 14 , 15 , 16 , 17 , 17 , 18 , 18 , 18 , 18 , 18 , 18 , 19 , 19 , 19 , 19 , 19 , 19 , 20 , 20 , 20 , 20 , 20 , 21 , 21 , 21 , 21 , 21 , 21 ];
        $accessory_category_id = [ 1 , 3 , 4 , 5 , 6 , 7 , 1 , 2 , 3 , 4 , 5 , 7 , 1 , 3 , 4 , 5 , 6 , 7 , 1 , 2 , 3 , 4 , 5 , 7 , 1 , 3 , 4 , 5 , 6 , 7 , 1 , 1 , 1 , 6 , 1 , 4 , 5 , 6 , 7 ,  1 ,  3 ,  7 ,  1 ,  2 ,  3 ,  7 ,  1 ,  1 ,  4 ,  5 ,  6 ,  7 ,  1 ,  2 ,  4 ,  5 ,  7 ,  1 ,  1 ,  1 ,  6 ,  1 ,  3 ,  4 ,  5 ,  6 ,  7 ,  1 ,  2 ,  3 ,  4 ,  5 ,  7 ,  3 ,  4 ,  5 ,  6 ,  7 ,  1 ,  2 ,  3 ,  4 ,  5 ,  7 ];

        for ($i = 0 ; $i < count($main_event_host_id) ; $i++)
        {
            MEHAC::query()->create([
                'accessory_category_id' => $accessory_category_id[$i] ,
                'main_event_host_id' => $main_event_host_id[$i]
            ]);
        }
    }
}
