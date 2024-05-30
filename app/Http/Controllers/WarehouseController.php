<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function GetWarehouseByGovernorate(Request $request): JsonResponse
    {
       $validator = Validator::make($request->all() , [
           'governorate' => 'required|string'
       ]);

       if($validator->fails())
       {
           return response()->json([
               "error" => $validator->errors()->first(),
               "status_code" => 422,
           ] , 422);
       }

        $isGovernorateAll = strtolower($request->governorate) === 'all';

        if($request -> governorate && !$isGovernorateAll)
        {
            $query = Warehouse::query()->where('governorate' , $request->governorate)->get();
            if($query->isEmpty())
            {
                return response()->json([
                    "error" => "There are no warehouses in this governorate !",
                    "status_code" => 404
                ] , 404);
            }
            return response()->json($query , 200);
        }

        $query = Warehouse::query()->get();

        if($query->isEmpty())
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ] , 422);
        }

        return response()->json($query , 200);
    }

}
