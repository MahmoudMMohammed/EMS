<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_id = [1 , 2 , 3 , 4 , 5 , 6 , 7 , 8 , 9 , 10 , 11 , 12 , 13 , 14 , 15 , 16 , 17 , 18 , 19 , 20 , 21 , 22 , 23 , 24 , 25 , 26 , 27 , 28];

        $profile_picture = [
            'Users/001.jpg' ,
            'Users/002.jpg' ,
            'Users/003.JPEG' ,
            'Users/004.jpg' ,
            'Users/005.jpg' ,
            'Users/006.jpg' ,
            'Users/007.jpg' ,
            'Users/008.jpg' ,
            'Users/009.jpg' ,
            'Users/010.jpg' ,
            'Users/011.jpg' ,
            'Users/012.jpg' ,
            'Users/013.jpg' ,
            'Users/014.jpg' ,
            'Users/015.jpg' ,
            'Users/016.jpg' ,
            'Users/017.jpg' ,
            'Users/018.JPG' ,
            'Users/019.jpg' ,
            'Users/020.jpg' ,
            'Users/021.jpg' ,
            'Users/022.PNG' ,
            'Users/023.jpg' ,
            'Users/024.jpg' ,
            'Users/025.jpg' ,
            'Users/026.jpg' ,
            'Users/027.jpg' ,
            'Users/028.jpg' ,
        ];

        for($i = 0 ; $i < count($user_id) ; $i++)
        {
            Profile::query()->create([
                'user_id' => $user_id[$i] ,
                'profile_picture' => $profile_picture[$i]
            ]);
        }
    }
}
