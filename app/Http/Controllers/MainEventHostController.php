<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainEventHostController extends Controller
{
    public function GetHostsRelatedEvent($id): JsonResponse
    {
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
        foreach ($events as $event)
        {
            $event->picture = 'http://localhost:8000/' . $event->picture ;
        }
        return response()->json($events , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////


}
