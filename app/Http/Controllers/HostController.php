<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Host;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HostController extends Controller
{
    public function WebGetHosts(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $hosts = Host::query()->get();
        if(!$hosts->count() > 0)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ]);
        }

        $name = $hosts->pluck('name')->toArray();
        $name = TranslateTextHelper::batchTranslate($name);

        $recommend = $hosts->pluck('recommended_for')->toArray();
        $recommend = TranslateTextHelper::batchTranslate($recommend);

        $response = [];
        foreach ($hosts as $host)
        {
            $response [] = [
                'host_id' => $host->id ,
                'name' => $name[$host->name],
                'picture' => $host->picture ,
                'recommended_for' => $recommend[$host->recommended_for]
            ];
        }
        return response()->json($response , 200);
    }

    /////////////////////////////////////////////////////////////////////////////
    public function GetLocationRelatedHost ($id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

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

        $name = $locations->pluck('name')->toArray();
        $name = TranslateTextHelper::batchTranslate($name);

        $response = [];
        foreach ($locations as $location)
        {
            $response [] = [
                'location_id' => $location->id ,
                'name' => $name[$location->name] ,
            ];
        }
        return response()->json($response , 200);
    }
}
