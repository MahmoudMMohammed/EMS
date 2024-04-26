<?php

namespace App\Http\Controllers;

use App\Models\Drink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrinkController extends Controller
{
    public function CountDrink(): JsonResponse
    {
        $Drink = Drink::query()->count();

        if(!$Drink){
            return response()->json([
                'message' => 'Something went wrong , try again later' ,
                'status' => 400
            ] , 400);
        }

        return response()->json(['Drink_Count' => $Drink , 'status' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

}
