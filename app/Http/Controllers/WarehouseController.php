<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function GetWarehouseByGovernorate(Request $request): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user-> profile -> preferred_language);

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
                    "error" => TranslateTextHelper::translate("There are no warehouses in this governorate"),
                    "status_code" => 404
                ] , 404);
            }
            return response()->json($query , 200);
        }

        $queries = Warehouse::query()->get();

        if($queries->isEmpty())
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ] , 422);
        }

        $governorate = $queries->pluck('governorate')->toArray();
        $governorate = TranslateTextHelper::batchTranslate($governorate);

        $address = $queries->pluck('address')->toArray();
        $address = TranslateTextHelper::batchTranslate($address);

        $response = [];
        foreach ($queries as $query)
        {
            $response [] = [
                'id' => $query->id ,
                'governorate' => $governorate[$query->governorate] ,
                'address' => $address[$query->address]
            ];
        }

        return response()->json($response , 200);
    }

}
