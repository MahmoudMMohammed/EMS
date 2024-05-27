<?php

namespace App\Http\Controllers;

use App\Models\AccessoryCategory;
use App\Models\Cart;
use App\Models\DrinkCategory;
use App\Models\EventSupplement;
use App\Models\FoodCategory;
use App\Models\HostDrinkCategory;
use App\Models\HostFoodCategory;
use App\Models\Location;
use App\Models\MainEventHost;
use App\Models\MEHAC;
use App\Models\Reservation;
use App\Models\UserEvent;
use App\Models\Warehouse;
use App\Models\WarehouseAccessory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserEventController extends Controller
{
    public function createEvent(Request $request): JsonResponse
    {
        // Validate the request
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
        $location = Location::findOrFail($request->location_id);
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
        $overlappingEvents = $this->checkForOverlappingEvents($request->location_id, $eventDate, $startTime, $endTime);
        if ($overlappingEvents) {
            return response()->json([
                "error" => "The selected time overlaps with an existing event.",
                "status_code" => 409,
            ], 409);
        }

        // Ensure the reservation starts at least one hour after the last event
        $latestEvent = $this->getLatestEvent($request->location_id, $eventDate, $startTime);
        if ($latestEvent && $latestEvent->end_time->diffInMinutes($startTime) < 60) {
            return response()->json([
                "error" => "The reservation must start at least one hour after the last reserved time.",
                "status_code" => 409,
            ], 409);
        }

        // Check if the user's cart is empty
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart || $cart->items()->count() == 0) {
            return response()->json([
                "error" => "Cart is empty",
                "status_code" => 400,
            ], 400);
        }

        // Process cart items
        [$foodDetails, $drinksDetails, $accessoriesDetails, $declinedItems, $totalPrice] = $this->processCartItems($cart, $location);

        // Create Event
        $event = $this->createUserEvent($user->id, $request, $startTime, $endTime);

        // Create Event Supplement
        $eventSupplement = $this->createEventSupplement($event->id, $location->governorate, $foodDetails, $drinksDetails, $accessoriesDetails, $totalPrice);

        // Create Reservation
        $reservation = $this->createReservation($user->id, $event->id);

        // Return the response
        return response()->json([
            "message" => "Event reserved successfully",
            "event" => $event,
            "event_supplement" => $eventSupplement,
            "reservation" => $reservation,
            "declined_items" => $declinedItems,
            "status_code" => 201
        ], 201);
    }
    /////////////////////////////////////
    private function checkForOverlappingEvents($locationId, $eventDate, $startTime, $endTime): bool
    {
        return UserEvent::where('location_id', $locationId)
            ->whereDate('date', $eventDate)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();
    }
    /////////////////////////////////////
    private function getLatestEvent($locationId, $eventDate, $startTime)
    {
        return UserEvent::where('location_id', $locationId)
            ->whereDate('date', $eventDate)
            ->where('end_time', '<=', $startTime)
            ->orderBy('end_time', 'desc')
            ->first();
    }
    /////////////////////////////////////
    private function processCartItems($cart, $location)
    {
        $foodDetails = [];
        $drinksDetails = [];
        $accessoriesDetails = [];
        $declinedItems = [];
        $totalPrice = 0;

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->itemable;
            $itemType = strtolower(class_basename($item));
            $approved = false;

            switch ($itemType) {
                case 'food':
                    if (HostFoodCategory::where('food_category_id', $item->food_category_id)->where('host_id', $location->host->id)->exists()) {
                        $category = FoodCategory::find($item->food_category_id);
                        $item['category'] = $category->category;
                        unset($item['food_category_id']);
                        $foodDetails[] = $item;
                        $approved = true;
                    } else {
                        $declinedItems['food'][] = $item;
                    }
                    break;
                case 'drink':
                    if (HostDrinkCategory::where('drink_category_id', $item->drink_category_id)->where('host_id', $location->host->id)->exists()) {
                        $category = DrinkCategory::find($item->drink_category_id);
                        $item['category'] = $category->category;
                        $drinksDetails[] = $item;
                        $approved = true;
                    } else {
                        $declinedItems['drink'][] = $item;
                    }
                    break;
                case 'accessory':
                    $mainEventHost = MainEventHost::whereHostId($location->host->id)->pluck('id');
                    $warehouse = Warehouse::whereGovernorate($location->governorate)->first();
                    $availableQuantityInWarehouse = WarehouseAccessory::whereAccessoryId($item->id)
                        ->whereWarehouseId($warehouse->id)
                        ->pluck('quantity');

                    if (MEHAC::where('accessory_category_id', $item->accessory_category_id)->whereIn('main_event_host_id', $mainEventHost)->exists() &&
                        $item->quantity <= $availableQuantityInWarehouse) {
                        $category = AccessoryCategory::find($item->accessory_category_id);
                        $item['category'] = $category->category;
                        unset($item['accessory_category_id']);
                        $accessoriesDetails[] = $item;
                        $approved = true;
                    } else {
                        $declinedItems['accessory'][] = $item;
                    }
                    break;
            }

            if ($approved) {
                $price = $this->parsePrice($cartItem->itemable->price);
                $totalPrice += $price * $cartItem->quantity;
                $cartItem->delete();
            }
        }

        return [$foodDetails, $drinksDetails, $accessoriesDetails, $declinedItems, $totalPrice];
    }
    /////////////////////////////////////
    private function parsePrice($priceString): float
    {
        $cleanedPrice = preg_replace('/[^0-9.,]/', '', $priceString);
        $cleanedPrice = str_replace(',', '', $cleanedPrice);
        return floatval($cleanedPrice);
    }
    /////////////////////////////////////
    private function createUserEvent($userId, $request, $startTime, $endTime)
    {
        return UserEvent::create([
            'user_id' => $userId,
            'location_id' => $request->location_id,
            'date' => $request->date,
            'invitation_type' => $request->invitation_type,
            'description' => $request->description,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'num_people_invited' => $request->num_people_invited,
        ]);
    }
    /////////////////////////////////////
    private function createEventSupplement($eventId, $governorate, $foodDetails, $drinksDetails, $accessoriesDetails, $totalPrice)
    {
        $warehouse = Warehouse::whereGovernorate($governorate)->first();

        return EventSupplement::create([
            'user_event_id' => $eventId,
            'warehouse_id' => $warehouse->id,
            'food_details' => json_encode($foodDetails),
            'drinks_details' => json_encode($drinksDetails),
            'accessories_details' => json_encode($accessoriesDetails),
            'total_price' => $totalPrice,
        ]);
    }
    /////////////////////////////////////
    private function createReservation($userId, $eventId)
    {
        return Reservation::create([
            'user_id' => $userId,
            'user_event_id' => $eventId,
            'verified' => false,
        ]);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getEventById($event_id): JsonResponse
    {
        $user = Auth::user();
        $event = UserEvent::find($event_id);
        if (!$event){
            return response()->json([
                "error" => "Event not found!",
                "status_code" => 404
            ],404);
        }
        if ($event->user_id != $user->id){
            return response()->json([
                "error" => "Event is no yours to show!",
                "status_code" => 403
            ],403);
        }
        return response()->json($event);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getUserEvents(): JsonResponse
    {
        $user = Auth::user();
        $events = UserEvent::whereUserId($user->id)->get();
        if ($events->count() == 0){
            return response()->json([
                "error" => "You have not created any event yet!",
                "status_code" => 404
            ],404);
        }
        return response()->json($events);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////

}
