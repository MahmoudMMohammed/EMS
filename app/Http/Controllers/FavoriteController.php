<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    public function addLocationToFavorites(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'location_id' => 'required | exists:locations,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $user = Auth::user();

        $existing = Favorite::whereUserId($user->id)
            ->whereLocationId($request->location_id)->first();

        if ($existing){
            return response()->json([
                "error" => 'Location is already in you favorites!',
                "status_code" => 400,
            ], 400);
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'location_id' => $request->location_id,
        ]);

        if (!$favorite){
            return response()->json([
                "error" => 'Failed to add location to favorites',
                "status_code" => 400,
            ], 400);
        }

        return response()->json([
            "message" => 'Location added to favorites successfully',
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////

    public function getUserFavorites(): JsonResponse
    {
        $user = Auth::user();

        $favorites = Favorite::whereUserId($user->id)->get();

        if (!$favorites->count > 0){
            return response()->json([
                "error" => 'You have not added anything to favorites yet!',
                "status_code" => 404,
            ], 404);
        }

        $favoritesData = [];
        foreach ($favorites as $favorite){
            $location = Location::find($favorite->location_id);
            $locationData = [];
            $locationData['name'] = $location->name;
            $locationData['governorate'] = $location->governorate;
            $locationData['open_time'] = $location->open_time;
            $locationData['close_time'] = $location->close_time;
            $locationData['logo'] = $location->logo;

            $favoritesData[] = $locationData;
        }

        return response()->json([
            "message" => 'These are your favorites:',
            "data" => $favoritesData,
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////

    public function removeFromFavorites(Request $request , $location_id): JsonResponse
    {
        $user = Auth::user();

        $location = Location::find($location_id);
        if (!$location){
            return response()->json([
                "error" => "Location not found!",
                "status_code" => 404,
            ], 404);
        }

        $favorite = Favorite::whereUserId($user->id)
            ->whereLocationId($request->location_id)->first();

        if (!$favorite){
            return response()->json([
                "error" => "Location is not in your favorites!",
                "status_code" => 400,
            ], 400);
        }

        $favorite->delete();
        return response()->json([
            "message" => "Location removed from your favorites successfully",
            "status_code" => 200,
        ], 200);

    }
    ////////////////////////////////////////////////////////////////////////////////////

}
