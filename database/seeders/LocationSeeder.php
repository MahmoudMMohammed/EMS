<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name              = [
            'Oriental Restaurant' , 'LETO Restaurant' , 'Torosa Restaurant' , 'Beroea Restaurant' , 'Cello Restaurant' ,
            'Qashla Cafe' , 'Fairouz Cafe' , 'Bla Bla Cafe' , 'Bean Cafe' , 'Malindo Cafe' , 'Rodmina Cafe' ,
            'LePiano Bar' , 'Blue Bar' , 'Ricky Bar' ,
            'Adam Lounge' , 'Zenobia Lounge' , 'Shahin Lounge' , 'Opera Lounge' , 'Sada Lounge' , 'Al-Narges Lounge' , 'Al-Sharaq Lounge' ,
            'Tishreen Park' , 'Green Park' ,
            'Opera House Theater' ,
            'Al-Galaa Stadium' , 'Al-Basil Stadium' ,
        ];

        $governorate       = [
            'Damascus' , 'Homs' , 'Tartus' , 'Aleppo' , 'Suwayda' ,
            'Damascus' , 'Homs' , 'Tartus' , 'Aleppo' , 'Suwayda' , 'Daraa' ,
            'Damascus' , 'Homs' , 'Tartus' ,
            'Damascus' , 'Homs' , 'Tartus' , 'Aleppo' , 'Suwayda' , 'Daraa' , 'Raqqa' ,
            'Damascus' , 'Homs' ,
            'Damascus' ,
            'Damascus' , 'Homs' ,
        ];

        $address           = [
            'Bab Touma Street' , 'Zaki Al-Arsuzi Street' , 'Marine Corniche Street' , 'Nour Al-Din Al-Zenki Street' , 'Qanawat Street' ,
            'Talla AL-Hajara Street' , 'Al-Hadara Street' , 'Marine Corniche Street' , 'Waddah Al-Yemen Street' , 'Qanawat Street' , 'Al-Shuhada Street' ,
            'Hanania Street' , 'Al-Hadara Street' , 'Al-Thawra Street' ,
            'Old Al-Zahra Street' , 'Al-Waer Street' , 'Marine Corniche Street' , 'Ibn Arabi Street' , 'Al-Mazraa Street' , 'Nawa Street' , 'Al-Mansour Street' ,
            'Beirut Street' , 'Tishreen Street' ,
            'Shukri Al-Quwatli Street' ,
            'Mezzeh Highway' , 'Al-Wafa Street' ,
        ];

        $user_id           = [ 1 , 2 , 3 , 4 , 5 , 6 , 7 , 8 , 9 , 10 , 11 , 12 , 13 , 14 , 15 , 16 , 17 , 18 , 19 , 20 , 21 , 22 , 23 , 24 , 25 , 26];

        $host_id           = [
            1 , 1 , 1 , 1 , 1 ,
            2 , 2 , 2 , 2 , 2 , 2 ,
            3 , 3 , 3 ,
            4 , 4 , 4 , 4 , 4 , 4 , 4 ,
            5 , 5 ,
            6 ,
            7 , 7 ,
        ];

        $capacity          = [
            90 , 70 , 120 , 135 , 100 ,
            75 , 60 , 50 , 40 , 40 , 50 ,
            55 , 50 , 80 ,
            150 , 200 , 240 , 200 , 100 , 180 , 140 ,
            300 , 200 ,
            400 ,
            800 , 600 ,

        ];

        $open_time         = [
            '9:00 AM' , '9:00 AM' , '9:00 AM' , '9:00 AM' , '9:00 AM' ,
            '10:00 AM' , '10:00 AM' , '10:00 AM' , '10:00 AM' , '10:00 AM' , '10:00 AM' ,
            '6:00 PM' , '6:00 PM' , '6:00 PM' ,
            '2:00 PM' , '2:00 PM' , '2:00 PM' , '2:00 PM' , '2:00 PM' , '2:00 PM' , '2:00 PM' ,
            '10:00 AM' , '10:00 AM' ,
            '4:00 PM' ,
            '8:00 AM' , '8:00 AM' ,
        ];

        $close_time       = [
            '12:00 PM' , '12:00 PM' , '12:00 PM' , '12:00 PM' , '12:00 PM' ,
            '12:00 PM' , '12:00 PM' , '12:00 PM' , '12:00 PM' , '12:00 PM' , '12:00 PM' ,
            '4:00 AM' , '4:00 AM' , '4:00 AM' ,
            '2:00 AM' , '2:00 AM' , '2:00 AM' , '2:00 AM' , '2:00 AM' , '2:00 AM' , '2:00 AM' ,
            '12:00 PM' , '12:00 PM' ,
            '12:00 PM' ,
            '10:00 PM' , '10:00 PM' ,
        ];

        $reservation_price = [
            360000 , 250000 , 395000 , 420000 , 320000 ,
            300000 , 230000 , 200000 , 170000 , 195000 , 140000 ,
            340000 , 300000 , 450000 ,
            500000 , 700000 , 900000 , 800000 , 350000 , 600000 , 400000 ,
            950000 , 750000 ,
            1500000 ,
            1800000 , 1600000 ,

        ];

        $x_position        = [
            33.508590 , 34.729690 , 34.889605 , 36.201082 , 32.711644 ,
            33.508819 , 34.714314 , 34.877789 , 36.218053 , 32.716240 , 32.625664 ,
            33.510480 , 34.714993 , 34.883804 ,
            33.495128 , 34.744653 , 34.889693 , 36.204704 , 32.722826 , 32.887352 , 35.945921 ,
            33.516477 , 34.747002 ,
            33.512552 ,
            33.501984 , 34.716503 ,
        ];

        $y_position        = [
            36.315828 , 36.699161 , 35.877339 , 37.163049 , 36.572541 ,
            36.314818 , 36.714953 , 35.881906 , 37.130008 , 36.575742 , 36.105244 ,
            36.317605 , 36.714611 , 35.885410 ,
            36.301741 , 36.679948 , 35.877655 , 37.095927 , 36.566042 , 36.037732 , 39.016428 ,
            36.269127 , 36.668351 ,
            36.278609 ,
            36.253769 , 36.688874 ,
        ];

        $logo = [
            'Location/4.jpg' ,
            'Location/5.jpg' ,
            'Location/9.jpg' ,
            'Location/13.jpg' ,
            'Location/17.jpg' ,
            'Location/21.jpg' ,
            'Location/25.jpg' ,
            'Location/29.jpg' ,
            'Location/33.jpg' ,
            'Location/37.jpg' ,
            'Location/41.jpg' ,
            'Location/45.jpg' ,
            'Location/49.jpg' ,
            'Location/53.jpg' ,
            'Location/60.jpg' ,
            'Location/61.jpg' ,
            'Location/65.jpg' ,
            'Location/69.jpg' ,
            'Location/73.jpg' ,
            'Location/77.jpg' ,
            'Location/81.jpg' ,
            'Location/88.jpg' ,
            'Location/92.jpg' ,
            'Location/96.jpg' ,
            'Location/100.jpg' ,
            'Location/104.jpg' ,

        ];

        for($i = 0 ; $i < count($name) ; $i++)
        {
            Location::query()->create([
                'name' => $name[$i] ,
                'governorate' => $governorate[$i] ,
                'address' => $address[$i] ,
                'user_id' => $user_id[$i] ,
                'host_id' => $host_id[$i] ,
                'capacity' => $capacity[$i] ,
                'open_time' => $open_time[$i] ,
                'close_time' => $close_time[$i] ,
                'reservation_price' => $reservation_price[$i] ,
                'x_position' => $x_position[$i] ,
                'y_position' => $y_position[$i] ,
                'logo' => $logo[$i]
            ]);
        }
    }
}
