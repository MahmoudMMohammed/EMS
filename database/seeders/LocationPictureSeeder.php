<?php

namespace Database\Seeders;

use App\Models\LocationPicture;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationPictureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $location_id = [
            1 , 1 , 1 , 1 ,         //Oriental Restaurant
            2 , 2 , 2 , 2 ,         //LETO Restaurant
            3 , 3 , 3 , 3 ,         //Torosa Restaurant
            4 , 4 , 4 , 4 ,         //Beroea Restaurant
            5 , 5 , 5 , 5 ,         //Cello Restaurant

            6 , 6 , 6 , 6 ,         //Qashla Cafe
            7 , 7 , 7 , 7 ,         //Fairouz Cafe
            8 , 8 , 8 , 8 ,         //Bla Bla Cafe
            9 , 9 , 9 , 9 ,         //Bean Cafe
            10 , 10 , 10 , 10 ,     //Malindo Cafe
            11 , 11 , 11 , 11 ,     //Rodmina Cafe

            12 , 12 , 12 , 12 ,     //LePiano Bar
            13 , 13 , 13 , 13 ,     //Blue Bar
            14 , 14 , 14 , 14 ,     //Ricky Bar

            15 , 15 , 15 , 15 ,     //Adam Lounge
            16 , 16 , 16 , 16 ,     //Zenobia Lounge
            17 , 17 , 17 , 17 ,     //Shahin Lounge
            18 , 18 , 18 , 18 ,     //Opera Lounge
            19 , 19 , 19 , 19 ,     //Sada Lounge
            20 , 20 , 20 , 20 ,     //Al-Narges Lounge
            21 , 21 , 21 , 21 ,     //Al-Sharaq Lounge

            22 , 22 , 22 , 22 ,     //Tishreen Park
            23 , 23 , 23 , 23 ,     //Green Park

            24 , 24 , 24 , 24 ,     //Opera House Theater

            25 , 25 , 25 , 25 ,     //Al-Galaa Stadium
            26 , 26 , 26 , 26 ,     //Al-Basil Stadium
        ];

        $picture = [
            'Location/1.jpg' ,   //Oriental Restaurant
            'Location/2.jpg' ,
            'Location/3.jpg' ,
            'Location/4.jpg' ,

            'Location/5.jpg' ,   //LETO Restaurant
            'Location/6.jpg' ,
            'Location/7.jpg' ,
            'Location/8.jpg' ,

            'Location/9.jpg' ,   //Torosa Restaurant
            'Location/10.jpg' ,
            'Location/11.jpg' ,
            'Location/12.jpg' ,

            'Location/13.jpg' ,  //Beroea Restaurant
            'Location/14.jpg' ,
            'Location/15.jpg' ,
            'Location/16.jpg' ,

            'Location/17.jpg' ,  //Cello Restaurant
            'Location/18.jpg' ,
            'Location/19.jpg' ,
            'Location/20.jpg' ,

            'Location/21.jpg' ,  //Qashla Cafe
            'Location/22.jpg' ,
            'Location/23.jpg' ,
            'Location/24.jpg' ,

            'Location/25.jpg' ,  //Fairouz Cafe
            'Location/26.jpg' ,
            'Location/27.jpg' ,
            'Location/28.jpg' ,

            'Location/29.jpg' ,  //Bla Bla Cafe
            'Location/30.jpg' ,
            'Location/31.jpg' ,
            'Location/32.jpg' ,

            'Location/33.jpg' ,  //Bean Cafe
            'Location/34.jpg' ,
            'Location/35.jpg' ,
            'Location/36.jpg' ,

            'Location/37.jpg' ,  //Malindo Cafe
            'Location/38.jpg' ,
            'Location/39.jpg' ,
            'Location/40.jpg' ,

            'Location/41.jpg' ,  //Rodmina Cafe
            'Location/42.jpg' ,
            'Location/43.jpg' ,
            'Location/44.jpg' ,

            'Location/45.jpg' ,  //LePiano Bar
            'Location/46.jpg' ,
            'Location/47.jpg' ,
            'Location/48.jpg' ,

            'Location/49.jpg' ,  //Blue Bar
            'Location/50.jpg' ,
            'Location/51.jpg' ,
            'Location/52.jpg' ,

            'Location/53.jpg' ,  //Ricky Bar
            'Location/54.jpg' ,
            'Location/55.jpg' ,
            'Location/56.jpg' ,

            'Location/57.jpg' ,  //Adam Lounge
            'Location/58.jpg' ,
            'Location/59.jpg' ,
            'Location/60.jpg' ,

            'Location/61.jpg' ,  //Zenobia Lounge
            'Location/62.jpg' ,
            'Location/63.jpg' ,
            'Location/64.jpg' ,

            'Location/65.jpg' ,  //Shahin Lounge
            'Location/66.jpg' ,
            'Location/67.jpg' ,
            'Location/68.jpg' ,

            'Location/69.jpg' ,  //Opera Lounge
            'Location/70.jpg' ,
            'Location/71.jpg' ,
            'Location/72.jpg' ,

            'Location/73.jpg' ,  //Sada Lounge
            'Location/74.jpg' ,
            'Location/75.jpg' ,
            'Location/76.jpg' ,

            'Location/77.jpg' ,  //Al-Narges Lounge
            'Location/78.jpg' ,
            'Location/79.jpg' ,
            'Location/80.jpg' ,

            'Location/81.jpg' ,  //Al-Sharaq Lounge
            'Location/82.jpg' ,
            'Location/83.jpg' ,
            'Location/84.jpg' ,

            'Location/85.jpg' ,  //Tishreen Park
            'Location/86.jpg' ,
            'Location/87.jpg' ,
            'Location/88.jpg' ,

            'Location/89.jpg' ,  //Green Park
            'Location/90.jpg' ,
            'Location/91.jpg' ,
            'Location/92.jpg' ,

            'Location/93.jpg' ,  //Opera House Theater
            'Location/94.jpg' ,
            'Location/95.jpg' ,
            'Location/96.jpg' ,

            'Location/97.jpg' ,  //Al-Galaa Stadium
            'Location/98.jpg' ,
            'Location/99.jpg' ,
            'Location/100.jpg' ,

            'Location/101.jpg' , //Al-Basil Stadium
            'Location/102.jpg' ,
            'Location/103.jpg' ,
            'Location/104.jpg' ,

        ];

        for($i = 0 ; $i < count($location_id) ; $i++)
        {
            LocationPicture::query()->create([
                'location_id' => $location_id[$i] ,
                'picture' => $picture[$i]
            ]);
        }
    }
}
