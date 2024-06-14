<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Food;
use App\Models\Location;
use App\Models\LocationPicture;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Constraint\IsEmpty;

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
    public function getLocations(): JsonResponse
    {
        $locations = Location::select('id','name','governorate','open_time','close_time','logo')->get();

        if (!$locations->count() > 0){
            return response()->json([
                "error" => "No locations to show!",
                "status_code" => "404"
            ],404);
        }
        return response()->json($locations,200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    public function getLocationById($location_id): JsonResponse
    {
        $location = Location::find($location_id);
        if (!$location){
            return response()->json([
                "error" => "Location not found!",
                "status_code" => 404,
            ], 404);
        }

        $admin = User::with('profile')->find($location->user_id);

        $locationPictures = LocationPicture::whereLocationId($location_id)->pluck('picture');

        $locationData = [
            "id" => $location->id,
            "name" => $location->name,
            "governorate" => $location->governorate,
            "address" => $location->address,
            "capacity" => $location->capacity,
            "open_time" => $location->open_time,
            "close_time" => $location->close_time,
            "reservation_price" => $location->reservation_price,
            "x_position" => $location->x_position,
            "y_position" => $location->y_position,
            "logo" => $location->logo,
            "picture1" => $locationPictures[0],
            "picture2" => $locationPictures[1],
            "picture3" => $locationPictures[2],
            "admin_name" => $admin->name,
            "admin_email" => $admin->email,
            "admin_phone_number" => $admin->profile->phone_number,
        ];
        return response()->json($locationData, 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    public function SortLocation(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $validator = Validator::make($request->all(), [
            "host_id" => 'required|integer|exists:locations,host_id',
            "governorate" => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        $isGovernorateNull = strtolower($request->governorate) === 'null';

        $query = Location::query()->where('host_id' , $request->host_id);

        if (!$query->count() > 0) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        if($request->governorate && !$isGovernorateNull)
        {
            $query->where('governorate' , $request->governorate);
        }

        $locations = $query->select('id', 'name', 'governorate', 'open_time', 'close_time', 'capacity' , 'logo')->get();

        if ($locations->isEmpty() && $request->governorate  && !$isGovernorateNull) {
            return response()->json([
                'error' => TranslateTextHelper::translate("No locations found for the specified governorate"),
                'status_code' => 404,
            ], 404);
        }

        $name = $locations->pluck('name')->toArray();
        $name = TranslateTextHelper::batchTranslate($name);

        $governorate = $locations->pluck('governorate')->toArray();
        $governorate = TranslateTextHelper::batchTranslate($governorate);

        $response = [];
        foreach ($locations as $location)
        {
            $response [] = [
                'id' => $location -> id ,
                'name' => $name[$location->name] ,
                'governorate' => $governorate[$location->governorate] ,
                'open_time' => $location -> open_time ,
                'close_time' => $location -> close_time,
                'capacity' => $location -> capacity,
                'logo' => $location -> logo
            ];
        }


        sleep(1);
        return response()->json($response , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function GetAllGovernorate(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $governorate = Location::distinct()->pluck('governorate')->toArray();

        if(!$governorate)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        array_unshift($governorate, 'all');

        $response = TranslateTextHelper::batchTranslateArray($governorate);

        return response()->json($response , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////


}
