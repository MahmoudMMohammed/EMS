<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Food;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function HomeCount(): JsonResponse
    {
        $location = Location::query()->count();
        if(!$location){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Accessory = Accessory::query()->count();
        if(!$Accessory){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Food = Food::query()->count();
        if(!$Food){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Drink = Drink::query()->count();

        if(!$Drink){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        return response()->json(['location_count' => $location ,
                                 'food_count' => $Food ,
                                 'drink_count' => $Drink ,
                                 'accessory_count' => $Accessory ,
                                 'status_code' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function GetAllLocation (Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "host_id" => 'required|exists:locations,host_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }
        $locations = Location::query()->where('host_id', $request->host_id)->select( 'id' , 'name' , 'governorate' , 'open_time' , 'close_time' , 'capacity' , 'logo')->get();

        if (!$locations)
        {
            return response()->json([
                "error" => "Something went wrong , try again later" ,
                "status_code" => 422,
            ], 422);
        }

        foreach ($locations as $location)
        {
            $location->logo = 'http://localhost:8000/' . $location->logo;
        }

        return response()->json($locations ,200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    public function SortLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "host_id" => 'required|exists:locations,host_id',
            "governorate" => 'required|exists:locations,governorate'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $locations = Location::query()
            ->where('governorate', $request->governorate)
            ->where('host_id', $request->host_id)
            ->select('id', 'name', 'governorate', 'open_time', 'close_time', 'logo')
            ->get();

        if (!$locations) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        foreach ($locations as $location) {
            $location->logo = 'http://localhost:8000/' . $location->logo;
        }

        return response()->json($locations , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function GetAllGovernorate(): JsonResponse
    {
        $governorate = Location::distinct()->pluck('governorate');

        if(!$governorate)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        return response()->json($governorate , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////

}
