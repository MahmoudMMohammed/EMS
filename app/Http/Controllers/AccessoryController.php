<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessoryController extends Controller
{
    public function getAccessories(): JsonResponse
    {
        $accessories = Accessory::select('id','name','price','picture')->get();

        if (!$accessories->count() > 0){
            return response()->json([
                "error" => "No accessories to show!",
                "status_code" => "404"
            ],404);
        }
        foreach ($accessories as $accessory){
            $accessory->picture = "http://localhost:8000/$accessory->picture";
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
            "picture" => "http://localhost:8000/$accessory->picture",
        ];
        return response()->json($accessoryData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////


}
