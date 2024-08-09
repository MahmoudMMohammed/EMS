<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Food;
use App\Models\Location;
use App\Models\LocationPicture;
use App\Models\User;
use App\Models\UserEvent;
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
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $locations = Location::select('id', 'name', 'governorate', 'open_time', 'close_time', 'logo')->get();

        if ($locations->isEmpty()) {
            return response()->json([
                "error" => "No locations to show!",
                "status_code" => 404
            ], 404);
        }

        // Convert the plucked collections to arrays and reset indices
        $names = $locations->pluck('name')->values()->toArray();
        $governorates = $locations->pluck('governorate')->values()->toArray();

        // Translate names and governorates
        $translatedNames = TranslateTextHelper::batchTranslate($names);
        $translatedGovernorates = TranslateTextHelper::batchTranslate($governorates);

        // Map the translated names and governorates back to the locations
        foreach ($locations as  $location) {
            $location->name = $translatedNames[$location->name] ?? $location->name;
            $location->governorate = $translatedGovernorates[$location->governorate] ?? $location->governorate;
        }

        return response()->json($locations, 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    public function getLocationById($location_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $location = Location::find($location_id);
        if (!$location){
            return response()->json([
                "error" => "Location not found!",
                "status_code" => 404,
            ], 404);
        }

        $admin = User::with('profile')->find($location->user_id);

        $locationPictures = LocationPicture::whereLocationId($location_id)->pluck('picture');

        $isFavorite = false;

        foreach ($user->favorites as $favorite){
            if ($location_id == $favorite->favoritable_id && get_class($location) == $favorite->favoritable_type){
                $isFavorite = true;
                break;
            }
        }

        $locationData = [
            "id" => $location->id,
            "name" => TranslateTextHelper::translate($location->name),
            "governorate" => TranslateTextHelper::translate($location->governorate),
            "address" => TranslateTextHelper::translate($location->address),
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
            "is_favorite" => $isFavorite,
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
    public function WebGetLocationByHost($category_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        if($category_id < 0 || $category_id > 7)
        {
            return response()->json([
                "error" => "invalid category ID must be between 0 and 7",
                "status_code" => 422,
            ], 422);
        }

        $result = [];
        if($category_id == 0)
        {
            $result = Location::query()->select('id' , 'name' , 'governorate' , 'capacity' , 'open_time' , 'close_time' , 'logo')->get();
            if($result->isEmpty())
            {
                return response()->json([
                    "message" => "There are no Locations.",
                    "status_code" => 404,
                ], 404);
            }
        }
        elseif (in_array($category_id , [1,2,3,4,5,6,7]))
        {
            $result = Location::query()
                ->where('host_id' , $category_id)
                ->select('id' , 'name' , 'governorate' , 'capacity' , 'open_time' , 'close_time' , 'logo')
                ->get();

            if($result->isEmpty())
            {
                return response()->json([
                    "message" => "There are no Locations for this specific host.",
                    "status_code" => 404,
                ], 404);
            }
        }
        return response()->json($result , 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebGetLocationGeneral($location_id): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $photo = LocationPicture::query()
            ->where('location_id' , $location_id)
            ->orderBy('id')
            ->select('id' , 'picture')
            ->get();


        $response = [
            'id' => $exist->id ,
            'governorate' => $exist->governorate ,
            'address' => $exist->address ,
            'host' => $exist->host->name ,
            'Xp' => $exist->x_position ,
            'Yp' => $exist->y_position ,
            'id_photo_1' => $photo[0]->id ,
            'photo_1' => $photo[0]->picture ,
            'id_photo_2' => $photo[1]->id ,
            'photo_2' => $photo[1]->picture ,
            'id_photo_3' => $photo[2]->id ,
            'photo_3' => $photo[2]->picture

        ];
        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebGetLocationDetails($location_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $response = [
            'id' => $exist->id ,
            'name' => $exist->name ,
            'hour_price' => number_format($exist->reservation_price).' S.P' ,
            'open_time' => $exist->open_time ,
            'close_time' => $exist->close_time ,
            'capacity' => $exist->capacity
        ];
        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebEditLocationDetails(Request $request , $location_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $validator = Validator::make($request->all() , [
            'name' => 'required|max:50' ,
            'price' => 'required|integer' ,
            'open' => 'required|date_format:h:i A' ,
            'close' => 'required|date_format:h:i A' ,
            'capacity' => 'required|integer'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $exist->update([
            'name' => $request->input('name') ,
            'reservation_price' => $request->input('price'),
            'open_time' => $request->input('open'),
            'close_time' => $request->input('close'),
            'capacity' => $request->input('capacity'),
        ]);

        return response()->json([
            "message" => "Location details updated successfully",
            "status_code" => 200,
        ], 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebGetLocationAdmin($location_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $response = [
            'id' => $exist->admin->id ,
            'name' => $exist->admin->name ,
            'email' => $exist->admin->email ,
            'phone_number' => $exist->admin->profile->phone_number
        ];

        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebDeleteLocation($location_id):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }


        $event = UserEvent::query()
            ->where('location_id' , $exist->id)
            ->whereNotIn('verified', ['Finished', 'Rejected'])
            ->exists();

        if($event)
        {
            return response()->json([
                "message" => "You cannot delete this place because there are reservations attached to it.",
                "status_code" => 422,
            ], 422);
        }

        $exist->delete();

        return response()->json([
            "message" => "This location has been successfully deleted" ,
            "status_code" => 200,
        ] , 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebPutLocationInMaintenance($location_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $event = UserEvent::query()
            ->where('location_id' , $exist->id)
            ->whereNotIn('verified', ['Finished', 'Rejected'])
            ->exists();

        if($event)
        {
            return response()->json([
                "message" => "You cannot maintain this place because there are reservations attached to it.",
                "status_code" => 422,
            ], 422);
        }

        if($exist->maintenance == 1)
        {
            return response()->json([
                "message" => "This place is actually under maintenance.",
                "status_code" => 422,
            ], 422);
        }

        $exist->maintenance = 1;
        $exist->save();

        return response()->json([
            "message" => "This place has been successfully put into maintenance" ,
            "status_code" => 200,
        ]);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebPutLocationInService($location_id):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        if($exist->maintenance == 0)
        {
            return response()->json([
                "message" => "This place is actually under service.",
                "status_code" => 422,
            ], 422);
        }

        $exist->maintenance = 0;
        $exist->save();

        return response()->json([
            "message" => "This place has been successfully put into service" ,
            "status_code" => 200,
        ]);
    }
}
