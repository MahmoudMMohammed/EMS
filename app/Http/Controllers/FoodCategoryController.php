<?php

namespace App\Http\Controllers;

use App\Models\HostFoodCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodCategoryController extends Controller
{
    public function getFoodCategory($host_id): JsonResponse
    {
        $exists = HostFoodCategory::query()->find($host_id);

        if (!$exists) {
            return response()->json(['error' => 'Host id is not found !'], 404);
        }

        $Categories = HostFoodCategory::where('host_id' , $host_id)->get();

        if(! $Categories->count() > 0)
        {
            return response()->json([
                    'message' => 'There are no category for this place']
                , 404);
        }

        $color = ['#443391' , '#3E5EAB' , '#1495CF' , '#60B246' , '#D1DC36' , '#F2EB3B' , '#F8BD19' , '#F89C21' , '#F25427' , '#F33128' , '#A71E4A' , '#7D3696' , '#443391' , '#3E5EAB' , '#1495CF' , '#60B246' , '#D1DC36' , '#F2EB3B' , '#F8BD19' , '#F89C21' , '#F25427' , '#F33128' , '#A71E4A' , '#7D3696'];
        $index = 0 ;

        $response = [];
        foreach ($Categories as $category)
        {
            $response [] = [
                'id' => $category->foodCategory->id ,
                'name' => $category->foodCategory->category ,
                'logo' => $category->foodCategory->logo ,
                'number' => $category->foodCategory->food->count() ,
                'color' => $color[$index]
            ] ;
            $index++;
        }
        return response()->json($response , 200);
    }
}
