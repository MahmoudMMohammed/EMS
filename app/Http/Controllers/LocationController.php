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
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        return response()->json(['location_count' => $location , 'status_code' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////


}
