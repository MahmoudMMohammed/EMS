<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MainEventHostController extends Controller
{
    public function GetHostsRelatedEvent($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $events = DB::table('main_event_hosts')
            ->join('hosts', 'main_event_hosts.host_id', '=', 'hosts.id')
            ->join('main_events', 'main_event_hosts.main_event_id', '=', 'main_events.id')
            ->where('main_event_id' , $id)
            ->select('hosts.id as host_id' , 'main_events.name as event_name' , 'hosts.name as host_name' , 'hosts.picture' , 'hosts.recommended_for')
            ->get();

        if(!$events->count() > 0)
        {
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
                ] , 400);
        }


        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $host = $events->pluck('host_name')->toArray();
        $recommend = $events->pluck('recommended_for')->toArray();

        $host_translate = TranslateTextHelper::batchTranslate($host);
        $recommend_translate = TranslateTextHelper::batchTranslate($recommend);

        $response = [] ;
        foreach ($events as $event)
        {
            $response [] = [
                'host_id' => $event -> host_id ,
                'event_name' => $event -> event_name ,
                'host_name' => $host_translate[$event->host_name] ,
                'picture' => env('APP_URL') . '/' . $event->picture ,
                'recommended_for' => $recommend_translate[$event->recommended_for]
            ];
        }

        sleep(1);
        return response()->json($response , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////


}
