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

        $phone_number = [
            '0981692323' ,
            '0992653253' ,
            '0997561410' ,
            '0959196261' ,
            '0994781267' ,
            '0997933208' ,
            '0992817928' ,
            '0981544758' ,
            '0994273569' ,
            '0968275808' ,
            '0993621541' ,
            '0997337466' ,
            '0942211235' ,
            '0946901218' ,
            '0935211793' ,
            '0966432799' ,
            '0962692922' ,
            '0934894321' ,
            '0945500305' ,
            '0937130372' ,
            '0981104287' ,
            '0997842324' ,
            '0981695444' ,
            '0945456631' ,
            '0978564411' ,
            '0993484732' ,
            '0936593673' ,
            '0930937446' ,

        ];



        $profile_picture = [
            'ProfilePictures/Owners&Admins/001.jpg' ,
            'ProfilePictures/Owners&Admins/002.png' ,
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

        $birthday = [
            '2003-10-01' ,
            '2004-11-02' ,
            '1990-12-03' ,
            '1991-01-04' ,
            '1992-02-05' ,
            '1993-03-06' ,
            '1994-04-07' ,
            '1995-05-08' ,
            '1996-06-09' ,
            '1997-07-10' ,
            '1998-08-11' ,
            '1999-09-12' ,
            '2003-10-13' ,
            '2002-11-14' ,
            '2000-12-15' ,
            '2001-01-16' ,
            '2001-02-17' ,
            '2002-03-18' ,
            '2003-04-19' ,
            '2001-05-20' ,
            '2002-06-21' ,
            '2003-07-22' ,
            '2001-08-23' ,
            '2002-09-24' ,
            '2003-10-25' ,
            '2004-11-26' ,
            '2001-12-27' ,
            '2002-01-28' ,
        ];

        for($i = 0 ; $i < count($user_id) ; $i++)
        {
            Profile::query()->create([
                'user_id' => $user_id[$i] ,
                'phone_number' => $phone_number[$i] ,
                'profile_picture' => $profile_picture[$i] ,
                'gender' => 'Male' ,
                'birth_date' => $birthday[$i]

            ]);
        }
    }
}
