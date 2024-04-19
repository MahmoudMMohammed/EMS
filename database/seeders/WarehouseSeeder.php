<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {                   //1           2        3          4          5           6         7
        $governorate = ['Damascus' , 'Homs' , 'Tartus' , 'Aleppo' , 'Suwayda' , 'Daraa' , 'Raqqa'];
        $address = ['Al-Qasaa Street' , 'Al-Hadara Street' , 'Henano Street' , 'Al-Hamdania Street' , 'Al-Shaarani Street' , 'Aqarba Street' , 'Al-Nour Street'];
        for($i = 0 ; $i < count($governorate) ; $i++)
        {
            Warehouse::query()->create([
                'governorate' => $governorate[$i],
                'address' => $address[$i]
            ]);
        }
    }
}
