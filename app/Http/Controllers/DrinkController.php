<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Drink;
use App\Models\DrinkCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DrinkController extends Controller
{
    public function getDrinksByCategory($category_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $drinkCategory = DrinkCategory::find($category_id);
        if (!$drinkCategory){
            return response()->json([
                "error" => TranslateTextHelper::translate("Category not found!"),
                "status_code" => "404"
            ],404);
        }

        $drinks = Drink::select('id','name','price','picture', 'description')->whereDrinkCategoryId($category_id)->get();

        $names = $drinks->pluck('name')->values()->toArray();
        $descriptions = $drinks->pluck('description')->values()->toArray();

        $translatedNames = TranslateTextHelper::batchTranslate($names);
        $translatedDescriptions = TranslateTextHelper::batchTranslate($descriptions);

        if (!$drinks->count() > 0){
            return response()->json([
                "error" => TranslateTextHelper::translate("No drinks to show!"),
                "status_code" => "404"
            ],404);
        }

        foreach ($drinks as $item){
            $item->name = $translatedNames[$item->name];
            $item->description = $translatedDescriptions[$item->description];
        }

        return response()->json($drinks,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getDrinkById($drink_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $drink = Drink::with('drinkCategory')->find($drink_id);
        if (!$drink){
            return response()->json([
                "error" => "Drink not found!",
                "status_code" => 404,
            ], 404);
        }
        $drinkData = [
            "id" => $drink->id,
            "name" => TranslateTextHelper::translate($drink->name),
            "price" => $drink->price,
            "category" => TranslateTextHelper::translate($drink->drinkCategory->category),
            "description" => TranslateTextHelper::translate($drink->description),
            "picture" => $drink->picture,
        ];
        return response()->json($drinkData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function getDrinksCategories(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $drinksCategories = DrinkCategory::all();

        if (!$drinksCategories){
            return response()->json([
                "error" => TranslateTextHelper::translate("No drinks categories to show!"),
                "status_code" => "404"
            ],404);
        }
        $names = $drinksCategories->pluck('category')->values()->toArray();
        $translatedNames = TranslateTextHelper::batchTranslate($names);

        foreach ($drinksCategories as $category){
            $category->category = $translatedNames[$category->category];
        }
        return response()->json($drinksCategories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

}
