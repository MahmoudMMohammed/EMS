<?php

namespace Database\Seeders;

use App\Models\MainEvent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MainEventSeeder extends Seeder
{

    public function run(): void
    {
                //1          2              3              4             5               6                     7                    8
        $name = ['Wedding' , 'Engagement' , 'Graduation' , 'Birthday' , 'Condolences' , 'Dining Invitation'  , 'Sports Event' , 'Custom'];

        for($i=0 ; $i<count($name) ; $i++)
        {
            MainEvent::query()->create([
                'name'=>$name[$i] ,
            ]);
        }
    }
}
