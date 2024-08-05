<?php

namespace App\Http\Controllers;

use App\Models\UserEvent;
use App\Models\UserJoinedEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserJoinedEventController extends Controller
{
    public function joinEvent($event_id) : JsonResponse
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

        $can_not_join = $exist->num_people_invited == $exist->num_people_joined ;
        if($can_not_join)
        {
            return response()->json([
                "message" => "You cannot join this event because it has reached the maximum number of visitors.",
                "status_code" => 422,
            ], 422);
        }

        $have_joined_before = UserJoinedEvent::query()
            ->where('user_id' , $user->id)
            ->where('user_event_id' , $event_id)
            ->first();

        if($have_joined_before)
        {
            return response()->json([
                "message" => "You are already joining this event.",
                "status_code" => 422,
            ], 422);
        }


        //have_start_event_time
        $startTime = Carbon::parse($exist->date . ' ' . $exist->start_time);
        $currentTime = Carbon::now();

        if (!($currentTime->isAfter($startTime)) ){
            return response()->json([
                "message" => "You cannot join the event because it has already started.",
                "status_code" => 422,
            ], 422);
        }

        if($exist->verified != 1)
        {
            return response()->json([
                "message" => "You cannot join the event because it is status not confirmed.",
                "status_code" => 422,
            ], 422);
        }

        if($exist->invitation_type == 'Private')
        {
            return response()->json([
                "message" => "You cannot join the event because it is Private not Public.",
                "status_code" => 422,
            ], 422);
        }

        $new_join = UserJoinedEvent::query()->create([
            'user_id' => $user->id ,
            'user_event_id' => $event_id
        ]);

        //increase
        $exist->increment('num_people_joined');


        return response()->json([
            "message" => "You have successfully joined the event.",
            "status_code" => 200,
        ], 200);
    }
}
