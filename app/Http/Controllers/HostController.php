<?php

namespace App\Http\Controllers;

use App\Models\Host;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostController extends Controller
{
    public function WebGetHosts(): JsonResponse
    {
        $hosts = Host::query()->get();
        if(!$hosts->count() > 0)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ]);
        }
        return response()->json($hosts , 200);
    }

    /////////////////////////////////////////////////////////////////////////////
    public function GetLocationRelatedHost ($id): JsonResponse
    {
        $exist = Host::query()->find($id);

        if(!$exist)
        {
            return response()->json([
                'message' => 'Host id is not found , Invalid host id' ,
                'status_code' => 404
            ], 404);
        }

        $locations = Location::query()->where('host_id' , $id)->get();

        if(!$locations->count() > 0)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ] , 422);
        }

        $response = [];
        foreach ($locations as $location)
        {
            $response [] = [
                'id' => $location->id ,
                'name' => $location->name ,
            ];
        }
        return response()->json($response , 200);
    }
}
