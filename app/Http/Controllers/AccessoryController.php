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
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        return response()->json(['accessory_count' => $Accessory , 'status_code' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

}
