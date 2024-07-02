<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\AccessoryCategory;
use App\Models\Location;
use App\Models\Warehouse;
use App\Models\WarehouseAccessory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
    public function getAccessoriesByCategorySorted(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "category_id" => 'required|integer|exists:drink_categories,id',
            "location_id" => 'required|integer|exists:locations,id',
            "type" => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $isTypeNull = $request->type == '-1';

        $location = Location::query()->where('id', $request->location_id)->pluck('governorate')->first();

        $warehouse = Warehouse::query()->where('governorate', $location)->pluck('id')->first();

        $warehouse_accessories = WarehouseAccessory::query()->where('warehouse_id', $warehouse)->get();

        $accessory_ids = $warehouse_accessories->pluck('accessory_id');

        $accessories = Accessory::query()->whereIn('id', $accessory_ids)
            ->where('accessory_category_id', $request->category_id);

        if (!$accessories->count() > 0) {
            return response()->json([
                "error" => "No accessory found for the specified category",
                "status_code" => 404,
            ], 404);
        }

        if ($request->type && !$isTypeNull) {
            $accessories->where('price', '<=', $request->type);
        }

        $items = $accessories->orderBy('price')->get();

        if ($items->isEmpty() && $request->type && !$isTypeNull) {
            return response()->json([
                'error' => "No accessory found for the specified price",
                'status_code' => 404,
            ], 404);
        }

        $response = [];
        foreach ($items as $item) {

            $quantity = $warehouse_accessories->where('accessory_id', $item->id)->pluck('quantity')->first();

            $response[] = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'description' => $item->description,
                'picture' => $item->picture,
                'quantity' => $quantity
            ];
        }

        // Return the response
        return response()->json($response, 200);
    }


}
