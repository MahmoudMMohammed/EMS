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
            'Profile Pictures/Owners & Admins/001.jpg' ,
            'Profile Pictures/Owners & Admins/002.jpg' ,
            'Profile Pictures/Owners & Admins/003.JPEG' ,
            'Profile Pictures/Owners & Admins/004.jpg' ,
            'Profile Pictures/Owners & Admins/005.jpg' ,
            'Profile Pictures/Owners & Admins/006.jpg' ,
            'Profile Pictures/Owners & Admins/007.jpg' ,
            'Profile Pictures/Owners & Admins/008.jpg' ,
            'Profile Pictures/Owners & Admins/009.jpg' ,
            'Profile Pictures/Owners & Admins/010.jpg' ,
            'Profile Pictures/Owners & Admins/011.jpg' ,
            'Profile Pictures/Owners & Admins/012.jpg' ,
            'Profile Pictures/Owners & Admins/013.jpg' ,
            'Profile Pictures/Owners & Admins/014.jpg' ,
            'Profile Pictures/Owners & Admins/015.jpg' ,
            'Profile Pictures/Owners & Admins/016.jpg' ,
            'Profile Pictures/Owners & Admins/017.jpg' ,
            'Profile Pictures/Owners & Admins/018.JPG' ,
            'Profile Pictures/Owners & Admins/019.jpg' ,
            'Profile Pictures/Owners & Admins/020.jpg' ,
            'Profile Pictures/Owners & Admins/021.jpg' ,
            'Profile Pictures/Owners & Admins/022.PNG' ,
            'Profile Pictures/Owners & Admins/023.jpg' ,
            'Profile Pictures/Owners & Admins/024.jpg' ,
            'Profile Pictures/Owners & Admins/025.jpg' ,
            'Profile Pictures/Owners & Admins/026.jpg' ,
            'Profile Pictures/Owners & Admins/027.jpg' ,
            'Profile Pictures/Owners & Admins/028.jpg' ,
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
