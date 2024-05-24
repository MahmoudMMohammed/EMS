<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\FoodCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function getFoodByCategory($category_id): JsonResponse
    {
        $foodCategory = FoodCategory::find($category_id);
        if (!$foodCategory){
            return response()->json([
                "error" => "Category not found!",
                "status_code" => "404"
            ],404);
        }
        $food = Food::select('id','name','price','picture')->whereFoodCategoryId($category_id)->get();

        if (!$food->count() > 0){
            return response()->json([
                "error" => "No food to show!",
                "status_code" => "404"
            ],404);
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
            "picture" => $food->picture,
        ];
        return response()->json($foodData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function getFoodCategories(): JsonResponse
    {
        $foodCategories = FoodCategory::all();
        if (!$foodCategories){
            return response()->json([
                "error" => "No food categories to show!",
                "status_code" => "404"
            ],404);
        }
        return response()->json($foodCategories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

}
