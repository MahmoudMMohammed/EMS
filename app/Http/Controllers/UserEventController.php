<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Helpers\TranslateTextHelper;
use App\Models\EventSupplement;
use App\Models\Host;
use App\Models\Location;
use App\Models\LocationPicture;
use App\Models\Receipt;
use App\Models\User;
use App\Models\UserEvent;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserEventController extends Controller
{
    public function createEvent(Request $request): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'main_event_id' => 'required|exists:main_events,id',
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

        // Check that the event duration is at least one hour
        if ($eventDate->isBefore(now())) {
            return response()->json([
                "error" => TranslateTextHelper::translate("You cannot create an event in the past!"),
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
                "error" => TranslateTextHelper::translate("The number of invited people is bigger than location capacity, Max capacity for this location is $location->capacity, Please choose a different location."),
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
            'main_event_id' => $request->main_event_id,
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

        $startTime = Carbon::parse($event->date . ' ' . $event->start_time);
        $endTime = Carbon::parse($event->date . ' ' . $event->end_time);

        $eventTimeInMinutes = $startTime->diffInMinutes($endTime);

        return EventSupplement::create([
            'user_event_id' => $eventId,
            'warehouse_id' => $warehouse->id,
            'total_price' => $location->reservation_price * ( $eventTimeInMinutes / 60), // add the reservation price for start,other supplements later
        ]);
    }

    /////////////////////////////////////
    private function createReceipt($userId, $eventSupplementsId, $userEventId): void
    {
        Receipt::create([
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
    public function getEventDetails($event_id): JsonResponse
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

        $response = [
            'id' => $event->id ,
            'invitation_type' => $event->invitation_type ,
            'description' => $event->description ,
            'num_people_invited' => $event->num_people_invited ,
            'num_people_joined' => $event->num_people_joined.'/'.$event->num_people_invited
        ];

        return response()->json($response);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function updateEventDetails(Request $request)
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer|exists:user_events,id',
            'invitation_type' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'num_people_invited' => 'sometimes|nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        // Retrieve the validated data
        $validatedData = $validator->validated();

        // Find the event
        $event = UserEvent::find($validatedData['event_id']);

        $validationResponse = $this->validateEvent($event, $user);
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        // Filter out null values to prevent overwriting with null
        $filteredData = array_filter($validatedData, function($value) {
            return !is_null($value);
        });

        // Update the event with only the provided data
        $event->update($filteredData);

        return response()->json([
            "message" => TranslateTextHelper::translate("Event updated successfully!"),
            "status_code" => 200,
        ], 200);
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

    public function deleteEvent($event_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $event = UserEvent::find($event_id);
        $validationResponse = $this->validateEvent($event, $user);
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $startTime = Carbon::parse($event->date . ' ' . $event->start_time);
        $currentTime = Carbon::now();

        if ($currentTime->diffInHours($startTime) <= 24) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Event cannot be deleted within 24 hours of the start time!"),
                "status_code" => 400
            ], 400);
        }

        // Proceed to delete the event if validation passes
        $event->delete();

        return response()->json([
            "message" => TranslateTextHelper::translate("Event deleted successfully"),
            "status_code" => 200
        ], 200);

    }
    //////////////////////////////////////////////////////////////////////////////////////

    private function validateEvent($event, $user): ?JsonResponse
    {
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

        return null; // Indicating validation passed
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

        $now = Carbon::now();
        switch ($request->type)
        {
            case 1 :
                $reservations = UserEvent::query()
                    ->where('user_id' , $user->id)
                    ->orderBy('date' , 'desc')
                    ->get();

                if($reservations->isEmpty())
                {
                    return response()->json([
                        'massage' => "Dear user,you do not have any reservations of your own yet, create some." ,
                        'status_code' => 404
                    ] , 404);
                }

                break;
            case 2 :
                $reservations = UserEvent::query()
                    ->where('invitation_type' , 'Public')
                    ->where( 'verified' , 'Confirmed')
                    ->where('user_id' , '!=' , $user->id)
                    ->orderBy('date' , 'desc')
                    ->get();

                if($reservations->isEmpty())
                {
                    return response()->json([
                        'massage' => "Dear user,there are no public events to join." ,
                        'status_code' => 404
                    ] , 404);
                }

                break;
            default :
                return response()->json(['error' => 'Invalid type' , 'status_code'=> 400], 400);
        }


        $response = [];

        //Pending          //Confirmed      //Rejected       //Finished
        $icon = ['Status/4.png' , 'Status/3.png' , 'Status/2.png' , 'Status/1.png'];

        $color = ['#F1910B' , '#60B246' , '#DD6A6A' , '#777777'];

        $app_url = env('app_url');

        foreach ($reservations as $reservation) {

            $checkTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
            if($checkTime < $now && $reservation->verified == 1)
            {
                $reservation->verified = UserEvent::STATUS_KEYS[3];
                $reservation->save();
            }

            // Calculate the remaining time
            $startTime = Carbon::parse($reservation->date . ' ' . $reservation->start_time);
            $currentTime = Carbon::now();
            $diff = $startTime->diff($currentTime);

            $remaining_days = sprintf('%03d', $diff->days);
            $remaining_hours = sprintf('%02d', $diff->h);
            $remaining_minutes = sprintf('%02d', $diff->i);

            if ($currentTime->isAfter($startTime) || $reservation->verified == 2){
                $remaining_days = sprintf('%03d', 000);
                $remaining_hours = sprintf('%02d', 00);
                $remaining_minutes = sprintf('%02d', 00);
            }

            $i = '';
            $c = '';

            switch($reservation->verified)
            {
                case 0 :
                    $i = $app_url . '/' . $icon[0] ;
                    $c = $color[0] ;
                    break ;

                case 1 :
                    $i = $app_url . '/' . $icon[1] ;
                    $c = $color[1] ;
                    break ;

                case 2 :
                    $i = $app_url . '/' . $icon[2] ;
                    $c = $color[2] ;
                    break ;

                case 3 :
                    $i = $app_url . '/' . $icon[3] ;
                    $c = $color[3] ;
                    break ;
            }

            $response[] = [
                'id' => $reservation->id,
                'name' => $reservation->location->name,
                'date' => $reservation->date,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'event' => $reservation->event->name ,
                'verified' => UserEvent::STATUS_KEYS[$reservation->verified],
                'logo' => $reservation->location->logo ,
                'days' => $remaining_days,
                'time' => $remaining_hours  . ':' . $remaining_minutes,
                'icon' => $i ,
                'color' => $c
            ];
        }

        return response()->json($response, 200);
    }
    //////////////////////////////////////////////////////////////////////////
    public function getUserPrivateEventDetails($event_id): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $event = UserEvent::query()->find($event_id);
        if(!$event)
        {
            return response()->json([
                "error" => "invalid event id",
                "status_code" => 422,
            ], 422);
        }

        $event = UserEvent::query()->where('id' , $event_id)->first();
        if(!$event)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $startTime = Carbon::parse($event->date . ' ' . $event->start_time)->subHours(24);
        $currentTime = Carbon::now();
        $diff = $startTime->diff($currentTime);

        $remaining_days = sprintf('%03d', $diff->days);
        $remaining_hours = sprintf('%02d', $diff->h);
        $remaining_minutes = sprintf('%02d', $diff->i);

        if ($currentTime->isAfter($startTime) || $event->verified == 2) {
            $remaining_days = sprintf('%03d', 0);
            $remaining_hours = sprintf('%02d', 0);
            $remaining_minutes = sprintf('%02d', 0);
        }

        $pictures = LocationPicture::query()
            ->where('location_id', $event->location_id)
            ->pluck('picture')
            ->toArray();

        if (!$pictures) {
            return response()->json([
                "error" => "Something went wrong, try again later",
                "status_code" => 422,
            ], 422);
        }

        $status = !($remaining_days == '000' && $remaining_hours == '00' && $remaining_minutes == '00');

        $response = [
            'id' => $event->id,
            'date' => $event->date,
            'verified' => UserEvent::STATUS_KEYS[$event->verified],
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
            'picture_1' => $pictures[0] ,
            'picture_2' => $pictures[1] ,
            'picture_3' => $pictures[2] ,
            'days' => $remaining_days,
            'time' => $remaining_hours . ':' . $remaining_minutes,
            'status' => $status
        ];

        return response()->json($response, 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function getBill($event_id): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = UserEvent::query()->find($event_id)->first();
        if(!$exist)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $foods = EventSupplement::query()
            ->where('user_event_id' , $exist->id)
            ->pluck('food_details');

        $foodTotalPrice = 0;
        foreach ($foods as $foodDetails) {
            foreach ($foodDetails as $food) {

                $price = (float)str_replace(['S.P', ',', ' '], '', $food['price']);
                $quantity = $food['quantity'];
                $foodTotalPrice += $price * $quantity;
            }
        }

        $drinks = EventSupplement::query()
            ->where('user_event_id' , $exist->id)
            ->pluck('drinks_details');

        $drinksTotalPrice = 0;
        foreach ($drinks as $drinksDetails) {
            foreach ($drinksDetails as $drink) {

                $price = (float)str_replace(['S.P', ',', ' '], '', $drink['price']);
                $quantity = $drink['quantity'];
                $drinksTotalPrice += $price * $quantity;
            }
        }

        $accessories = EventSupplement::query()
            ->where('user_event_id' , $exist->id)
            ->pluck('accessories_details');

        $accessoriesTotalPrice = 0;
        foreach ($accessories as $accessoriesDetails) {
            foreach ($accessoriesDetails as $accessory) {

                $price = (float)str_replace(['S.P', ',', ' '], '', $accessory['price']);
                $quantity = $accessory['quantity'];
                $accessoriesTotalPrice += $price * $quantity;
            }
        }

        $reservation = UserEvent::query()->where('id' , $event_id)->pluck('location_id');
        $reservation_price = Location::query()->where('id' , $reservation)->first();
        $reservation_price = $reservation_price->reservation_price ;
        if(!$reservation_price)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);        }


        $start_time = Carbon::parse($exist->date . ' ' . $exist->start_time);
        $end_time = Carbon::parse($exist->date . ' ' . $exist->end_time);
        $event_time_minutes = $start_time->diffInMinutes($end_time);
        $reservation_price = $reservation_price * ($event_time_minutes / 60);

        $total_price = EventSupplement::query()
            ->where('user_event_id' , $exist->id)
            ->first();

        $tax = ($foodTotalPrice + $drinksTotalPrice + $accessoriesTotalPrice + $reservation_price) * 0.05 ;
        $total_price = (float)str_replace(['S.P', ',', ' '], '',$total_price->total_price);

        $response = [
            'Food' => number_format($foodTotalPrice , 2 ).' S.P' ,
            'Drinks' => number_format($drinksTotalPrice , 2 ).' S.P' ,
            'accessories' => number_format($accessoriesTotalPrice , 2 ).' S.P' ,
            'Reservation price' => number_format($reservation_price , 2 ).' S.P' ,
            'Reconstruction tax' =>number_format($tax , 2 ).' S.P' ,
            'total_price' => number_format($total_price+$tax , 2).' S.P'];

        return response()->json($response ,200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function getReservationByDate(Request $request): JsonResponse
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
            //1:all , 2:today , 3:week , 4:month , 5:year
            'type'=>'required|in:1,2,3,4,5'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'message' => $validator->errors()->first() ,
                'status_code'=>422
            ] , 422);
        }

        $response = [];
        $now = Carbon::now();
        $today = $now->toDateString();

        switch ($request->type)
        {
            case 1 :
                //all
                $reservations = UserEvent::query()->orderBy('date' , 'desc')->get();

                if ($reservations->isEmpty()) {
                    return response()->json([
                        "message" => "There are no reservations registered yet.",
                        "status_code" => 404,
                    ], 404);
                }
                break ;

            case 2 ;
                //today
                $reservations = UserEvent::query()
                    ->whereDate('date', $today)
                    ->orderBy('date' , 'desc')
                    ->get();

                if ($reservations->isEmpty()) {
                    return response()->json([
                        "message" => "There are no reservations registered today.",
                        "status_code" => 404,
                    ], 404);
                }
                break;

            case 3 ;
                //week
                $startOfWeek = $now->startOfWeek()->toDateString();
                $endOfWeek = $now->endOfWeek()->toDateString();
                $reservations = UserEvent::query()->whereBetween('date', [$startOfWeek, $endOfWeek])
                    ->orderBy('date' , 'desc')
                    ->get();

                if ($reservations->isEmpty()) {
                    return response()->json([
                        "message" => "There are no reservations registered this week.",
                        "status_code" => 404,
                    ], 404);
                }
                break;

            case 4 ;
                //month
                $startOfMonth = $now->startOfMonth()->toDateString();
                $endOfMonth = $now->endOfMonth()->toDateString();

                $reservations = UserEvent::query()->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->orderBy('date' , 'desc')
                    ->get();

                if ($reservations->isEmpty()) {
                    return response()->json([
                        "message" => "There are no reservations registered this month.",
                        "status_code" => 404,
                    ], 404);
                }
                break;

            case 5 ;
                //year
                $startOfYear = Carbon::createFromDate($now->year, 1, 1)->toDateString();
                $endOfYear = Carbon::createFromDate($now->year, 12, 31)->toDateString();

                $reservations = UserEvent::query()->whereBetween('date', [$startOfYear, $endOfYear])
                    ->orderBy('date' , 'desc')
                    ->get();

                if ($reservations->isEmpty()) {
                    return response()->json([
                        "message" => "There are no reservations registered this year.",
                        "status_code" => 404,
                    ], 404);
                }
                break;

            default:
                return response()->json([ "message" => "Invalid type provided", "status_code" => 422,], 422);
        }

        $icon = ['Status/4.png' , 'Status/3.png' , 'Status/2.png' , 'Status/1.png'];
        $color = ['#F1910B' , '#60B246' , '#DD6A6A' , '#777777'];
        $i = '';
        $c = '';

        $app_url = env('APP_URL');

        foreach ($reservations as $reservation) {

            $checkTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
            if($checkTime < $now && $reservation->verified == 1)
            {
                $reservation->verified = UserEvent::STATUS_KEYS[3];
                $reservation->save();
            }

            switch($reservation->verified)
            {
                case 0 :
                    $i = $app_url . '/' . $icon[0] ;
                    $c = $color[0] ;
                    break ;

                case 1 :
                    $i = $app_url . '/' . $icon[1] ;
                    $c = $color[1] ;
                    break ;

                case 2 :
                    $i = $app_url . '/' . $icon[2] ;
                    $c = $color[2] ;
                    break ;

                case 3 :
                    $i = $app_url . '/' . $icon[3] ;
                    $c = $color[3] ;
                    break ;
            }

            $response[] = [
                'id' => $reservation->id,
                'name' => $reservation->location->name,
                'date' => $reservation->date,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'verified' => UserEvent::STATUS_KEYS[$reservation->verified],
                'num_people_invited' => $reservation->num_people_invited,
                'logo' => $reservation->location->logo,
                'icon' => $i ,
                'color' => $c
            ];
        }

        return response()->json($response , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function getReservationByHost(Request $request): JsonResponse
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
            //1:all , 2:restaurant , 3:cafe , 4:Bar , 5:Lounge , 6:Park , 7:Theater , 8:Stadium
            'type'=>'required|in:1,2,3,4,5,6,7,8'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()->first(),
                'status_code' => 422
            ] , 422);
        }

        $response = [];
        $hosts = [];

        $icon = ['Status/4.png' , 'Status/3.png' , 'Status/2.png' , 'Status/1.png'];
        $color = ['#F1910B' , '#60B246' , '#DD6A6A' , '#777777'];
        $i = '';
        $c = '';
        $app_url = env('APP_URL');

        $now = Carbon::now();

        switch ($request->type)
        {
            case 1 :
                //all
                $reservations = UserEvent::query()->orderBy('date' , 'desc')->get();

                if($reservations->isEmpty())
                {
                    return response()->json([
                        "message" => "There are no reservations registered yet.",
                        "status_code" => 404,
                    ], 404);
                }
                foreach ($reservations as $reservation)
                {
                    $checkTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
                    if($checkTime < $now && $reservation->verified == 1)
                    {
                        $reservation->verified = UserEvent::STATUS_KEYS[3];
                        $reservation->save();
                    }

                    switch($reservation->verified)
                    {
                        case 0 :
                            $i = $app_url . '/' . $icon[0] ;
                            $c = $color[0] ;
                            break ;

                        case 1 :
                            $i = $app_url . '/' . $icon[1] ;
                            $c = $color[1] ;
                            break ;

                        case 2 :
                            $i = $app_url . '/' . $icon[2] ;
                            $c = $color[2] ;
                            break ;

                        case 3 :
                            $i = $app_url . '/' . $icon[3] ;
                            $c = $color[3] ;
                            break ;
                    }

                    $response[] = [
                        'id' => $reservation->id,
                        'name' => $reservation->location->name,
                        'date' => $reservation->date,
                        'start_time' => $reservation->start_time,
                        'end_time' => $reservation->end_time,
                        'verified' => UserEvent::STATUS_KEYS[$reservation->verified],
                        'num_people_invited' => $reservation->num_people_invited,
                        'logo' => $reservation->location->logo,
                        'icon' => $i ,
                        'color' => $c
                    ];
                }
                return response()->json($response , 200);

            case 2 :
                //restaurant
                $hosts = Host::query()->where('name' , 'Restaurant')->pluck('id');

                if(!$hosts)
                {
                    return response()->json([
                        "error" => "Something went wrong , try again later",
                        "status_code" => 422,
                    ], 422);
                }
                break ;

            case 3 ;
                //cafe
                $hosts = Host::query()->where('name' , 'Cafe')->pluck('id');

                if(!$hosts)
                {
                    return response()->json([
                        "error" => "Something went wrong , try again later",
                        "status_code" => 422,
                    ], 422);
                }
                break ;

            case 4 ;
                //Bar
                $hosts = Host::query()->where('name' , 'Bar')->pluck('id');

                if(!$hosts)
                {
                    return response()->json([
                        "error" => "Something went wrong , try again later",
                        "status_code" => 422,
                    ], 422);
                }
                break ;

            case 5 ;
                //Lounge
                $hosts = Host::query()->where('name' , 'Lounge')->pluck('id');
                if(!$hosts)
                {
                    return response()->json([
                        "error" => "Something went wrong , try again later",
                        "status_code" => 422,
                    ], 422);
                }
                break ;

            case 6 :
                //Park
                $hosts = Host::query()->where('name' , 'Park')->pluck('id');
                if(!$hosts)
                {
                    return response()->json([
                        "error" => "Something went wrong , try again later",
                        "status_code" => 422,
                    ], 422);
                }
                break ;

            case 7 :
                //Theater
                $hosts = Host::query()->where('name' , 'Theater')->pluck('id');
                if(!$hosts)
                {
                    return response()->json([
                        "error" => "Something went wrong , try again later",
                        "status_code" => 422,
                    ], 422);
                }
                break ;

            case 8 :
                //Stadium
                $hosts = Host::query()->where('name' , 'Stadium')->pluck('id');
                if(!$hosts)
                {
                    return response()->json([
                        "error" => "Something went wrong , try again later",
                        "status_code" => 422,
                    ], 422);
                }
                break ;

            default :
                return response()->json([ "message" => "Invalid type provided", "status_code" => 422,], 422);

        }

        $locations = Location::query()->where('host_id' , $hosts)->pluck('id');
        if(!$locations)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $reservations = UserEvent::query()
            ->whereIn('location_id' , $locations)
            ->orderBy('date' , 'desc')
            ->get();

        if($reservations->isEmpty())
        {
            return response()->json([
                "message" => "There are no reservations registered for the selected host.",
                "status_code" => 404,
            ], 404);
        }


        foreach ($reservations as $reservation)
        {
            $checkTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
            if($checkTime < $now && $reservation->verified == 1)
            {
                $reservation->verified = UserEvent::STATUS_KEYS[3];
                $reservation->save();
            }

            switch($reservation->verified)
            {
                case 0 :
                    $i = $app_url . '/' . $icon[0] ;
                    $c = $color[0] ;
                    break ;

                case 1 :
                    $i = $app_url . '/' . $icon[1] ;
                    $c = $color[1] ;
                    break ;

                case 2 :
                    $i = $app_url . '/' . $icon[2] ;
                    $c = $color[2] ;
                    break ;

                case 3 :
                    $i = $app_url . '/' . $icon[3] ;
                    $c = $color[3] ;
                    break ;
            }

            $response[] = [
                'id' => $reservation->id,
                'name' => $reservation->location->name,
                'date' => $reservation->date,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'verified' => UserEvent::STATUS_KEYS[$reservation->verified],
                'num_people_invited' => $reservation->num_people_invited,
                'logo' => $reservation->location->logo,
                'icon' => $i ,
                'color' => $c
            ];
        }
        return response()->json($response , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function getReservationByGovernorate(Request $request) : JsonResponse
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
            //1:all , 2:Damascus , 3:Homs , 4:Tartus , 5:Aleppo , 6:Suwayda , 7:Daraa , 8:Raqqa
            'type'=>'required|in:1,2,3,4,5,6,7,8'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()->first(),
                'status_code' => 422
            ] , 422);
        }

        $response = [];
        $locations = [];

        $icon = ['Status/4.png' , 'Status/3.png' , 'Status/2.png' , 'Status/1.png'];
        $color = ['#F1910B' , '#60B246' , '#DD6A6A' , '#777777'];
        $i = '';
        $c = '';

        $app_url = env('APP_URL');
        $now = Carbon::now();

        switch($request->type)
        {
            case 1 :
                //all
                $reservations = UserEvent::query()->orderBy('date' , 'desc')->get();
                if($reservations->isEmpty())
                {
                    return response()->json([
                        "message" => "There are no reservations registered yet.",
                        "status_code" => 404,
                    ], 404);
                }

                foreach ($reservations as $reservation)
                {
                    $checkTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
                    if($checkTime < $now && $reservation->verified == 1)
                    {
                        $reservation->verified = UserEvent::STATUS_KEYS[3];
                        $reservation->save();
                    }

                    switch($reservation->verified)
                    {
                        case 0 :
                            $i = $app_url . '/' . $icon[0] ;
                            $c = $color[0] ;
                            break ;

                        case 1 :
                            $i = $app_url . '/' . $icon[1] ;
                            $c = $color[1] ;
                            break ;

                        case 2 :
                            $i = $app_url . '/' . $icon[2] ;
                            $c = $color[2] ;
                            break ;

                        case 3 :
                            $i = $app_url . '/' . $icon[3] ;
                            $c = $color[3] ;
                            break ;
                    }

                    $response[] = [
                        'id' => $reservation->id,
                        'name' => $reservation->location->name,
                        'date' => $reservation->date,
                        'start_time' => $reservation->start_time,
                        'end_time' => $reservation->end_time,
                        'verified' => UserEvent::STATUS_KEYS[$reservation->verified],
                        'num_people_invited' => $reservation->num_people_invited,
                        'logo' => $reservation->location->logo,
                        'icon' => $i ,
                        'color' => $c
                    ];
                }

                return response()->json($response , 200);

            case 2 :
                //Damascus
                $locations = Location::query()->where('governorate' , 'Damascus')->pluck('id');
                break ;

            case 3 :
                //Homs
                $locations = Location::query()->where('governorate' , 'Homs')->pluck('id');
                break ;

            case 4 :
                //Tartus
                $locations = Location::query()->where('governorate' , 'Tartus')->pluck('id');
                break ;

            case 5 :
                //Aleppo
                $locations = Location::query()->where('governorate' , 'Aleppo')->pluck('id');
                break ;

            case 6 :
                //Suwayda
                $locations = Location::query()->where('governorate' , 'Suwayda')->pluck('id');
                break ;

            case 7 :
                //Daraa
                $locations = Location::query()->where('governorate' , 'Daraa')->pluck('id');
                break ;

            case 8 :
                //Daraa
                $locations = Location::query()->where('governorate' , 'Raqqa')->pluck('id');
                break ;

            default :
                return response()->json([ "message" => "Invalid type provided", "status_code" => 422,], 422);
        }

        $reservations = UserEvent::query()
            ->whereIn('location_id' , $locations)
            ->orderBy('date' , 'desc')
            ->get();

        if($reservations->isEmpty())
        {
            return response()->json([
                "message" => "There are no reservations registered for the selected governorate.",
                "status_code" => 404,
            ], 404);
        }

        foreach ($reservations as $reservation)
        {
            $checkTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
            if($checkTime < $now && $reservation->verified == 1)
            {
                $reservation->verified = UserEvent::STATUS_KEYS[3];
                $reservation->save();
            }

            switch($reservation->verified)
            {
                case 0 :
                    $i = $app_url . '/' . $icon[0] ;
                    $c = $color[0] ;
                    break ;

                case 1 :
                    $i = $app_url . '/' . $icon[1] ;
                    $c = $color[1] ;
                    break ;

                case 2 :
                    $i = $app_url . '/' . $icon[2] ;
                    $c = $color[2] ;
                    break ;

                case 3 :
                    $i = $app_url . '/' . $icon[3] ;
                    $c = $color[3] ;
                    break ;
            }

            $response[] = [
                'id' => $reservation->id,
                'name' => $reservation->location->name,
                'date' => $reservation->date,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'verified' => UserEvent::STATUS_KEYS[$reservation->verified],
                'num_people_invited' => $reservation->num_people_invited,
                'logo' => $reservation->location->logo,
                'icon' => $i ,
                'color' => $c
            ];
        }

        return response()->json($response , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function getReservationByState(Request $request) : JsonResponse
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
            //1:all , 2:Pending , 3:Confirmed , 4:Rejected , 5:Finished
            'type' => 'required|in:1,2,3,4,5'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()->first(),
                'status_code' => 422
            ] , 422);
        }

        $response = [];

        $app_url = env('APP_URL');
        $now = Carbon::now();

        switch ($request->type)
        {
            case 1 :
                //all
                $reservations = UserEvent::query()->orderBy('date' , 'desc')->get();
                if($reservations->isEmpty())
                {
                    return response()->json([
                        "message" => "There are no reservations registered yet.",
                        "status_code" => 404,
                    ], 404);
                }
                break ;

            case 2 :
                //Pending
                $reservations = UserEvent::query()
                    ->where('verified' , 'Pending')
                    ->orderBy('date' , 'desc')
                    ->get();
                if($reservations->isEmpty())
                {
                    return response()->json([
                        "message" => "There are no pending reservations.",
                        "status_code" => 404,
                    ], 404);
                }
                break ;

            case 3 :
                //Confirmed
                $reservations = UserEvent::all();
                foreach ($reservations as $reservation)
                {
                    $checkTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
                    if($checkTime < $now && $reservation->verified == 1)
                    {
                        $reservation->verified = UserEvent::STATUS_KEYS[3];
                        $reservation->save();
                    }
                }

                $reservations = UserEvent::query()
                    ->where('verified' , 'Confirmed')
                    ->orderBy('date' , 'desc')
                    ->get();
                if($reservations->isEmpty())
                {
                    return response()->json([
                        "message" => "There are no confirmed reservations.",
                        "status_code" => 404,
                    ], 404);
                }
                break ;

            case 4 :
                //Rejected
                $reservations = UserEvent::query()
                    ->where('verified' , 'Rejected')
                    ->orderBy('date' , 'desc')
                    ->get();
                if($reservations->isEmpty())
                {
                    return response()->json([
                        "message" => "There are no rejected reservations.",
                        "status_code" => 404,
                    ], 404);
                }
                break ;

            case 5 :
                //Finished
                $reservations = UserEvent::query()
                    ->where('verified' , 'Finished')
                    ->orderBy('date' , 'desc')
                    ->get();
                if($reservations->isEmpty())
                {
                    return response()->json([
                        "message" => "There are no finished reservations.",
                        "status_code" => 404,
                    ], 404);
                }
                break ;

            default :
                return response()->json([ "message" => "Invalid type provided", "status_code" => 422,], 422);
        }

        $icon = ['Status/4.png' , 'Status/3.png' , 'Status/2.png' , 'Status/1.png'];
        $color = ['#F1910B' , '#60B246' , '#DD6A6A' , '#777777'];
        $i = '';
        $c = '';

        foreach ($reservations as $reservation)
        {
            $checkTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
            if($checkTime < $now && $reservation->verified == 1)
            {
                $reservation->verified = UserEvent::STATUS_KEYS[3];
                $reservation->save();
            }

            switch($reservation->verified)
            {
                case 0 :
                    $i = $app_url . '/' . $icon[0] ;
                    $c = $color[0] ;
                    break ;

                case 1 :
                    $i = $app_url . '/' . $icon[1] ;
                    $c = $color[1] ;
                    break ;

                case 2 :
                    $i = $app_url . '/' . $icon[2] ;
                    $c = $color[2] ;
                    break ;

                case 3 :
                    $i = $app_url . '/' . $icon[3] ;
                    $c = $color[3] ;
                    break ;
            }

            $response[] = [
                'id' => $reservation->id,
                'name' => $reservation->location->name,
                'date' => $reservation->date,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'verified' => UserEvent::STATUS_KEYS[$reservation->verified],
                'num_people_invited' => $reservation->num_people_invited,
                'logo' => $reservation->location->logo,
                'icon' => $i ,
                'color' => $c
            ];
        }
        return response()->json($response , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function getMineReservation(): JsonResponse
    {
        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $role = User::query()->where('id' , $user->id)->first();

        $response = [];
        switch($role->role)
        {
            case 'Admin' :
                $location = Location::query()->where('user_id' , $user->id)->pluck('id');

                $reservations = UserEvent::query()
                    ->whereIn('location_id' , $location)
                    ->orderBy('date' , 'desc')
                    ->get();

                if($reservations->isEmpty())
                {
                    return response()->json([
                        "message" => "There are no reservations registered in your location yet.",
                        "status_code" => 404,
                    ], 404);
                }
                break ;

            case 'Owner' :
                $reservations = UserEvent::query()
                    ->orderBy('date' , 'desc')
                    ->get();

                if($reservations->isEmpty())
                {
                    return response()->json([
                        "message" => "There are no reservations registered in your application yet.",
                        "status_code" => 404,
                    ], 404);
                }
                break ;

            default :
                return response()->json([ "message" => "Invalid type provided", "status_code" => 422,], 422);
        }
        $icon = ['Status/4.png' , 'Status/3.png' , 'Status/2.png' , 'Status/1.png'];
        $color = ['#F1910B' , '#60B246' , '#DD6A6A' , '#777777'];
        $i = '';
        $c = '';

        $app_url = env('APP_URL');
        $now = Carbon::now();

        foreach ($reservations as $reservation)
        {
            $checkTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
            if($checkTime < $now && $reservation->verified == 1)
            {
                $reservation->verified = UserEvent::STATUS_KEYS[3];
                $reservation->save();
            }

            switch($reservation->verified)
            {
                case 0 :
                    $i = $app_url . '/' . $icon[0] ;
                    $c = $color[0] ;
                    break ;

                case 1 :
                    $i = $app_url . '/' . $icon[1] ;
                    $c = $color[1] ;
                    break ;

                case 2 :
                    $i = $app_url . '/' . $icon[2] ;
                    $c = $color[2] ;
                    break ;

                case 3 :
                    $i = $app_url . '/' . $icon[3] ;
                    $c = $color[3] ;
                    break ;
            }

            $response[] = [
                'id' => $reservation->id,
                'name' => $reservation->location->name,
                'date' => $reservation->date,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'verified' => UserEvent::STATUS_KEYS[$reservation->verified],
                'num_people_invited' => $reservation->num_people_invited,
                'logo' => $reservation->location->logo,
                'icon' => $i ,
                'color' => $c
            ];
        }
        return response()->json($response , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function getUserGeneralEventDetails($event_id): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $event = UserEvent::query()->find($event_id);
        if(!$event)
        {
            return response()->json([
                "error" => "invalid event id",
                "status_code" => 422,
            ], 422);
        }

        $event = UserEvent::query()->where('id' , $event_id)->first();
        if(!$event)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $startTime = Carbon::parse($event->date . ' ' . $event->start_time);
        $currentTime = Carbon::now();
        $diff = $startTime->diff($currentTime);

        $remaining_days = sprintf('%03d', $diff->days);
        $remaining_hours = sprintf('%02d', $diff->h);
        $remaining_minutes = sprintf('%02d', $diff->i);

        if ($currentTime->isAfter($startTime) || $event->verified == 2){
            $remaining_days = sprintf('%03d', 000);
            $remaining_hours = sprintf('%02d', 00);
            $remaining_minutes = sprintf('%02d', 00);
        }

        $response = [
            'id' => $event->id,
            'date' => $event->date,
            'verified' => UserEvent::STATUS_KEYS[$event->verified],
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
            'event'=> $event->event->name ,
            'days' => $remaining_days,
            'time' => $remaining_hours . ':' . $remaining_minutes,
            'invitation_type' => $event->invitation_type ,
            'num_people_joined' => $event->num_people_joined .'/'. $event->num_people_invited
        ];

        return response()->json($response , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function StatisticsReservation(): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $now = Carbon::now();
        $confirmed = UserEvent::query()->where('verified' , 'Confirmed')->get();

        foreach ($confirmed as $confirm)
        {
            $checkTime = Carbon::parse($confirm->date . ' ' . $confirm->end_time);
            if($checkTime < $now && $confirm->verified == 1)
            {
                $confirm->verified = UserEvent::STATUS_KEYS[3];
                $confirm->save();
            }
        }

        $confirmed = UserEvent::query()->where('verified' , 'Confirmed')->count();
        $rejected = UserEvent::query()->where('verified' , 'Rejected')->count();
        $pending = UserEvent::query()->where('verified' , 'Pending')->count();

        $response1 = [
            'confirmed reservations' => $confirmed ,
            'color' => '#60B246' ,
            'icon' => env('APP_URL') . '/Icon/7.png'
        ];

        $response2 = [
            'rejected reservations' => $rejected ,
            'color' => '#D1DC36',
            'icon' => env('APP_URL') . '/Icon/6.png'
        ];
        $response3 = [
            'pending reservations' => $pending ,
            'color' => '#F2EB3B' ,
            'icon' => env('APP_URL') . '/Icon/5.png'
        ];

        $response = [$response1 , $response2 ,$response3];

        return response()->json($response ,200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function getGeneralDetails($event_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = UserEvent::query()->find($event_id)->first();
        if(!$exist)
        {
            return response()->json([
                "error" => "invalid event id",
                "status_code" => 422,
            ], 422);
        }

        $status = !($exist->num_people_joined == $exist->num_people_invited) ;

        $picture = LocationPicture::query()
            ->where('location_id' , $exist->location_id)
            ->pluck('picture')
            ->toArray();

        $response = [
            'id' => $exist->id ,
            'date' => $exist->date ,
            'verified' => UserEvent::STATUS_KEYS[$exist->verified] ,
            'start_time' => $exist->start_time ,
            'end_time' => $exist->end_time ,
            'num_people_joined' => $exist->num_people_joined.'/'.$exist->num_people_invited ,
            'photo_1' => $picture[0],
            'photo_2' => $picture[1],
            'photo_3' => $picture[2],
            'status' => $status
        ];
        return response()->json($response , 200);
    }
}
