<?php

namespace App\Http\Controllers;

use App\Models\EventSupplement;
use App\Models\UserEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventSupplementController extends Controller
{
    public function getSupplementsForEvent($event_id): JsonResponse
    {
        $user = Auth::user();
        $event = UserEvent::find($event_id);
        if (!$event){
            return response()->json([
                "error" => "Event not found!",
                "status_code" => 404
            ],404);
        }
        if ($event->user_id != $user->id ){
            return response()->json([
                "error" => "Event is not yours to show!",
                "status_code" => 403
            ],403);
        }
        $supplements = EventSupplement::whereUserEventId($event->id)->first();

        if (!$supplements){
            return response()->json([
                "error" => "You have not ordered any supplements for your event!",
                "status_code" => 404
            ],404);
        }

//        unset($supplements['id']);
//        unset($supplements['user_event_id']);
//        unset($supplements['warehouse_id']);

        return response()->json([
            "food_details" => json_decode($supplements->food_details),
            "drinks_details" => json_decode($supplements->drinks_details),
            "accessories_details" => json_decode($supplements->accessories_details),
        ],200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////

}
