<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Food;
use App\Models\FoodCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FoodController extends Controller
{
    public function getFoodByCategory($category_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $foodCategory = FoodCategory::find($category_id);
        if (!$foodCategory){
            return response()->json([
                "error" => TranslateTextHelper::translate("Category not found!"),
                "status_code" => "404"
            ],404);
        }
        $food = Food::select('id','name','price','picture', 'description', 'country_of_origin')->whereFoodCategoryId($category_id)->get();

        $names = $food->pluck('name')->values()->toArray();
        $descriptions = $food->pluck('description')->values()->toArray();
        $countries = $food->pluck('country_of_origin')->values()->toArray();

        $translatedNames = TranslateTextHelper::batchTranslate($names);
        $translatedDescriptions = TranslateTextHelper::batchTranslate($descriptions);
        $translatedCountries = TranslateTextHelper::batchTranslate($countries);

        if (!$food->count() > 0){
            return response()->json([
                "error" => TranslateTextHelper::translate("No food to show!"),
                "status_code" => "404"
            ],404);
        }

        foreach ($food as $item){
            $item->name = $translatedNames[$item->name];
            $item->description = $translatedDescriptions[$item->description];
            $item->country_of_origin = $translatedCountries[$item->country_of_origin];
        }
        return response()->json($food,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getFoodById($food_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $food = Food::with('foodCategory')->find($food_id);
        if (!$food){
            return response()->json([
                "error" => TranslateTextHelper::translate("Food not found!"),
                "status_code" => 404,
            ], 404);
        }
        $foodData = [
            "id" => $food->id,
            "name" => TranslateTextHelper::translate($food->name),
            "price" => $food->price,
            "category" => TranslateTextHelper::translate($food->foodCategory->category),
            "description" => TranslateTextHelper::translate($food->description),
            "country_of_origin" => TranslateTextHelper::translate($food->country_of_origin),
            "picture" => $food->picture,
        ];
        return response()->json($foodData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function getFoodCategories(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $foodCategories = FoodCategory::all();
        if (!$foodCategories){
            return response()->json([
                "error" => TranslateTextHelper::translate("No food categories to show!"),
                "status_code" => "404"
            ],404);
        }
        $names = $foodCategories->pluck('category')->values()->toArray();
        $translatedNames = TranslateTextHelper::batchTranslate($names);

        foreach ($foodCategories as $category){
            $category->category = $translatedNames[$category->category];
        }

        return response()->json($foodCategories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

}
