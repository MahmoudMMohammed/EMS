<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\AccessoryCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessoryController extends Controller
{
    public function getAccessoriesByCategory($category_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $accessoryCategory = AccessoryCategory::find($category_id);
        if (!$accessoryCategory){
            return response()->json([
                "error" => "Category not found!",
                "status_code" => "404"
            ],404);
        }

        $accessories = Accessory::select('id','name','price','picture', 'description')->whereAccessoryCategoryId($category_id)->get();

        $names = $accessories->pluck('name')->values()->toArray();
        $descriptions = $accessories->pluck('description')->values()->toArray();

        $translatedNames = TranslateTextHelper::batchTranslate($names);
        $translatedDescriptions = TranslateTextHelper::batchTranslate($descriptions);

        if (!$accessories->count() > 0){
            return response()->json([
                "error" => TranslateTextHelper::translate("No accessories to show!"),
                "status_code" => "404"
            ],404);
        }
        foreach ($accessories as $item){
            $item->name = $translatedNames[$item->name];
            $item->description = $translatedDescriptions[$item->description];
        }
        return response()->json($accessories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getAccessoryById($accessory_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $accessory = Accessory::with('accessoriesCategory')->find($accessory_id);
        if (!$accessory){
            return response()->json([
                "error" => "Accessory not found!",
                "status_code" => 404,
            ], 404);
        }
        $accessoryData = [
            "id" => $accessory->id,
            "name" => TranslateTextHelper::translate($accessory->name),
            "price" => $accessory->price,
            "category" => TranslateTextHelper::translate($accessory->accessoriesCategory->category),
            "description" => TranslateTextHelper::translate($accessory->description),
            "picture" => $accessory->picture,
        ];
        return response()->json($accessoryData, 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function getAccessoriesCategories(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $accessoriesCategories = AccessoryCategory::all();
        if (!$accessoriesCategories){
            return response()->json([
                "error" => TranslateTextHelper::translate("No accessories categories to show!"),
                "status_code" => "404"
            ],404);
        }

        $names = $accessoriesCategories->pluck('category')->values()->toArray();
        $translatedNames = TranslateTextHelper::batchTranslate($names);

        foreach ($accessoriesCategories as $category){
            $category->category = $translatedNames[$category->category];
        }
        return response()->json($accessoriesCategories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////


}
