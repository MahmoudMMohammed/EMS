<?php

namespace Database\Seeders;

use App\Models\Accessory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = [
            'Tulip Flower Vase' , 'Basil Flower Vase' , 'Roses Vase' , 'Lavender Flower Vase' , 'Table Runner(model:1)' , 'Table Runner(model:2)' , 'Table Runner(model:3)' , 'Table Runner(model:4)' , 'Navy Blue Table Cloths' , 'Purple Table Cloths' , 'White Table Cloths' , 'Blue Table Cloths' , 'Pink Table Cloths' , 'Red Table Cloths' , 'yellow Table Cloths' , 'Balloon Decorations(model:1)' , 'Balloon Decorations(model:2)' , 'Balloon Decorations(model:3)' , 'Balloon Decorations(model:4)' , 'Balloon Decorations(model:5)' , 'Table Candles(model:1)' , 'Table Candles(model:2)' , 'Table Candles(model:3)' , 'Table Candles(model:4)' , 'Pink Chair Sashes' , 'Beige Chair Sashes' , 'Dark Pink Chair Sashes' , 'Brown Chair Sashes' , 'Green Chair Sashes' ,
            'Gray Party Chair' , 'Brown Party Chair' , 'Green Party Chair' , 'Purple Party Chair' , 'Blue Party Chair' , 'Black Picnic Chair' , 'White Picnic Chair' , 'Beech Wood Chair' , 'MDF Wood Chair' , 'MDF Wood Table' , 'Black Plastic Table' , 'Black Iron Table' , 'Black Glass Table' , 'Event Tent(model:1)' , 'Event Tent(model:2)' , 'Event Tent(model:3)' , 'Event Tent(model:4)' ,
            'Quran Reader' , 'Muslim Preacher' , 'Imam of Muslims' , 'Mosque Preacher' , 'Father of Christians' , 'Bishop of Christians' , 'Patriarch of Christians' ,
            'Arada Band' , 'Mawlawi Band' , 'Chanting Band' , 'Singing Band' , 'Church Scout' , 'DJ' ,
            'Display Screen' , 'Air Desert' , 'Spark Emitting Device' ,
            'Strobe Lights' , 'Stage Lights' , 'Disco Ball' , 'Moon Lights' , 'Fog Machines' ,
            'Microphone Stand' , 'Portable Speaker System' , 'Wireless Microphone' , 'Mixing Board' ,
        ];

        $price = [
            14000 , 18000 , 16000 , 16000 , 5000 , 5000 , 5000 , 5000 , 8000 , 8000 , 8000 , 8000 , 8000 , 8000 , 8000 , 55000 , 38000 , 50000 , 45000 , 25000 , 10000 , 11000 , 12000 , 12000 , 5000 , 5000 , 8000 , 8000 , 8000 ,
            8000 , 8000 , 8000 , 8000 , 8000 , 4000 , 4000 , 6000 , 6000 , 14000 , 10000 , 12000 , 14000 , 128000 , 145000 , 160000 , 112000 ,
            20000 , 0 , 0 , 0 , 0 , 0 , 0 ,
            120000 , 160000 , 180000 , 180000 , 300000 , 210000 ,
            145000 , 62000 , 27000 ,
            38000 , 22000 , 32000 , 18000 , 46000 ,
            8000 , 24000 , 12000 , 43000 ,
            ];

        $picture = [
            'Accessories/Decoration/1.jpg' ,
            'Accessories/Decoration/2.jpg' ,
            'Accessories/Decoration/3.jpg' ,
            'Accessories/Decoration/4.jpg' ,
            'Accessories/Decoration/5.jpg' ,
            'Accessories/Decoration/6.jpg' ,
            'Accessories/Decoration/7.jpg' ,
            'Accessories/Decoration/8.jpg' ,
            'Accessories/Decoration/9.jpg' ,
            'Accessories/Decoration/10.jpg' ,
            'Accessories/Decoration/11.jpg' ,
            'Accessories/Decoration/12.jpg' ,
            'Accessories/Decoration/13.jpg' ,
            'Accessories/Decoration/14.jpg' ,
            'Accessories/Decoration/15.jpg' ,
            'Accessories/Decoration/16.jpg' ,
            'Accessories/Decoration/17.jpg' ,
            'Accessories/Decoration/18.jpg' ,
            'Accessories/Decoration/19.jpg' ,
            'Accessories/Decoration/20.jpg' ,
            'Accessories/Decoration/21.jpg' ,
            'Accessories/Decoration/22.jpg' ,
            'Accessories/Decoration/23.jpg' ,
            'Accessories/Decoration/24.jpg' ,
            'Accessories/Decoration/25.jpg' ,
            'Accessories/Decoration/26.jpg' ,
            'Accessories/Decoration/27.jpg' ,
            'Accessories/Decoration/28.jpg' ,
            'Accessories/Decoration/29.jpg' ,

            'Accessories/Basics/30.jpg' ,
            'Accessories/Basics/31.jpg' ,
            'Accessories/Basics/32.jpg' ,
            'Accessories/Basics/33.jpg' ,
            'Accessories/Basics/34.jpg' ,
            'Accessories/Basics/35.jpg' ,
            'Accessories/Basics/36.jpg' ,
            'Accessories/Basics/37.jpg' ,
            'Accessories/Basics/38.jpg' ,
            'Accessories/Basics/39.jpg' ,
            'Accessories/Basics/40.jpg' ,
            'Accessories/Basics/41.jpg' ,
            'Accessories/Basics/42.jpg' ,
            'Accessories/Basics/43.jpg' ,
            'Accessories/Basics/44.jpg' ,
            'Accessories/Basics/45.jpg' ,
            'Accessories/Basics/46.jpg' ,

            'Accessories/Religious/47.jpg' ,
            'Accessories/Religious/48.jpg' ,
            'Accessories/Religious/49.jpg' ,
            'Accessories/Religious/50.jpg' ,
            'Accessories/Religious/51.jpg' ,
            'Accessories/Religious/52.jpg' ,
            'Accessories/Religious/53.jpg' ,

            'Accessories/Visual_presentations/54.jpg' ,
            'Accessories/Visual_presentations/55.jpg' ,
            'Accessories/Visual_presentations/56.jpg' ,
            'Accessories/Visual_presentations/57.jpg' ,
            'Accessories/Visual_presentations/58.jpg' ,
            'Accessories/Visual_presentations/59.jpg' ,

            'Accessories/Electrical_equipment/60.jpg' ,
            'Accessories/Electrical_equipment/61.jpg' ,
            'Accessories/Electrical_equipment/62.jpg' ,

            'Accessories/Lighting_equipment/63.jpg' ,
            'Accessories/Lighting_equipment/64.jpg' ,
            'Accessories/Lighting_equipment/65.jpg' ,
            'Accessories/Lighting_equipment/66.jpg' ,
            'Accessories/Lighting_equipment/67.jpg' ,

            'Accessories/Audio_equipment/68.jpg' ,
            'Accessories/Audio_equipment/69.jpg' ,
            'Accessories/Audio_equipment/70.jpg' ,
            'Accessories/Audio_equipment/71.jpg' ,

        ];

        $description = [
            '1 vase of artificial white tulips flowers placed on the table with green veins , NOTE : it is only for rent' ,
            '1 vase of artificial pink and white basil flowers placed on the table with green veins , NOTE : it is only for rent' ,
            '1 vase of artificial red roses placed on the table with green veins , NOTE : it is only for rent' ,
            '1 vase of artificial yellow lavender flower placed on the table with green veins , NOTE : it is only for rent' ,
            '1 Decorative white table runner 30cm * 275cm , NOTE : it is only for rent' ,
            '1 Decorative white table runner 30cm * 275cm , NOTE : it is only for rent' ,
            '1 Decorative white table runner 30cm * 275cm , NOTE : it is only for rent' ,
            '1 Decorative white table runner 30cm * 275cm , NOTE : it is only for rent' ,
            '1 Tablecloths in navy blue color and 2m * 2m size , NOTE : it is only for rent' ,
            '1 Tablecloths in purple color and 2m * 2m size , NOTE : it is only for rent' ,
            '1 Tablecloths in white color and 2m * 2m size , NOTE : it is only for rent' ,
            '1 Tablecloths in blue color and 2m * 2m size , NOTE : it is only for rent' ,
            '1 Tablecloths in pink color and 2m * 2m size , NOTE : it is only for rent' ,
            '1 Tablecloths in red color and 2m * 2m size , NOTE : it is only for rent' ,
            '1 Tablecloths in yellow color and 2m * 2m size , NOTE : it is only for rent' ,
            '50 balloons in different colors : pink , white , yellow and different size' ,
            '30 balloons in different colors : purple , white , dark purple and different size' ,
            '45 balloons in different colors : blue , white , dark blue and different size' ,
            '40 balloons in different colors : blue , white , dark blue and different size' ,
            '25 balloons in different colors : blue , white , dark blue and different size' ,
            '1 Decorative table candles contains 1 candle , NOTE : it is only for rent' ,
            '1 Decorative table candles contains 1 candle , NOTE : it is only for rent' ,
            '1 Decorative table candles contains 1 candle , NOTE : it is only for rent' ,
            '1 Decorative table candles contains 1 candle , NOTE : it is only for rent' ,
            '1 Decorative pink chair sashes made of silk , NOTE : it is only for rent' ,
            '1 Decorative beige chair sashes made of silk , NOTE : it is only for rent' ,
            '1 Decorative dark pink chair sashes made of silk with flowers , NOTE : it is only for rent' ,
            '1 Decorative brown chair sashes made of silk with flowers , NOTE : it is only for rent' ,
            '1 Decorative green chair sashes made of silk with flowers , NOTE : it is only for rent' ,

            '1 Decorative gray party chair , height : 60cm , width : 35cm , NOTE : it is only for rent' ,
            '1 Decorative brown party chair , height : 60cm , width : 35cm , NOTE : it is only for rent' ,
            '1 Decorative green party chair , height : 60cm , width : 35cm , NOTE : it is only for rent' ,
            '1 Decorative purple party chair , height : 60cm , width : 35cm , NOTE : it is only for rent' ,
            '1 Decorative blue party chair , height : 60cm , width : 35cm , NOTE : it is only for rent' ,
            '1 Decorative black picnic chair , height : 40cm , width : 25cm , NOTE : it is only for rent' ,
            '1 Decorative white picnic chair , height : 40cm , width : 25cm , NOTE : it is only for rent' ,
            '1 Decorative beech wood chair , height : 45cm , width : 30cm , NOTE : it is only for rent' ,
            '1 Decorative MDF wood chair , height : 35cm , width : 20cm , NOTE : it is only for rent' ,
            '1 Decorative MDF wood table , height : 75cm , width : 60cm , NOTE : it is only for rent' ,
            '1 Decorative black plastic table , height : 80cm , width : 35cm , NOTE : it is only for rent' ,
            '1 Decorative black iron table , height : 80cm , width : 40cm , NOTE : it is only for rent' ,
            '1 Decorative black glass table , height : 70cm , width : 45cm , NOTE : it is only for rent' ,
            '1 white event tent , Height : 2m , Width : 8m , Length : 4m , NOTE : it is only for rent' ,
            '1 white event tent , Height : 2.55m , Width : 8.75m , Length : 2.95m , NOTE : it is only for rent' ,
            '1 white event tent , Height : 3m , Width : 7m , Length : 3m , NOTE : it is only for rent' ,
            '1 white event tent , Height : 1.8m , Width : 6m , Length : 1.5m , NOTE : it is only for rent' ,

            '1 person who reads the holy Quran , NOTE : He takes 20.000 per hour',
            '1 person who prays for Muslims , NOTE : He does not take money',
            '1 person who leads Muslims in prayer , NOTE : He does not take money',
            '1 person who prays for Muslims in mosque , NOTE : He does not take money',
            '1 person who reads the Bible to Christians , NOTE : He does not take money' ,
            '1 person who preaches Christians , NOTE : He does not take money' ,
            '1 person who supervises Christians , NOTE : He does not take money',

            '8 people in traditional Arabic dress with swords and shield , NOTE : They takes 120.000 per hour' ,
            '6 people wearing long white traditional clothes , NOTE : They takes 160.000 per hour' ,
            '6 people wearing long white traditional clothes , NOTE : They takes 160.000 per hour' ,
            '8 people wearing red hats and traditional black clothes , NOTE : They takes 180.000 per hour' ,
            '8 people They have oud , guitar and drums , NOTE : They takes 180.000 per hour' ,
            '24 people playing different singing instruments , NOTE : They takes 300.000 per hour' ,
            '1 person who mixes tunes on a dedicated instrument',

            '1 display screen , 2m for long and 3m for wide , NOTE : it is only for rent' ,
            '1 air desert , 1m for long and 50cm for wide , NOTE : it is only for rent' ,
            '1 Spark emitting device , 30cm for long and 20cm for wide , NOTE : it is only for rent' ,

            '1 strobe lights for creating a dynamic lighting effect , NOTE : it is only for rent' ,
            '1 Stage lights in various colors and effects for creating atmosphere , NOTE : it is only for rent' ,
            '1 Classic disco ball for creating a fun and festive atmosphere , NOTE : it is only for rent' ,
            '1 decorative moonlights to create a soft and atmospheric glow , NOTE : it is only for rent' ,
            '1 fog machines to create a special effect or atmosphere for events , NOTE : it is only for rent' ,

            '1 Adjustable microphone stand for presentations or performances , NOTE : it is only for rent' ,
            '1 Portable speaker system for playing music or speeches , NOTE : it is only for rent' ,
            '1 Wireless microphones for presentations or performances with greater freedom of movement , NOTE : it is only for rent' ,
            '1 Audio mixing board for controlling sound levels of multiple audio sources , NOTE : it is only for rent' ,
        ];

        $accessories_categories_id = [
            1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 , 1 ,
            2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 , 2 ,
            3 , 3 , 3 , 3 , 3 , 3 , 3 ,
            4 , 4 , 4 , 4 , 4 , 4 ,
            5 , 5 , 5 ,
            6 , 6 , 6 , 6 , 6 ,
            7 , 7 , 7 , 7 ,
        ];

        for ($i = 0 ; $i < count($name) ; $i++)
        {
            Accessory::query()->create([
                'name' => $name[$i] ,
                'price' => $price[$i] ,
                'picture' => $picture[$i] ,
                'accessory_category_id' => $accessories_categories_id[$i] ,
                'description' => $description[$i]
            ]);
        }
    }
}
