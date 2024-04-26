<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessoryController extends Controller
{
    public function CountAccessory(): JsonResponse
    {
        $Accessory = Accessory::query()->count();

        if(!$Accessory){
            return response()->json([
                'message' => 'Something went wrong , try again later' ,
                'status' => 400
            ] , 400);
        }

        return response()->json(['Accessory_Count' => $Accessory , 'status' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

}
