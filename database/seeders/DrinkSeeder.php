<?php

namespace Database\Seeders;

use App\Models\Drink;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DrinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = [
            'Space Drink' , 'Ginger Drink' , 'Mojitio Drink' , 'Ice Tea Peach' , 'Ice Tea Lemon' , 'Ice Tea Berry' , 'Ice Tea Blue' ,                                                                                                  //Cold drinks
            'Cumin and Lemon' , 'Tea' , 'Espresso' , 'Mocha' , 'Cappuccino' , 'Tea Karak' , 'Sahlab' , 'Hot Chocolate' , 'Hot Chocolate (Mint)' ,                                                                                      //Hot drinks
            'Vanilla Milkshake' , 'Chocolate Milkshake' , 'Strawberry Milkshake' , 'Blue Milkshake' , 'Cookies Milkshake' , 'Mint Milkshake' , 'Berry Milkshake' , 'Brownis Milkshake' , 'Mango Milkshake' , 'Caramel Milkshake' ,     //Milkshake
            'Banana and Milk Cocktail' , 'Vanilla Cocktail' , 'Nutella Cocktail' , 'Chocolate Cocktail' , 'Strawberry Cocktail' , 'Mango Cocktail' , 'Berry Cocktail' , 'Brownis Cocktail' ,                                           //Cocktails
            'Strawberry Juices' , 'Polo Juices' , 'Blueberry Juices' , 'Mango Juices' , 'Carrots Juices' , 'Orange Juices' ,                                                                                                           //Juices
            'Cafe Latte Mocha' , 'Turkish Coffee' , 'Bitter Coffee' , 'American Coffee' , 'Ice Coffee' , 'Ice Latte Coffee' , 'Ice Coffee Caramel' , 'Ice Coffee Vanilla' ,
        ];

        $price = [
            37000 , 40000 , 35000 , 32000 , 32000 , 34000 , 33000 ,
            12000 , 14000 , 22000 , 25000 , 26000 , 18000 , 15000 , 22000 , 24000 ,
            36000 , 36000 , 36000 , 38000 , 38000 , 33000 , 35000 , 40000 , 39000 , 41000 ,
            35000 , 36000 , 39000 , 36000 , 36000 , 37000 , 38000 , 40000 ,
            20000 , 20000 , 22000 , 32000 , 20000 , 20000 ,
            19000 , 21000 , 12000 , 24000 , 32000 , 34000 , 38000 , 38000 ,
        ];

        $category_id = [
            1 , 1 , 1 , 1 , 1 , 1 , 1 ,
            2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 ,
            3 , 3 , 3 , 3 , 3 , 3 , 3 , 3 , 3 , 3 ,
            4 , 4 , 4 , 4 , 4 , 4 , 4 , 4 ,
            5 , 5 , 5 , 5 , 5 , 5 ,
            6 , 6 , 6 , 6 , 6 , 6 , 6 , 6 ,
        ];

        $picture = [
            'Drinks/Cold_drinks/128.jpg' ,
            'Drinks/Cold_drinks/129.jpg' ,
            'Drinks/Cold_drinks/130.jpg' ,
            'Drinks/Cold_drinks/131.jpg' ,
            'Drinks/Cold_drinks/132.jpg' ,
            'Drinks/Cold_drinks/133.jpg' ,
            'Drinks/Cold_drinks/134.jpg' ,

            'Drinks/Hot_drinks/135.jpg' ,
            'Drinks/Hot_drinks/136.jpg' ,
            'Drinks/Hot_drinks/137.jpg' ,
            'Drinks/Hot_drinks/138.jpg' ,
            'Drinks/Hot_drinks/139.jpg' ,
            'Drinks/Hot_drinks/140.jpg' ,
            'Drinks/Hot_drinks/141.jpg' ,
            'Drinks/Hot_drinks/142.jpg' ,
            'Drinks/Hot_drinks/143.jpg' ,

            'Drinks/Milkshake/152.jpg' ,
            'Drinks/Milkshake/153.jpg' ,
            'Drinks/Milkshake/154.jpg' ,
            'Drinks/Milkshake/155.jpg' ,
            'Drinks/Milkshake/156.jpg' ,
            'Drinks/Milkshake/157.jpg' ,
            'Drinks/Milkshake/158.jpg' ,
            'Drinks/Milkshake/159.jpg' ,
            'Drinks/Milkshake/160.jpg' ,
            'Drinks/Milkshake/161.jpg' ,

            'Drinks/Cocktails/168.jpg' ,
            'Drinks/Cocktails/169.jpg' ,
            'Drinks/Cocktails/170.jpg' ,
            'Drinks/Cocktails/171.jpg' ,
            'Drinks/Cocktails/172.jpg' ,
            'Drinks/Cocktails/173.jpg' ,
            'Drinks/Cocktails/174.jpg' ,
            'Drinks/Cocktails/175.jpg' ,

            'Drinks/Natural_juices/162.jpg' ,
            'Drinks/Natural_juices/163.jpg' ,
            'Drinks/Natural_juices/164.jpg' ,
            'Drinks/Natural_juices/165.jpg' ,
            'Drinks/Natural_juices/166.jpg' ,
            'Drinks/Natural_juices/167.jpg' ,

            'Drinks/Coffee/144.jpg' ,
            'Drinks/Coffee/145.jpg' ,
            'Drinks/Coffee/146.jpg' ,
            'Drinks/Coffee/147.jpg' ,
            'Drinks/Coffee/148.jpg' ,
            'Drinks/Coffee/149.jpg' ,
            'Drinks/Coffee/150.jpg' ,
            'Drinks/Coffee/151.jpg' ,
        ];

        $description = [
            'Soda , Orange , Lemon , Syrup mixture',
            'Soda , ginger , Lemon' ,
            'Soda , Mint , Lemon , Mojito syrup' ,
            'Peach flavored iced tea with ice cubes' ,
            'Lemon flavored iced tea with ice cubes' ,
            'Berry flavored iced tea with ice cubes' ,
            'Blue flavored iced tea with ice cubes' ,

            'Gingre , Lemon slices with cumin' ,
            'Small cup of red tea with white starch foam' ,
            'Hot milk with white foam and caramel' ,
            'Hot milk with brown foam and caramel' ,
            'Hot milk with brown foam painted' ,
            'Small cup of red tea with hot milk' ,
            'small cup of hot milk with cinnamon' ,
            'small cup of hot milk and dark chocolate with foam' ,
            'small cup of hot milk and dark or white chocolate with foam , Green mint' ,

            'Vanilla , Cold milk , Ice cubes , Flavourings' ,
            'Chocolate , Cold milk , Ice cubes , Flavourings' ,
            'Strawberry , Cold milk , Ice cubes , Flavourings' ,
            'Coconut , Ice cream , Pineapple , Flavourings' ,
            'Pieces of cookies , Cold milk , Ice cubes , Flavourings' ,
            'Mint , Cold milk , Ice cubes , Flavourings' ,
            'Berry , Cold milk , Ice cubes , Flavourings' ,
            'Brownis , Cold milk , Ice cubes , Flavourings' ,
            'Mango , Cold milk , Ice cubes , Flavourings' ,
            'Caramel , Cold milk , Ice cubes , Flavourings' ,

            'Cold milk , Banana pieces , Honey , Ice cubes' ,
            'Cold milk , Vanilla , Honey , Ice cubes' ,
            'Cold milk , Nutella , Honey , Ice cubes' ,
            'Cold milk , Chocolate , Honey , Ice cubes' ,
            'Cold milk , Strawberry , Honey , Ice cubes' ,
            'Cold milk , Mango , Honey , Ice cubes' ,
            'Cold milk , Berry , Honey , Ice cubes' ,
            'Cold milk , Brownis , Honey , Ice cubes' ,

            '500ml of natural strawberry juice with ice cube' ,
            '500ml of natural polo juice with ice cube , Lemon , Mint' ,
            '500ml of natural blueberry juice with ice cube' ,
            '500ml of natural Mango juice with ice cube' ,
            '500ml of natural carrots juice with ice cube' ,
            '500ml of natural orange juice with ice cube' ,

            '250ml of cafe Latte Mocha with foam' ,
            '250ml of Turkish coffee with foam' ,
            '100ml of Bitter coffee with foam' ,
            '250ml of American coffee with foam' ,
            '350ml of Ice coffee with foam' ,
            '350ml of Ice latte coffee with foam , Milk' ,
            '350ml of Ice coffee with foam , Milk , Caramel' ,
            '350ml of Ice coffee with foam , Milk , Vanilla' ,
        ];


        for($i = 0 ; $i < count($name) ; $i++)
        {
            Drink::query()->create([
                'name' => $name[$i] ,
                'price' => $price[$i] ,
                'drink_category_id' => $category_id[$i] ,
                'picture' => $picture[$i] ,
                'description' => $description[$i]
            ]);
        }
    }
}
