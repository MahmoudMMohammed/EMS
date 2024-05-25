<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\EventSupplement;
use App\Models\Location;
use App\Models\MainEvent;
use App\Models\Reservation;
use App\Models\UserEvent;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserEventController extends Controller
{
    public function createEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'invitation_type' => 'required|string',
            'description' => 'required|string',
            'start_time' => 'required|date_format:h:i A',
            'end_time' => 'required|date_format:h:i A|after:start_time',
            'num_people_invited' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        // Parse date and time using the specified format
        $eventDate = Carbon::parse($request->date);
        $startTime = Carbon::createFromFormat('Y-m-d h:i A', $request->date . ' ' . $request->start_time);
        $endTime = Carbon::createFromFormat('Y-m-d h:i A', $request->date . ' ' . $request->end_time);

        // Retrieve the location's open and close times
        $location = Location::find($request->location_id);
        $locationOpenTime = Carbon::createFromFormat('Y-m-d h:i A', $request->date . ' ' . $location->open_time);
        $locationCloseTime = Carbon::createFromFormat('Y-m-d h:i A', $request->date . ' ' . $location->close_time);

        // Check if the event is within the location's operating hours
        if ($startTime < $locationOpenTime || $endTime > $locationCloseTime) {
            return response()->json([
                "error" => "The event time is outside the location's operating hours.",
                "status_code" => 422,
            ], 422);
        }

        // Check for overlapping events
        $overlappingEvents = UserEvent::where('location_id', $request->location_id)
            ->whereDate('date', $eventDate)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();

        if ($overlappingEvents) {
            return response()->json([
                "error" => "The selected time overlaps with an existing event.",
                "status_code" => 409,
            ], 409);
        }
        // Ensure the reservation starts at least one hour after the last event
        $latestEvent = UserEvent::where('location_id', $request->location_id)
            ->whereDate('date', $eventDate)
            ->where('end_time', '<=', $startTime)
            ->orderBy('end_time', 'desc')
            ->first();

        if ($latestEvent && $latestEvent->end_time->diffInMinutes($startTime) < 60) {
            return response()->json([
                "error" => "The reservation must start at least one hour after the last reserved time.",
                "status_code" => 409,
            ], 409);
        }

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart || $cart->items()->count() == 0) {
            return response()->json([
                "error" => "Cart is empty",
                "status_code" => 400,
            ], 400);
        }

        // Categorize cart items
        $foodDetails = [];
        $drinksDetails = [];
        $accessoriesDetails = [];
        $totalPrice = 0;

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->itemable;
            $itemType = strtolower(class_basename($item));

            switch ($itemType) {
                case 'food':
                    $foodDetails[] = $item;
                    break;
                case 'drink':
                    $drinksDetails[] = $item;
                    break;
                case 'accessory':
                    $accessoriesDetails[] = $item;
                    break;
            }
            // Remove non-numeric characters except for dots and commas, then remove commas
            $priceString = $cartItem->itemable->price;
            $cleanedPrice = preg_replace('/[^0-9.,]/', '', $priceString);
            $cleanedPrice = str_replace(',', '', $cleanedPrice);
            $price = floatval($cleanedPrice);

            $totalPrice += $price * $cartItem->quantity;
        }

        // Create Event
        $event = UserEvent::create([
            'user_id' => $user->id,
            'location_id' => $request->location_id,
            'date' => $request->date,
            'invitation_type' => $request->invitation_type,
            'description' => $request->description,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'num_people_invited' => $request->num_people_invited,
        ]);

        $warehouse = Warehouse::whereGovernorate($location->governorate)->first();

        // Create Event Supplement
        $eventSupplement = EventSupplement::create([
            'user_event_id' => $event->id,
            'warehouse_id' => $warehouse->id,
            'food_details' => json_encode($foodDetails),
            'drinks_details' => json_encode($drinksDetails),
            'accessories_details' => json_encode($accessoriesDetails),
            'total_price' => $totalPrice,
        ]);

        //Create Reservation
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'user_event_id' => $event->id,
            'verified' => false
        ]);

        // Clear the user's cart
        $cart->items()->delete();

        return response()->json([
            "message" => "Event reserved successfully",
            "event" => $event,
            "event_supplement" => $eventSupplement,
            "reservation" => $reservation,
            "status_code" => 201
        ], 201);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getEventById($event_id)
    {
        $event = UserEvent::find($event_id);
        if (!$event){
            return response()->json([
                "error" => "Event not found!",
                "status_code" => 404
            ],404);
        }
        return response()->json($event,200);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getUserEvents()
    {
        $user = Auth::user();
        $events = UserEvent::whereUserId($user->id)->get();
        if ($events->count() == 0){
            return response()->json([
                "error" => "You have not created any event yet!",
                "status_code" => 404
            ],404);
        }
        return response()->json($events,200);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////

}
