<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function getFood(): JsonResponse
    {
        $food = Food::select('id','name','price','picture')->get();

        if (!$food->count() > 0){
            return response()->json([
                "error" => "No food to show!",
                "status_code" => "404"
            ],404);
        }
        foreach ($food as $item){
            $item->picture = "http://localhost:8000/$item->picture";
        }
        return response()->json($food,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getFoodById($food_id): JsonResponse
    {
        $food = Food::with('foodCategory')->find($food_id);
        if (!$food){
            return response()->json([
                "error" => "Food not found!",
                "status_code" => 404,
            ], 404);
        }
        $foodData = [
            "id" => $food->id,
            "name" => $food->name,
            "price" => $food->price,
            "category" => $food->foodCategory->category,
            "description" => $food->description,
            "country_of_origin" => $food->country_of_origin,
            "picture" => "http://localhost:8000/$food->picture",
        ];
        return response()->json($foodData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////

}
