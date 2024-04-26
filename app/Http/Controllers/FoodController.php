<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function CountFood(): JsonResponse
    {
        $Food = Food::query()->count();

        if(!$Food){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        return response()->json(['food_count' => $Food , 'status_code' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

}
