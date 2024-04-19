<?php

namespace Database\Seeders;

use App\Models\Host;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {           //1             2        3       4          5         6           7
        $name = ['Restaurant' , 'Cafe' , 'Bar' , 'Lounge' , 'Park' , 'Theater' , 'Stadium'];
        $picture = ['Hosts/1.jpg' , 'Hosts/2.jpg' , 'Hosts/3.jpg' , 'Hosts/4.jpg' , 'Hosts/5.jpg' , 'Hosts/6.jpg' , 'Hosts/7.jpg'];
        $recommended_for = ['For food determination' , 'For small gatherings' , 'For parties with friends' , 'For large meetings' , 'For barbecue parties' , 'For visual presentations' , 'For huge events'];

        for($i = 0 ; $i<count($name) ; $i++){
            Host::query()->create([
                'name' => $name[$i] ,
                'picture' => $picture[$i] ,
                'recommended_for' => $recommended_for[$i],
                'recommended_picture' => 'Hosts/8.png'
            ]);
        }
    }
}
