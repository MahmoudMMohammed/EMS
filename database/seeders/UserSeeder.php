<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker ;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = ['a' , 'b' , 'c' , 'd' , 'e' , 'f' , 'g' , 'h' , 'i' , 'g' , 'k' , 'l' , 'm' , 'n' , 'o' , 'p' , 'q' , 'r' , 's' , 't' , 'u' , 'v' , 'e' , 'x' , 'w' , 'z'];

        $email = Faker::create() ;

        $password = [];

        $verified = [];

        $role = [];

        for($i = 0 ; $i < count($name) ; $i++)
        {
            User::query()->create([
                'name' => $name[$i] ,
                'email' => $email->unique()->safeEmail ,
                'password' => 'ite2003H' ,
                'verified' => true ,
                'role' => 'Admin'
            ]);
        }
    }
}
