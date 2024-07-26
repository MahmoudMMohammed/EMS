<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Drink;
use App\Models\DrinkCategory;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
    public function getDrinksByCategorySorted(Request $request): JsonResponse
    {
        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $validator = Validator::make($request->all() , [
            "category_id" => 'required|integer|exists:drink_categories,id',
            "type" => 'required|integer|between:-1,999999'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $isTypeNull = $request->type == '-1' ;

        $query = Drink::query()->where('drink_category_id' , $request->category_id);

        if(!$query->count() > 0)
        {
            return response()->json([
                "error" => TranslateTextHelper::translate("No drinks found for the specified category"),
                "status_code" => 404,
            ], 404);
        }

        if($request->type && !$isTypeNull)
        {
            $query->where('price' , '<=' , $request->type);
        }

        $drinks = $query->orderBy('price')->get();

        if($drinks->isEmpty() && $request->type && !$isTypeNull )
        {
            return response()->json([
                'error' =>TranslateTextHelper::translate("No drinks found for the specified price"),
                'status_code' => 404,
            ], 404);
        }

        $name = $drinks->pluck('name')->toArray();
        $name = TranslateTextHelper::batchTranslate($name);

        $description = $drinks->pluck('description')->toArray();
        $description = TranslateTextHelper::batchTranslate($description);

        $drinksIds = $drinks->pluck('id')->toArray();

        $favorites = Favorite::query()
            ->where('favoritable_type' , 'App\Models\Drink')
            ->whereIn('favoritable_id' , $drinksIds)
            ->pluck('favoritable_id')
            ->toArray();

        $response = [];
        foreach ($drinks as $drink)
        {
            $response [] = [
                'id' => $drink->id ,
                'name' => $name[$drink->name] ,
                'price' => $drink->RawPrice ,
                'currency' => 'S.P' ,
                'description' => $description[$drink->description] ,
                'picture' => $drink->picture ,
                'is_favorite' => in_array($drink->id , $favorites)
            ];
        }

        return response()->json($response , 200);
    }
}
