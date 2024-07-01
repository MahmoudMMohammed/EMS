<?php

namespace App\Http\Controllers;

use App\Models\HostDrinkCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrinkCategoryController extends Controller
{
    public function getDrinksCategory($host_id): JsonResponse
    {
        $exists = HostDrinkCategory::query()->find($host_id);

        if (!$exists) {
            return response()->json(['error' => 'Host id is not found !'], 404);
        }

        $Categories = HostDrinkCategory::where('host_id' , $host_id)->get();

        if(! $Categories->count() > 0)
        {
            return response()->json([
                    'message' => 'There are no category for this place']
                , 404);
        }

        $color = ['#443391' , '#3E5EAB' , '#1495CF' , '#60B246' , '#D1DC36' , '#F2EB3B' , '#F8BD19' , '#F89C21' , '#F25427' , '#F33128' , '#A71E4A' , '#7D3696' , '#443391' , '#3E5EAB' , '#1495CF' , '#60B246' , '#D1DC36' , '#F2EB3B' , '#F8BD19' , '#F89C21' , '#F25427' , '#F33128' , '#A71E4A' , '#7D3696'];

        $response = [];
        $index = 0 ;
        foreach ($Categories as $category)
        {
            $response [] = [
                'id' => $category->drinkCategory->id ,
                'name' => $category->drinkCategory->category ,
                'logo' => $category->drinkCategory->logo ,
                'number' => $category->drinkCategory->drinks->count(),
                'color' => $color[$index]
            ] ;
            $index++;
        }
        return response()->json($response , 200);
    }
}
