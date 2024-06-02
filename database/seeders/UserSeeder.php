<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Profile;
use App\Models\User;
use Database\Factories\CartFactory;
use Database\Factories\CartItemFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = ['Mohamad Mansour' , 'Mahmoud Mohammad' , 'Obeda Al Rahal' , 'Obaid Al Rifaie' , 'Abd Al Hadi Baghdadi' , 'Hussein Harajli' , 'Hussam Hariri' , 'Taj Aldeen ALkittanee' , 'Akram Al Aek' , 'Mohammad Ismail' , 'Laith Hamada' , 'Ibrahem Al Omari' , 'Mohammad Abd Al Aziz' , 'Mohammad Al Younes' , 'Mohammad Abd Al Majed' , 'Khaled Al Daba' , 'Baraa Al Ghafre' , 'Salem Al Kallas' , 'Majed Mohammad' , 'Zaher Abdallah' , 'Modar Barhoum' , 'Talaa Talaa' , 'Mohammad Safi' , 'Omar Safi' , 'Khaled Al Rifaie' , 'Obada Al Masri' , 'Abd Al Rahman Al Hamod' , 'Wail Houbi'];

        $email = ['360mohamad360@gmail.com' , 'mahmoudmohammad932@gmail.com' , 'rhalobeda@gmail.com' , 'obaidallahalrifaie@gmail.com' , 'obadawork912@gmail.com' , '10hussien@gmail.com' , 'hussam10h@gmail.com' , 'tajldeen20027@gmail.com' , 'akramalek0@gmail.com' , 'mohamaadasmaeel12@gmail.com' , 'lio6laith@gmail.com' , 'omariibrahem64@gmail.com' , 'aa9612358@gmail.com' , 'Mohamadnalyounes4@gmail.com' , 'mam29740@gmail.com' , 'Aldabakhaled7@gmail.com' , 'baraaghafre11@gmail.com' , 'mhdj09069@gmail.com' , 'mmh922939@gmail.com' , 'zaherabdullah38@gmail.com' , 'modarbarhoum109@gmail.com' , 'tl2354086@gmail.com' , 'faresion12345@gmail.com' , 'omartpa12@gmail.com' , 'khaledrf581@gmail.com' , 'Obadaa.masri@gmail.com' , 'abd.alrrahman.alhamod@gmail.com' , 'hpewail@gmail.com'] ;

        $password = ['ite2003' , 'ite2003' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023' , 'ite2023'];

        $role = ['Owner' , 'Owner' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' , 'Admin' ];

        for($i = 0 ; $i < count($name) ; $i++)
        {
            User::query()->create([
                'name' => $name[$i] ,
                'email' => $email[$i],
                'password' => $password[$i] ,
                'verified' => true ,
                'role' => $role[$i]
            ]);
        }

        User::factory()->count(10)
            ->has(Cart::factory(), 'cart')
            ->has(Profile::factory(), 'profile')
            ->create();
    }
}
