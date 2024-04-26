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
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        return response()->json(['drink_count' => $Drink , 'status_code' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

}
