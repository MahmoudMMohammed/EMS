<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\AccessoryCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessoryController extends Controller
{
    public function getAccessoriesByCategory($category_id): JsonResponse
    {
        $accessoryCategory = AccessoryCategory::find($category_id);
        if (!$accessoryCategory){
            return response()->json([
                "error" => "Category not found!",
                "status_code" => "404"
            ],404);
        }

        $accessories = Accessory::select('id','name','price','picture')->whereAccessoryCategoryId($category_id)->get();

        if (!$accessories->count() > 0){
            return response()->json([
                "error" => "No accessories to show!",
                "status_code" => "404"
            ],404);
        }

        return response()->json($accessories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getAccessoryById($accessory_id): JsonResponse
    {
        $accessory = Accessory::with('accessoriesCategory')->find($accessory_id);
        if (!$accessory){
            return response()->json([
                "error" => "Accessory not found!",
                "status_code" => 404,
            ], 404);
        }
        $accessoryData = [
            "id" => $accessory->id,
            "name" => $accessory->name,
            "price" => $accessory->price,
            "category" => $accessory->accessoriesCategory->category,
            "description" => $accessory->description,
            "picture" => $accessory->picture,
        ];
        return response()->json($accessoryData, 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function getAccessoriesCategories(): JsonResponse
    {
        $accessoriesCategories = AccessoryCategory::all();
        if (!$accessoriesCategories){
            return response()->json([
                "error" => "No accessories categories to show!",
                "status_code" => "404"
            ],404);
        }
        return response()->json($accessoriesCategories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////


}
