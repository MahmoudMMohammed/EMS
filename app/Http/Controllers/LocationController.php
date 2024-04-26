<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\IsEmpty;

class LocationController extends Controller
{
    public function CountLocation(): JsonResponse
    {
        $location = Location::query()->count();

        if(!$location){
            return response()->json([
                'message' => 'Something went wrong , try again later' ,
                'status' => 400
            ] , 400);
        }

        return response()->json(['Location_Count' => $location , 'status' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////


}
