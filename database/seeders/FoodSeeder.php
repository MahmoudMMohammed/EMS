<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = [
            'Khashkhash Kebab' , 'Eggplant Kebab' , 'Indian Kebab' , 'Kibbeh Labanieh' , 'Shakriya' , 'Shish Barak' ,                                                                                                                                                                                       //Oriental meals
            'Escalope' , 'Crispy' , 'Escalope Milanese' , 'Escalope Gritin' , 'Gordon Bleu' , 'Chicken Celesta' , 'Chicken Cordon' , 'Chicken Stroganoff' ,                                                                                                                                                 //Western meals
            'Roasted Marrow' , 'Souda sheep' , 'Sheep Eggs' , 'Chops' , 'Meat Kebab' , 'Halabi Kebab' , 'Orfali Kebab' , 'Maria' ,                                                                                                                                                                          //Grills
            'Shrimp Fattah' , 'Fried Calamari' , 'Grilled Calamari' , 'Provencal Calamari' , 'Provencal Shrimp' , 'Picant Shrimp' , 'Grilled Shrimp' , 'Pagrus Fish' , 'Fried Merlan Fish' , 'Fried Sultan Ibrahim' , 'Fried Grouper Fish' ,                                                                //Seafood
            'Cheese Barak' , 'Supreme Mini' , 'Chicken Toast Roll' , 'Kibbeh Hamis' , 'Spring Roll' , 'Grilled kibbeh' , 'French Fries' , 'Potato Wedges' , 'Chicken Liver' , 'Spicy Fries' , 'Chicken Sausage' , 'Mozzarella Steak' ,                                                                      //Hot appetizers
            'Hummus' , 'Muhammara' , 'Mutable' , 'Baba Ghanoush' , 'Beirut Hummus' , 'Turkish Hummus' ,                                                                                                                                                                                                     //cold appetizers
            'Four Seasons Pizza','Mushroom Pizza','Margarita Pizza','Pepperoni Pizza','Salami Pizza','Turkey Pizza','Sausage Pizza','Fettuccini','Creamy Pesto','Tortellini','Penne Arrabbiata','MAc & Cheese','Pink Pasta' ,                                                                               //Pastries
            'Lentil Soup' , 'Mushroom Soup' , 'Chicken Soup' , 'Vegetables Soup' , 'white Soup' , 'Vermicelli Soup' , 'Onion Soup' , 'Corn Soup' ,                                                                                                                                                          //Soups
            'Quinoa Tabbouleh' , 'Fattoush' , 'Armenian Salad' , 'Tabbouleh' , 'Oriental Arugula Salad' , 'Oriental salad' , 'Rocca Salad' , 'Celesta Salad' , 'Corn Salad (Lemon)' , 'Quinoa Salad' , 'Greek Salad' , 'Roquefort Salad' , 'Caesar Salad' , 'Avocado Salad' ,                               //Salads
            'Pancake' , 'Lotus Fettuccine' , 'Pizza Lounge' , 'Soiree' , 'Pistachio Pancake' , 'Pistachio Pancake' , 'Brownies Crepe' , 'Pistachio Crepe' , 'Chocolate Crepe' , 'Lotus Crepe' , 'Spicy Louts Crepe' , 'Dark Pancake' ,                                                                      //Desserts
            'Ghariba with Cream' , 'Namora' , 'Qatayef' , 'Warbat' , 'Shoaibiyat' , 'Shaebieat with nuts' , 'Pistachio Warbat' , 'Harissa' , 'Awamma' , 'Namora with cream' , 'Madlouka' , 'Halawa with cream' , 'Esamlia' ,                                                                                //Eastern sweets
            'Santana Cake' , 'Sprinkle Cake' , 'Dark Chocolate Cake' , 'Hawaii Cake' , 'Montana Cake' , 'Nutella Cake' , 'Forblanche Cake' , 'Lotus Cake' , 'Chocolate Cake' , 'Blueberry Cake' , 'Strawberry Cake' , 'Lemon Cake' , 'Raspberry Cake' , 'Orange Cake' , 'Galaxy Cake' , 'Louts Cake'        //Cake
        ];

        $price = [
            89000 , 91000 , 92000 , 84000 , 88000 , 75000 ,
            75000 , 75000 , 80000 , 82000 , 89000 , 85000 , 85000 , 89000 ,
            40000 , 71000 , 125000 , 175000 , 180000 , 180000 , 175000 , 120000 ,
            69000 , 76500 , 80000 , 84000 , 82500 , 82500 , 85000 , 140000 , 148500 , 110000 , 190000 ,
            52000 , 58000 , 60000 , 30000 , 60000 , 40000 , 26000 , 26000 , 38000 , 38000 , 40000 , 50000 ,
            22000 , 22000 , 24000 , 24000 , 25000 , 28000 ,
            42900 , 42900 , 45000 , 48500 , 50000 , 50000 , 48500 , 35000 , 36000 , 45000 , 24000 , 26000 , 27000 ,
            28000 , 29000 , 32000 , 29000 , 30000 , 30000 , 32000 , 32000 ,
            32000 , 36000 , 36000 , 30000 , 38000 , 34000 , 39000 , 39000 , 40000 , 42000 , 50000 , 52000 , 59000 , 60000 ,
            43500 , 91500 , 61500 , 143000 , 79500 , 70500 , 51000 , 66000 , 37500 , 43500 , 66000 , 37500 ,
            8000 , 12000 , 7000 , 12000 , 10000 , 9000 , 17000 , 40000 , 22000 , 40000 , 38000 , 70000 , 90000 ,
            249000 , 249600 , 260000 , 285000 , 265000 , 291200 , 290000 , 300000 , 332800 , 33000 , 30000 , 32000 , 35000 , 33000 , 36000 , 38000
            ];

        $category_id = [
            1 , 1 , 1 , 1 , 1 , 1 ,
            2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 ,
            3 , 3 , 3 , 3 , 3 , 3 , 3 , 3 ,
            4 , 4 , 4 , 4 , 4 , 4 , 4 , 4 , 4 , 4 , 4 ,
            5 , 5 , 5 , 5 , 5 , 5 , 5 , 5 , 5 , 5 , 5 , 5 ,
            6 , 6 , 6 , 6 , 6 , 6 ,
            7 , 7 , 7 , 7 , 7 , 7 , 7 , 7 , 7 , 7 , 7 , 7 , 7 ,
            8 , 8 , 8 , 8 , 8 , 8 , 8 , 8 ,
            9 , 9 , 9 , 9 , 9 , 9 , 9 , 9 , 9 , 9 , 9 , 9 , 9 , 9 ,
            10 , 10 , 10 , 10 , 10 , 10 , 10 , 10 , 10 , 10 , 10 , 10 ,
            11 , 11 , 11 , 11 , 11 , 11 , 11 , 11 , 11 , 11 , 11 , 11 , 11 ,
            12 , 12 , 12 , 12 , 12 , 12 , 12 , 12 , 12 , 12 , 12 , 12 , 12 , 12 , 12 , 12
            ];

        $picture  = [
            'Food/Oriental_meals/1.jpg' ,
            'Food/Oriental_meals/2.jpg' ,
            'Food/Oriental_meals/3.jpg' ,
            'Food/Oriental_meals/4.jpg' ,
            'Food/Oriental_meals/5.jpg' ,
            'Food/Oriental_meals/6.jpg' ,

            'Food/Western_meals/7.jpg' ,
            'Food/Western_meals/8.jpg' ,
            'Food/Western_meals/9.jpg' ,
            'Food/Western_meals/10.jpg' ,
            'Food/Western_meals/11.jpg' ,
            'Food/Western_meals/12.jpg' ,
            'Food/Western_meals/13.jpg' ,
            'Food/Western_meals/14.jpg' ,

            'Food/Grills/68.jpg' ,
            'Food/Grills/69.jpg' ,
            'Food/Grills/70.jpg' ,
            'Food/Grills/71.jpg' ,
            'Food/Grills/72.jpg' ,
            'Food/Grills/73.jpg' ,
            'Food/Grills/74.jpg' ,
            'Food/Grills/75.jpg' ,

            'Food/Seafood/76.jpg' ,
            'Food/Seafood/77.jpg' ,
            'Food/Seafood/78.jpg' ,
            'Food/Seafood/79.jpg' ,
            'Food/Seafood/80.jpg' ,
            'Food/Seafood/81.jpg' ,
            'Food/Seafood/82.jpg' ,
            'Food/Seafood/83.jpg' ,
            'Food/Seafood/84.jpg' ,
            'Food/Seafood/85.jpg' ,
            'Food/Seafood/86.jpg' ,

            'Food/Hot_appetizers/21.jpg' ,
            'Food/Hot_appetizers/22.jpg' ,
            'Food/Hot_appetizers/23.jpg' ,
            'Food/Hot_appetizers/24.jpg' ,
            'Food/Hot_appetizers/25.jpg' ,
            'Food/Hot_appetizers/26.jpg' ,
            'Food/Hot_appetizers/27.jpg' ,
            'Food/Hot_appetizers/28.jpg' ,
            'Food/Hot_appetizers/29.jpg' ,
            'Food/Hot_appetizers/30.jpg' ,
            'Food/Hot_appetizers/31.jpg' ,
            'Food/Hot_appetizers/32.jpg' ,

            'Food/Cold_appetizers/15.jpg' ,
            'Food/Cold_appetizers/16.jpg' ,
            'Food/Cold_appetizers/17.jpg' ,
            'Food/Cold_appetizers/18.jpg' ,
            'Food/Cold_appetizers/19.jpg' ,
            'Food/Cold_appetizers/20.jpg' ,

            'Food/Pastries/55.jpg' ,
            'Food/Pastries/56.jpg' ,
            'Food/Pastries/57.jpg' ,
            'Food/Pastries/58.jpg' ,
            'Food/Pastries/59.jpg' ,
            'Food/Pastries/60.jpg' ,
            'Food/Pastries/61.jpg' ,
            'Food/Pastries/62.jpg' ,
            'Food/Pastries/63.jpg' ,
            'Food/Pastries/64.jpg' ,
            'Food/Pastries/65.jpg' ,
            'Food/Pastries/66.jpg' ,
            'Food/Pastries/67.jpg' ,

            'Food/Soups/33.jpg' ,
            'Food/Soups/34.jpg' ,
            'Food/Soups/35.jpg' ,
            'Food/Soups/36.jpg' ,
            'Food/Soups/37.jpg' ,
            'Food/Soups/38.jpg' ,
            'Food/Soups/39.jpg' ,
            'Food/Soups/40.jpg' ,

            'Food/Salads/41.jpg' ,
            'Food/Salads/42.jpg' ,
            'Food/Salads/43.jpg' ,
            'Food/Salads/44.jpg' ,
            'Food/Salads/45.jpg' ,
            'Food/Salads/46.jpg' ,
            'Food/Salads/47.jpg' ,
            'Food/Salads/48.jpg' ,
            'Food/Salads/49.jpg' ,
            'Food/Salads/50.jpg' ,
            'Food/Salads/51.jpg' ,
            'Food/Salads/52.jpg' ,
            'Food/Salads/53.jpg' ,
            'Food/Salads/54.jpg' ,

            'Food/Desserts/87.jpg' ,
            'Food/Desserts/88.jpg' ,
            'Food/Desserts/89.jpg' ,
            'Food/Desserts/90.jpg' ,
            'Food/Desserts/91.jpg' ,
            'Food/Desserts/92.jpg' ,
            'Food/Desserts/93.jpg' ,
            'Food/Desserts/94.jpg' ,
            'Food/Desserts/95.jpg' ,
            'Food/Desserts/96.jpg' ,
            'Food/Desserts/97.jpg' ,
            'Food/Desserts/98.jpg' ,

            'Food/Eastern_sweets/99.jpg' ,
            'Food/Eastern_sweets/100.jpg' ,
            'Food/Eastern_sweets/101.jpg' ,
            'Food/Eastern_sweets/102.jpg' ,
            'Food/Eastern_sweets/103.jpg' ,
            'Food/Eastern_sweets/104.jpg' ,
            'Food/Eastern_sweets/105.jpg' ,
            'Food/Eastern_sweets/106.jpg' ,
            'Food/Eastern_sweets/107.jpg' ,
            'Food/Eastern_sweets/108.jpg' ,
            'Food/Eastern_sweets/109.jpg' ,
            'Food/Eastern_sweets/110.jpg' ,
            'Food/Eastern_sweets/111.jpg' ,

            'Food/Cake/112.jpg' ,
            'Food/Cake/113.jpg' ,
            'Food/Cake/114.jpg' ,
            'Food/Cake/115.jpg' ,
            'Food/Cake/116.jpg' ,
            'Food/Cake/117.jpg' ,
            'Food/Cake/118.jpg' ,
            'Food/Cake/119.jpg' ,
            'Food/Cake/120.jpg' ,
            'Food/Cake/121.jpg' ,
            'Food/Cake/122.jpg' ,
            'Food/Cake/123.jpg' ,
            'Food/Cake/124.jpg' ,
            'Food/Cake/125.jpg' ,
            'Food/Cake/126.jpg' ,
            'Food/Cake/127.jpg' ,
            ];

        $description = [
            '200g of Meat Kebab , Tomato Sauce with especially spicy' ,
            '200g of Meat Kebab , Onion slices with lettuce , Eggplant' ,
            '200g of Meat Kebab , Green peppers and red peppers , Vermicelli rice with Onion slices' ,
            '6 pieces of Kibbeh , Yogurt , Vermicelli rice with Onion slices' ,
            '150g of Meat Lamb , Yogurt , Vermicelli rice with Onion slices' ,
            '150g of Shish , Yogurt , Onion slices with lettuce , Vermicelli rice' ,

            '2 big pieces of Escalope , Mozzarella sauce and potatoes , Sauteed vegetables' ,
            '4 pieces of chicken fatayel , Barbecue sauce and potatoes , Coleslaw ' ,
            '2 pieces of chicken cutlets , Mozzarella sauce and potatoes , Sauteed vegetables' ,
            '2 pieces of chicken cutlets , Bechamel sauce and potatoes , Sauteed vegetables' ,
            '2 pieces of chicken cutlets , Mozzarella sauce and potato wedges , Sauteed vegetables' ,
            '2 pieces of chicken cutlets , Mozzarella sauce with rice and green pepper , Sauteed vegetables' ,
            '2 pieces of chicken cutlets , Mozzarella sauce and potato wedges , Sauteed vegetables , Mushrooms' ,
            'Green pepper , Red pepper , Onion , Soy sauce , Mushrooms' ,

            '1 piece of Marrow meat grilled , Tomatoes and onions , Fresh salad' ,
            '300g of Souda sheep , Fresh salad , Grilled Tomatoes and onions' ,
            '500g of Eggs sheep grilled , Tomatoes and onions , Fresh salad' ,
            '500g of grilled Chops , Fresh salad , Bread , Tomatoes and onions' ,
            '500g of Kebab meat , Grilled tomatoes and onions , Bread , Fresh salad' ,
            '500g of Kebab meat , Tomato Sauce with especially spicy , onions' ,
            '500g of Orfali kebab , Fresh salad , Grilled tomatoes and onions' ,
            '500g of Maria meat , Especially spicy , Onions , Bread' ,

            '150g of Shrimp meat , fried eggplant , 50g of nuts' ,
            '300g of Fried Calamari rings with especially red spicy' ,
            '200g of Calamari with grilled vegetables , Onion slices with special spices' ,
            '200g of Provencal Calamari , Lemon slices and sauce with Asian spices' ,
            '200g of Provencal Shrimp , Lemon slices , Garlic sauce' ,
            '200g of Prawns with blademere sauce , Lemon , Spicy sauce' ,
            '200g of Grilled Prawns , Capsicum , Onion , Cocktail sauce' ,
            '500g of Grilled Pagrus Fish , Served with brown rice , Lemon and garlic , Tarator' ,
            '450g of Fried Merlan Fish with herbs , Lemon and garlic , Tarator' ,
            '500g of Fried Sultan Ibrahim , Served with Mujadara , Lemon and garlic , Tarator' ,
            '500g of Fried Grouper Fish , Tarator , Served with Mujadara , Lemon and garlic' ,

            '4 pieces of Cheese Barak , Local cheese , Mozzarella' ,
            '4 pieces of Chicken Flakes , mushroom , Tomato Sauce with especially spicy' ,
            '4 pieces of Chicken Toast Roll , Sumac , Onions , Spicy sauce' ,
            '2 pieces of Kibbeh Hamis stuffed Lamp meat with walnuts and special spices' ,
            '4 pieces of Spring Roll , Carrots , Withe cabbage , Green peppers , Leeks' ,
            '2 pieces of Grilled kibbeh stuffed Lamp meat with fresh salad' ,
            'Crispy potato plate with ketchup , creamy onion' ,
            'Roasted Potato , Rosemary , Oregano , Olive oil' ,
            'Potato , Green pepper , Olive oil , Red pepper , Lemon ' ,
            'Potato , Pepper , Molasses , Garlic , Lemon slices and sauce' ,
            'Potato , Green pepper , Red pepper , Garlic , Olive Oil' ,
            '4 pieces of Mozzarella Pane , FLour , Eggs , Corn FLakes' ,

            'Ground Chickpeas with tahini , Olive oil' ,
            'Pepper Molasses , Onion , Black seed , Tahini , Muhammara' ,
            'Yogurt , Tahini , Lemon , Garlic , Olive oil' ,
            'Green pepper , Red pepper , Tomato , Parsley , Pomegranate molasses' ,
            'Chickpeas , Parsley , Garlic , Lemon , Olive oil' ,
            'Capsicum Molasses , Green pepper , Red pepper , Hummus , Olive oil' ,

            'Thin dough 22 cm , Pizza sauce , Mozzarella , Cheese , Fresh mushrooms , Chopped olives , Tomato slices , green peppers' ,
            'Thin dough 22 cm , Pizza sauce , Mozzarella , Cheese , Fresh mushrooms' ,
            'Thin dough 22 cm , Pizza sauce , Mozzarella , Cheese' ,
            'Thin dough 22 cm , Pizza sauce , Mozzarella , Cheese , 8 slices pf pepperoni' ,
            'Thin dough 22 cm , Pizza sauce , Mozzarella , Cheese , 6 slices pf salami' ,
            'Thin dough 22 cm , Pizza sauce , Mozzarella , Cheese , 6 turkey slices' ,
            'Thin dough 22 cm , Pizza sauce , Mozzarella , Cheese , sausage' ,
            'White sauce with fresh mushroom , Cheese' ,
            'Pesto sauce with white sauce and cheese , Special Spices' ,
            'Macaroni stuffed with cheese served with sauce and cheese' ,
            'Macaroni stuffed with red sauce and olives' ,
            'Macaroni stuffed with cheddar sauce and cheese' ,
            'Mix of red and white sauce with cheese , Mozzarella' ,

            'Lentils , Carrots , Potatoes , Onions' ,
            'Butter , Flour , Creme fraiche , Milk , Mushrooms' ,
            'Potatoes , Chicken , Chicken stock base ' ,
            'potatoes , Carrots , Onions , Green peppers , Chicken stock base' ,
            'Butter , Flour , Creme Fraiche , Milk , Chicken',
            'Chicken Vermicelli , Chicken stock base' ,
            'Butter , Flour , Onions base , Carrots',
            'Flour , Butter , Milk , Corn , Milk , Mushrooms',

            'Parsley , White Cabbage , Cucumber , Tomato , Lemon , Olive oil , Feta cheese' ,
            'Tomatoes , Cucumber , Lettuce , Green thyme , Tarragon , Onions , Bread , Green Mint' ,
            'Cucumber , Tomato , Arabic Lettuce , Hot pepper , Lemon , Olive oil' ,
            'Parsley , Tomatoes , Olive oil , Bulgur , Lemon , Sumac , Mint , Onions' ,
            'Arugula , Onions , Tomatoes , Sumac , Lemon , Olive oil' ,
            'Tomatoes , Cucumber , Lettuce , Lemon , Mint , Onions , Olive oil' ,
            'Arugula , Fresh mushrooms , Cherry , Tomatoes , Parmesan , Olive oil' ,
            'Tortilla Bread , French Lettuce , Arugula , Lorosso lettuce , Cherry tomatoes , Honey mustard sauce',
            'Arabic Lettuce , Red pepper , Green pepper , Lemon' ,
            'French lettuce , Lorosso lettuce , Arugula , Quinoa sauce , Honey , Vinegar , Fresh mushrooms , Tomatoes' ,
            'Cucumber , Tomato , Lettuce , Green pepper , Feta cheese , Vinaigrette sauce' ,
            'French Lettuce , Dersinck sauce , Roquefort , Cherry tomatoes , Walnuts',
            'Arabic lettuce , Toast , Chicken , Caesar sauce',
            'Green capsicum , Red capsicum , Lorosso , Lettuce , Fringe Lettuce , Carrots , Avocado , Fresh cream',

            '12 small pieces of pancake (Served with a cup of chocolate and ice cream)',
            'Fettuccine slices with louts sauce , Banana , Lemon ice cream',
            'Marshmallow pieces with chocolate balls , Cocoa sauce' ,
            '50 pieces of mixed crepe cakes with cream , Mini pancake , Clair , Crepe grates stuffed with cream , Brownies' ,
            '4 layers of flour dough with green mint sauce' ,
            '12 pieces of crepe soiree with green mint sauce' ,
            'Crepe stuffed with brownies covered with 3 type of chocolate' ,
            'Crepe dipped in pistachio sauce decorated with white chocolate' ,
            'Crepe covered with 3 types of chocolate' ,
            'Crepe covered with louts sauce decorated with white chocolate' ,
            '2 Rolls of crepe stuffed with banana , Patisserie cream and cinnamon , Dipped in louts' ,
            'Crepe covered with 3 types of chocolate and white chocolate' ,

            '1 piece of ghariba cream stuffed with pistachios' ,
            '1 piece of Namora with cream and pistachios' ,
            '1 piece of Fried Qatayef with cream and pistachios' ,
            '1 piece of cream Warbat , Pistachios' ,
            '1 piece of Shoaibiyat with cream , Pistachios' ,
            '1 piece of Shaebieat with walnuts , Pistachios' ,
            '1 piece of Pistachio Warbat , Pistachios ',
            '500g of Harissa with pistachios' ,
            '500g of Awamma with local syrup' ,
            '500g of Namora with cream' ,
            '500g of Madlouka with cream , Pistachios ' ,
            '1 Kg of Halawa with cream , Pistachios' ,
            '1 Kg of Konafa , Arabic cream , Vegetable ghee' ,

            'For 10 people , Layers of cake with galaxy chocolate and galaxy cream' ,
            'For 10 people , Layers of cake covered with 3 types of chocolate' ,
            'For 10 people , Layers of cake with chocolate and chocolate cream' ,
            'For 10 people , Layers of cake with Pineapple and Coconut',
            'For 10 people , Layers of cake with crunch and white chocolate',
            'For 10 people , Layers of cake with nutella chocolate and white cream' ,
            'For 10 people , Layers of cake with crunch and fruits' ,
            'For 10 people , Layers of cake with louts and louts cream' ,
            'For 10 people , Layers of cake with chocolate and white cream' ,
            '1 piece , Biscuit and cream cheese on the top blueberry sauce' ,
            '1 piece , Biscuit and cream cheese on the top strawberry sauce' ,
            '1 piece , Biscuit and cream cheese on the top Lemon sauce' ,
            '1 piece , Biscuit and cream cheese on the top raspberry sauce' ,
            '1 piece , Biscuit and cream cheese on the top orange sauce' ,
            '1 piece , Biscuit and cream cheese on the top chocolate galaxy' ,
            '1 piece , Biscuit and cream cheese on the top louts sauce' ,
        ];

        $country_of_origin = [ 'Algeria', 'Bahrain', 'Comoros', 'Djibouti', 'Egypt', 'Iraq', 'Jordan', 'Kuwait', 'Lebanon', 'Libya', 'Mauritania', 'Morocco', 'Oman', 'Palestine', 'Qatar', 'Saudi Arabia', 'Somalia', 'Sudan', 'Syria', 'Tunisia', 'United Arab Emirates', 'Yemen'];

        for ($i = 0 ; $i < count($name) ; $i++)
        {
            Food::query()->create([
                'name' => $name[$i] ,
                'price' => $price[$i] ,
                'food_category_id' => $category_id[$i],
                'picture' => $picture[$i] ,
                'description' => $description[$i] ,
                'country_of_origin' => $country_of_origin[array_rand($country_of_origin)] ,
            ]);
        }
    }
}
