<?php

namespace App\Http\Controllers;

use App\Models\EventSupplement;
use App\Models\UserEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventSupplementController extends Controller
{
    public function getSupplementsForEvent($event_id): JsonResponse
    {
        $event = UserEvent::find($event_id);
        $supplements = EventSupplement::whereUserEventId($event->id)->first();

        if (!$supplements){
            return response()->json([
                "error" => "You have not ordered any supplements for your event!",
                "status_code" => 404
            ],404);
        }

        unset($supplements['id']);
        unset($supplements['user_event_id']);
        unset($supplements['warehouse_id']);

        return response()->json($supplements,200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////

}
