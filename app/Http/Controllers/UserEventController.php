<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Helpers\TranslateTextHelper;
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
use App\Models\Receipt;
use App\Models\User;
use App\Models\UserEvent;
use App\Models\Warehouse;
use App\Models\WarehouseAccessory;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Switch_;

class UserEventController extends Controller
{
    public function createEvent(Request $request): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'invitation_type' => 'required|string',
            'description' => 'required|string',
            'start_time' => 'required|date_format:h:i A',
            'end_time' => 'required|date_format:h:i A',
            'num_people_invited' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        // Parse date and time using the specified format and convert to 24-hour format
        $eventDate = Carbon::parse($request->date);
        $startTime = Carbon::createFromFormat('Y-m-d h:i A', $request->date . ' ' . $request->start_time);
        $endTime = Carbon::createFromFormat('Y-m-d h:i A', $request->date . ' ' . $request->end_time);

        // Check that the event duration is at least one hour
        if ($endTime->diffInMinutes($startTime) < 60) {
            return response()->json([
                "error" => TranslateTextHelper::translate("The event duration must be at least one hour."),
                "status_code" => 422,
            ], 422);
        }

        // Check that the event starts at least three hours from now
        if ($startTime->diffInMinutes(Carbon::now()) < 180) {
            return response()->json([
                "error" => TranslateTextHelper::translate("The event start time must be at least three hours from now."),
                "status_code" => 422,
            ], 422);
        }

        // Retrieve the location's open and close times and convert to 24-hour format
        $location = Location::findOrFail($request->location_id);
        $locationOpenTime = Carbon::createFromFormat('h:i A', $location->open_time)->setDateFrom($eventDate);
        $locationCloseTime = Carbon::createFromFormat('h:i A', $location->close_time)->setDateFrom($eventDate);

        // Handle cases where closing time is past midnight
        if ($locationCloseTime->lt($locationOpenTime)) {
            $locationCloseTime->addDay();
        }

        // Check if the event is within the location's operating hours
        if ($startTime < $locationOpenTime || $endTime > $locationCloseTime) {
            return response()->json([
                "error" => TranslateTextHelper::translate("The event time is outside the location's operating hours."),
                "status_code" => 422,
            ], 422);
        }

        // Check for overlapping events
        $overlappingEvents = $this->checkForOverlappingEvents($request->location_id, $eventDate, $startTime, $endTime);
        if ($overlappingEvents) {
            return response()->json([
                "error" => TranslateTextHelper::translate("The selected time overlaps with an existing event."),
                "status_code" => 409,
            ], 409);
        }

        // Ensure the reservation starts at least one hour after the last event
        $latestEvent = $this->getLatestEvent($request->location_id, $eventDate, $startTime);
        if ($latestEvent && Carbon::parse($latestEvent->end_time)->diffInMinutes($startTime) < 60) {
            return response()->json([
                "error" => TranslateTextHelper::translate("The reservation must start at least one hour after the last reserved time."),
                "status_code" => 409,
            ], 409);
        }

        $isLocationHasSpaceForPeople = $this->checkLocationCapacity($location->id, $request->num_people_invited);
        if (!$isLocationHasSpaceForPeople) {
            return response()->json([
                "error" => TranslateTextHelper::translate("The number of invited people is bigger than location capacity, Please choose a different location."),
                "status_code" => 422,
            ], 422);
        }

        // Create Event
        $event = $this->createUserEvent($user->id, $request, $startTime, $endTime);

        // Create Event Supplement
        $eventSupplements = $this->createEventSupplement($event->id, $location->governorate);

        // Create a receipt for the event
        $this->createReceipt($user->id, $eventSupplements->id, $event->id);

        $admin = User::find($location->user_id);
        TranslateTextHelper::setTarget($admin->profile->preferred_language);
        event(new NotificationEvent($location->user_id, TranslateTextHelper::translate("Event reservation has been requested by user $user->id"), TranslateTextHelper::translate("New reservation")));

        TranslateTextHelper::setTarget($user->profile->preferred_language);
        // Return the response
        return response()->json([
            "message" => TranslateTextHelper::translate("Event reserved successfully"),
            "event_id" => $event->id,
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
    private function createEventSupplement($eventId, $governorate)
    {
        $event = UserEvent::find($eventId);

        $warehouse = Warehouse::whereGovernorate($governorate)->first();

        $location = Location::find($event->location_id);

        return EventSupplement::create([
            'user_event_id' => $eventId,
            'warehouse_id' => $warehouse->id,
            'total_price' => $location->reservation_price, // add the reservation price for start,other supplements later
        ]);
    }

    /////////////////////////////////////
    private function createReceipt($userId, $eventSupplementsId, $userEventId)
    {
        return Receipt::create([
            'user_id' => $userId,
            'event_supplement_id' => $eventSupplementsId,
            'user_event_id' => $userEventId,
        ]);
    }

    /////////////////////////////////////
    private function checkLocationCapacity($location_id, $people_invited): bool
    {
        $location = Location::find($location_id);
        if ($location->capacity < $people_invited) {
            return false;
        } else {
            return true;
        }
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getEventById($event_id): JsonResponse
    {
        $user = Auth::user();
        $event = UserEvent::find($event_id);
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if (!$event) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Event not found!"),
                "status_code" => 404
            ], 404);
        }
        if ($event->user_id != $user->id) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Event is no yours to show!"),
                "status_code" => 403
            ], 403);
        }
        return response()->json($event);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getUserEvents(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $events = UserEvent::whereUserId($user->id)
            ->select('id', 'date', 'start_time', 'end_time', 'location_id', 'verified') // Select only necessary fields
            ->get();

        if ($events->isEmpty()) {
            return response()->json([
                "error" => TranslateTextHelper::translate("You have not created any event yet!"),
                "status_code" => 404
            ], 404);
        }

        $eventsDetails = $events->map(function($event) {
            $location = Location::find($event->location_id, ['name', 'logo']); // Select only necessary fields from Location

            // Calculate the remaining time
            $startTime = Carbon::parse($event->date . ' ' . $event->start_time);
            $currentTime = Carbon::now();
            $diff = $startTime->diff($currentTime);

            return [
                'id' => $event->id,
                'date' => $event->date,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'status' => UserEvent::STATUS_KEYS[$event->verified],
                'location_name' => $location->name,
                'location_logo' => $location->logo,
                'remaining_days' => $diff->days,
                'remaining_hours' => $diff->h,
                'remaining_minutes' => $diff->i
            ];
        });

        return response()->json($eventsDetails);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function WebEventGraphicalStatistics(): JsonResponse
    {
        // Initialize an array to store the results
        $monthlyEventCounts = [];

        // Get current year
        $currentYear = Carbon::now()->year;

        // Loop through each month of the year
        for ($month = 1; $month <= 12; $month++) {
            // Calculate the start and end dates for the current month
            $startDate = Carbon::create($currentYear, $month, 1)->startOfMonth();
            $endDate = Carbon::create($currentYear, $month, 1)->endOfMonth();

            // Query to count events within the current month
            $eventCount = UserEvent::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

            // Format the month name (e.g., 'jan' for January)
            $monthName = $startDate->format('M');

            // Store the result in the array
            $monthlyEventCounts[] = [
                'month' => $monthName,
                'number_of_events' => $eventCount,
            ];
        }

        return response()->json($monthlyEventCounts, 200);
    }

    //////////////////////////////////////////////////////////////////////////////////////
    public function getUserEvent(Request $request): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $validator = Validator::make($request->all() , [
            'type' => 'required|in:1,2'
        ]);


        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        switch ($request->type)
        {
            case 1 :
                $reservations = UserEvent::query()->where('invitation_type' , 'Private')
                    ->whereUserId($user->id)
                    ->get();

                break;
            case 2 :
                $reservations = UserEvent::query()->where('invitation_type' , 'Public')
                    ->whereUserId($user->id)
                    ->get();

                break;
            default :
                return response()->json(['error' => 'Invalid type' , 'status_code'=> 400], 400);
        }


        $response = [];

        foreach ($reservations as $reservation) {

            // Calculate the remaining time
            $startTime = Carbon::parse($reservation->date . ' ' . $reservation->start_time);
            $currentTime = Carbon::now();
            $diff = $startTime->diff($currentTime);

            $response[] = [
                'id' => $reservation->id,
                'name' => $reservation->location->name,
                'date' => $reservation->date,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'verified' => UserEvent::STATUS_KEYS[$reservation->verified],
                'logo' => $reservation->location->logo ,
                'days' => $diff->days,
                'hours' => $diff->h,
                'minutes' => $diff->i
            ];
        }

        return response()->json($response, 200);
    }
    //////////////////////////////////////////////////////////////////////////////////////

}
