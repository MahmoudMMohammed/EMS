<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\MainEventHost;
use App\Models\MEHAC;
use App\Models\UserEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class MEHACController extends Controller
{
    public function getReservationAccessoriesCategory($event_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = UserEvent::query()->find($event_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "invalid event id",
                "status_code" => 422,
            ], 422);
        }

        $location_id = $exist->location_id ;

        $host = Location::query()
            ->where('id' , $location_id)
            ->pluck('host_id');

        if(!$host)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $categories_ids = MainEventHost::query()
            ->where('host_id' , $host)
            ->where('main_event_id' , $exist->main_event_id)
            ->pluck('id');

        if(!$categories_ids)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $results = MEHAC::query()->whereIn('main_event_host_id' , $categories_ids)->get();

        if(!$results)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $color = ['#443391' , '#3E5EAB' , '#1495CF' , '#60B246' , '#D1DC36' , '#F2EB3B' , '#F8BD19' , '#F89C21' , '#F25427' , '#F33128' , '#A71E4A' , '#7D3696' , '#443391' , '#3E5EAB' , '#1495CF' , '#60B246' , '#D1DC36' , '#F2EB3B' , '#F8BD19' , '#F89C21' , '#F25427' , '#F33128' , '#A71E4A' , '#7D3696'];
        $index = 0 ;

        $response = [];
        foreach ($results as $result)
        {
            $response [] = [
                'id' => $result->category->id ,
                'name' => $result->category->category ,
                'logo' => $result->category->logo ,
                'number' => $result->category->accessories->count(),
                'color' => $color[$index]
            ];
            $index++ ;
        }
        return response()->json($response ,200);
    }
}
