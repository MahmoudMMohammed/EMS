<?php

namespace App\Http\Controllers;

use App\Models\Drink;
use App\Models\DrinkCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrinkController extends Controller
{
    public function getDrinksByCategory($category_id): JsonResponse
    {
        $drinkCategory = DrinkCategory::find($category_id);
        if (!$drinkCategory){
            return response()->json([
                "error" => "Category not found!",
                "status_code" => "404"
            ],404);
        }

        $drinks = Drink::select('id','name','price','picture')->whereDrinkCategoryId($category_id)->get();

        if (!$drinks->count() > 0){
            return response()->json([
                "error" => "No drinks to show!",
                "status_code" => "404"
            ],404);
        }
        return response()->json($drinks,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getDrinkById($drink_id): JsonResponse
    {
        $drink = Drink::with('drinkCategory')->find($drink_id);
        if (!$drink){
            return response()->json([
                "error" => "Drink not found!",
                "status_code" => 404,
            ], 404);
        }
        $drinkData = [
            "id" => $drink->id,
            "name" => $drink->name,
            "price" => $drink->price,
            "category" => $drink->drinkCategory->category,
            "description" => $drink->description,
            "picture" => $drink->picture,
        ];
        return response()->json($drinkData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function getDrinksCategories(): JsonResponse
    {
        $drinksCategories = DrinkCategory::all();
        if (!$drinksCategories){
            return response()->json([
                "error" => "No drinks categories to show!",
                "status_code" => "404"
            ],404);
        }
        return response()->json($drinksCategories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

}
