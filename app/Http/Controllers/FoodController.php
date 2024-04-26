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
                'message' => 'Something went wrong , try again later' ,
                'status' => 400
            ] , 400);
        }

        return response()->json(['Food_Count' => $Food , 'status' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

}
