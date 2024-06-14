<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Favorite;
use App\Models\Food;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    public function addToFavorites(Request $request): JsonResponse
    {
        $validationError = $this->validateFavoriteItem($request);
        if ($validationError) {
            return response()->json($validationError, 422);
        }

        $user = Auth::user();

        $item = $this->getItem($request->favoritable_type, $request->favoritable_id);

        // Check if the favoritable item exists
        if (!$item) {
            return response()->json([
                "error" => 'The selected favoritable item does not exist',
                "status_code" => 404,
            ], 404);
        }

        $existing = Favorite::whereUserId($user->id)
            ->where('favoritable_type', get_class($item))
            ->where('favoritable_id', $request->favoritable_id)
            ->first();

        if ($existing) {
            return response()->json([
                "error" => ucfirst($request->favoritable_type) . ' is already in your favorites!',
                "status_code" => 400,
            ], 400);
        }

        $favorite = new Favorite(['user_id' => $user->id]);
        $favorite->favoritable()->associate($item);
        $favorite->save();


        return response()->json([
            "message" => ucfirst($request->favoritable_type) . ' added to favorites successfully',
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////
    public function getUserFavorites(): JsonResponse
    {
        $user = Auth::user();

        $favorites = Favorite::whereUserId($user->id)->get();

        if ($favorites->isEmpty()) {
            return response()->json([
                "error" => 'You have not added anything to favorites yet!',
                "status_code" => 404,
            ], 404);
        }

        $favoritesData = $this->prepareFavoritesData($user, $favorites);


        return response()->json($favoritesData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////

    public function removeFromFavorites(Request $request): JsonResponse
    {
        $validationError = $this->validateFavoriteItem($request);
        if ($validationError) {
            return response()->json($validationError, 422);
        }

        $user = Auth::user();
        $item = $this->getItem($request->favoritable_type, $request->favoritable_id);

        // Check if the favoritable item exists
        if (!$item) {
            return response()->json([
                "error" => 'The selected favoritable item does not exist',
                "status_code" => 404,
            ], 404);
        }

        // Find the favorite entry
        $favorite = Favorite::whereUserId($user->id)
            ->where('favoritable_type', get_class($item))
            ->where('favoritable_id', $request->favoritable_id)
            ->first();

        if (!$favorite) {
            return response()->json([
                "error" => ucfirst($request->favoritable_type) . " is not in your favorites!",
                "status_code" => 400,
            ], 400);
        }

        // Delete the favorite entry
        $favorite->delete();

        return response()->json([
            "message" => ucfirst($request->favoritable_type) . " removed from your favorites successfully",
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////
    private function getItem($type, $itemId)
    {
        switch ($type) {
            case 'food':
                return Food::find($itemId);
            case 'drink':
                return Drink::find($itemId);
            case 'accessory':
                return Accessory::find($itemId);
            case 'location' :
                return Location::find($itemId);
            default:
                return response()->json([
                    "error" => 'Invalid favoritable type',
                    "status_code" => 422,
                ], 422);
        }
    }
    /////////////////////////////////////////
    private function validateFavoriteItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'favoritable_id' => 'required|integer',
            'favoritable_type' => 'required|string|in:food,drink,accessory,location'
        ]);

        if ($validator->fails()) {
            return [
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ];
        }
        return null;
    }
    /////////////////////////////////////////
    private function prepareFavoritesData($user, $favorites)
    {
        $items = [
            'locations' => [],
            'foods' => [],
            'drinks' => [],
            'accessories' => []
        ];

        foreach ($favorites as $favorite) {
            $item = $favorite->favoritable;
            if ($item) {
                switch (class_basename($item)) {
                    case 'Location':
                        $items['locations'][] = $item;
                        break;
                    case 'Food':
                        $items['foods'][] = $item;
                        break;
                    case 'Drink':
                        $items['drinks'][] = $item;
                        break;
                    case 'Accessory':
                        $items['accessories'][] = $item;
                        break;
                }
            }
        }

        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $translatedLocationNames = TranslateTextHelper::batchTranslate(array_column($items['locations'], 'name'));
        $translatedGovernorates = TranslateTextHelper::batchTranslate(array_column($items['locations'], 'governorate'));
        $translatedFoodNames = TranslateTextHelper::batchTranslate(array_column($items['foods'], 'name'));
        $translatedDrinkNames = TranslateTextHelper::batchTranslate(array_column($items['drinks'], 'name'));
        $translatedAccessoryNames = TranslateTextHelper::batchTranslate(array_column($items['accessories'], 'name'));

        return $this->formatFavoritesData($favorites, [
            'locations' => [$translatedLocationNames, $translatedGovernorates],
            'foods' => $translatedFoodNames,
            'drinks' => $translatedDrinkNames,
            'accessories' => $translatedAccessoryNames
        ]);
    }
    /////////////////////////////////////////

    private function formatFavoritesData($favorites, $translations)
    {
        $favoritesData = [];
        foreach ($favorites as $favorite) {
            $itemData = [];
            $item = $favorite->favoritable;
            if ($item) {
                switch (get_class($item)) {
                    case Location::class:
                        $itemData['type'] = 'location';
                        $itemData['name'] = $translations['locations'][0][$item->name] ?? $item->name;
                        $itemData['governorate'] = $translations['locations'][1][$item->governorate] ?? $item->governorate;
                        $itemData['open_time'] = $item->open_time;
                        $itemData['close_time'] = $item->close_time;
                        $itemData['logo'] = $item->logo;
                        break;
                    case Food::class:
                        $itemData['type'] = 'food';
                        $itemData['name'] = $translations['foods'][$item->name] ?? $item->name;
                        $itemData['price'] = $item->price;
                        $itemData['description'] = $item->description;
                        $itemData['picture'] = $item->picture;
                        break;
                    case Drink::class:
                        $itemData['type'] = 'drink';
                        $itemData['name'] = $translations['drinks'][$item->name] ?? $item->name;
                        $itemData['price'] = $item->price;
                        $itemData['description'] = $item->description;
                        $itemData['picture'] = $item->picture;
                        break;
                    case Accessory::class:
                        $itemData['type'] = 'accessory';
                        $itemData['name'] = $translations['accessories'][$item->name] ?? $item->name;
                        $itemData['price'] = $item->price;
                        $itemData['description'] = $item->description;
                        $itemData['picture'] = $item->picture;
                        break;
                }
                $favoritesData[] = $itemData;
            }
        }
        return $favoritesData;
    }
    /////////////////////////////////////////

}
