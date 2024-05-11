<?php

namespace App\Http\Controllers;

use App\Models\Drink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrinkController extends Controller
{
    public function getDrinks(): JsonResponse
    {
        $drinks = Drink::select('id','name','price','picture')->get();

        if (!$drinks->count() > 0){
            return response()->json([
                "error" => "No drinks to show!",
                "status_code" => "404"
            ],404);
        }
        foreach ($drinks as $drink){
            $drink->picture = "http://localhost:8000/$drink->picture";
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
            "picture" => "http://localhost:8000/$drink->picture",
        ];
        return response()->json($drinkData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////


}
