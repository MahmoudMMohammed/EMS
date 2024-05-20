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
            'ProfilePictures/Owners&Admins/001.jpg' ,
            'ProfilePictures/Owners&Admins/002.jpg' ,
            'ProfilePictures/Owners&Admins/003.JPEG' ,
            'ProfilePictures/Owners&Admins/004.jpg' ,
            'ProfilePictures/Owners&Admins/005.jpg' ,
            'ProfilePictures/Owners&Admins/006.jpg' ,
            'ProfilePictures/Owners&Admins/007.jpg' ,
            'ProfilePictures/Owners&Admins/008.jpg' ,
            'ProfilePictures/Owners&Admins/009.jpg' ,
            'ProfilePictures/Owners&Admins/010.jpg' ,
            'ProfilePictures/Owners&Admins/011.jpg' ,
            'ProfilePictures/Owners&Admins/012.jpg' ,
            'ProfilePictures/Owners&Admins/013.jpg' ,
            'ProfilePictures/Owners&Admins/014.jpg' ,
            'ProfilePictures/Owners&Admins/015.jpg' ,
            'ProfilePictures/Owners&Admins/016.jpg' ,
            'ProfilePictures/Owners&Admins/017.jpg' ,
            'ProfilePictures/Owners&Admins/018.JPG' ,
            'ProfilePictures/Owners&Admins/019.jpg' ,
            'ProfilePictures/Owners&Admins/020.jpg' ,
            'ProfilePictures/Owners&Admins/021.jpg' ,
            'ProfilePictures/Owners&Admins/022.PNG' ,
            'ProfilePictures/Owners&Admins/023.jpg' ,
            'ProfilePictures/Owners&Admins/024.jpg' ,
            'ProfilePictures/Owners&Admins/025.jpg' ,
            'ProfilePictures/Owners&Admins/026.jpg' ,
            'ProfilePictures/Owners&Admins/027.jpg' ,
            'ProfilePictures/Owners&Admins/028.jpg' ,
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
