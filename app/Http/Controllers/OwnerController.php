<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Helpers\TranslateTextHelper;
use App\Models\AppRating;
use App\Models\EventSupplement;
use App\Models\Feedback;
use App\Models\Location;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserEvent;
use App\Models\WalletCharge;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as password_rule;
use Illuminate\Support\Facades\Hash;

class OwnerController extends Controller
{
    public function WebGetOwners (): JsonResponse
    {
        $user = Auth::user();

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $owners = User::with('profile')
            ->where('role' , "Owner")
            ->where('id' , '!=' , $user->id)
            ->get();

        if(!$owners->count() > 0)
        {
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $data = $owners->pluck('name')->toArray();
        $data = TranslateTextHelper::batchTranslate($data);

        $response = [];
        foreach ($owners as $owner)
        {
            $response [] = [
                'owner_id' => $owner->id ,
                'name' => $data[$owner->name] ,
                'profile_picture' => $owner -> profile -> profile_picture
            ];
        }

        return response()->json($response , 200);
    }

    ////////////////////////////////////////////////////////////////
    public function WebDeleteFeedback($feedback_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $feedback = Feedback::query()->find($feedback_id);

        if (!$feedback) {
            return response()->json([
                'error' => 'Feedback not found',
                'status_code' => 404,
            ], 404);
        }

        $feedback->delete();

        return response()->json([
            'message' => TranslateTextHelper::translate('Rating has been deleted successfully'),
            'status_code' => 200,
        ], 200);

    }

    ////////////////////////////////////////////////////////////////
    public function blockUser(Request $request , $user_id): JsonResponse
    {
        $owner = Auth::user();
        TranslateTextHelper::setTarget($owner -> profile -> preferred_language);

        $validator = Validator::make($request->all(), [
            'duration' => 'required|string|in:hour,day,month,year',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'status_code' => 422,
            ], 422);
        }

        $duration = $request->input('duration');

        $user = User::find($user_id);
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'status_code' => 404,
            ], 404);
        }

        if($user->is_blocked == 1)
        {
            return response()->json([
                'message' => TranslateTextHelper::translate("The user has already been banned for a period of time until : $user->blocked_until") ,
                'status_code' => 403
            ] , 403);
        }

        $blocked_until = null;
        switch ($duration) {
            case 'hour':
                $blocked_until = Carbon::now()->addHour();
                break;
            case 'day':
                $blocked_until = Carbon::now()->addDay();
                break;
            case 'month':
                $blocked_until = Carbon::now()->addMonth();
                break;
            case 'year':
                $blocked_until = Carbon::now()->addYear();
                break;
        }

        $user->is_blocked = true;
        $user->blocked_until = $blocked_until;
        $user->save();

        return response()->json([
            'message' => TranslateTextHelper::translate("User blocked until $user->blocked_until successfully"),
            'status_code' => 200,
        ], 200);
    }

    ////////////////////////////////////////////////////////////////
    public function unblockUser($user_id): JsonResponse
    {
        $owner = Auth::user();
        TranslateTextHelper::setTarget($owner->profile->preferred_language);

        $user = User::find($user_id);
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'status_code' => 404,
            ], 404);
        }

        if ($user->is_blocked == 0) {
            return response()->json([
                'message' => TranslateTextHelper::translate("The user is not currently blocked"),
                'status_code' => 403,
            ], 403);
        }

        $user->is_blocked = false;
        $user->blocked_until = null;
        $user->save();

        return response()->json([
            'message' => TranslateTextHelper::translate("User unblocked successfully"),
            'status_code' => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////
    public function deleteReservation($event_id): JsonResponse
    {
        $owner = Auth::user();
        TranslateTextHelper::setTarget($owner->profile->preferred_language);
        $event = UserEvent::find($event_id);


        if (!$event) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Event not found!"),
                "status_code" => 404
            ], 404);
        }

        $user = User::findOrFail($event->user_id);

        if ($event->verified == 1 || $event->verified == 0){
            return response()->json([
                "error" => TranslateTextHelper::translate("Action cannot be done!, Reservation can be deleted only if they are Rejected or Finished."),
                "status_code" => 400
            ], 400);
        }

        $event->delete();

        TranslateTextHelper::setTarget($user->profile->preferred_language);
        event(new NotificationEvent($user->id,TranslateTextHelper::translate("Your reservation for event in $event->date has been deleted"),TranslateTextHelper::translate("Reservation Deleted")));

        TranslateTextHelper::setTarget($owner->profile->preferred_language);
        return response()->json([
            "error" => TranslateTextHelper::translate("Event deleted successfully"),
            "status_code" => 204
        ], 204);
    }
    ////////////////////////////////////////////////////////////////
    public function StatisticsSales() : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong, try again later",
                "status_code" => 422,
            ], 422);
        }

        $user_event_ids = OwnerController::getFinishedAndConfirmedEventsAll();
        $user_event_ids = $user_event_ids->pluck('id');

        $eventSupplements = EventSupplement::query()
            ->whereIn('user_event_id', $user_event_ids)
            ->get();

        $foodTotalSales = 0;
        $drinksTotalSales = 0;
        $accessoriesTotalSales = 0;


        foreach ($eventSupplements as $eventSupplement) {

            foreach ($eventSupplement->food_details as $foodSale) {
                $foodTotalSales += $foodSale['quantity'];
            }

            foreach ($eventSupplement->drinks_details as $drinksSale) {
                $drinksTotalSales += $drinksSale['quantity'];
            }

            foreach ($eventSupplement->accessories_details as $accessoriesSale) {
                $accessoriesTotalSales += $accessoriesSale['quantity'];
            }
        }

        $app_url = env('APP_URL');

        $response = [
            [
                'number of food sold' => $foodTotalSales,
                'color' => '#F8BD19',
                'icon' => $app_url . '/Icon/4.png'
            ],
            [
                'number of drinks sold' => $drinksTotalSales,
                'color' => '#F69B21',
                'icon' => $app_url . '/Icon/9.png'
            ],
            [
                'number of accessories sold' => $accessoriesTotalSales,
                'color' => '#F25427',
                'icon' => $app_url . '/Icon/8.png'
            ],
        ];

        return response()->json($response);
    }
    ////////////////////////////////////////////////////////////////
    public function StatisticsProfits() : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong, try again later",
                "status_code" => 422,
            ], 422);
        }

        $user_events = OwnerController::getFinishedAndConfirmedEventsAll();

        $location_reservations = 0 ;
        $reservation_price = 0;
        foreach ($user_events as $user_event)
        {
            $price = (float)str_replace(['S.P', ',', ' '], '', $user_event->location->reservation_price);

            $start_time = Carbon::parse($user_event->date . ' ' . $user_event->start_time);
            $end_time = Carbon::parse($user_event->date . ' ' . $user_event->end_time);
            $event_time_minutes = $start_time->diffInMinutes($end_time);

            $reservation_price = $price * ($event_time_minutes / 60);
            $location_reservations += $reservation_price ;
        }

        $user_event_ids = OwnerController::getFinishedAndConfirmedEventsAll();
        $user_event_ids = $user_event_ids->pluck('id')->toArray();

        $eventSupplements = EventSupplement::query()
            ->whereIn('user_event_id', $user_event_ids)
            ->get();

        $foodTotalSales = 0;
        $drinksTotalSales = 0;
        $accessoriesTotalSales = 0;

        foreach ($eventSupplements as $eventSupplement) {

            foreach ($eventSupplement->food_details as $foodSale) {
                $price = (float)str_replace(['S.P', ',', ' '], '', $foodSale['price']);
                $quantity = $foodSale['quantity'];
                $foodTotalSales += $price * $quantity;
            }

            foreach ($eventSupplement->drinks_details as $drinksSale) {
                $price = (float)str_replace(['S.P', ',', ' '], '', $drinksSale['price']);
                $quantity = $drinksSale['quantity'];
                $drinksTotalSales += $price * $quantity;
            }

            foreach ($eventSupplement->accessories_details as $accessoriesSale) {
                $price = (float)str_replace(['S.P', ',', ' '], '', $accessoriesSale['price']);
                $quantity = $accessoriesSale['quantity'];
                $accessoriesTotalSales += $price * $quantity;
            }
        }
        $supplementTotalPrice = $foodTotalSales + $drinksTotalSales + $accessoriesTotalSales ;
        $tax = ($supplementTotalPrice + $location_reservations) * 0.05 ;

        $total_profit = $supplementTotalPrice + $location_reservations ;

        $response = [
            [
                'location reservations' => number_format($location_reservations , 2 ).' S.P',
                'color' => '#F33128',
            ],
            [
                'supplement reservations' => number_format($supplementTotalPrice , 2 ).' S.P',
                'color' => '#A71E4A',
            ],
            [
                'taxes reservations' => number_format($tax , 2 ).' S.P',
                'color' => '#7D3696',
            ],
            [
                'total_profits'=> number_format($total_profit , 2 ).' S.P',
                'color'=> '#989898'
            ]
        ];

        return response()->json($response , 200);

    }
    ////////////////////////////////////////////////////////////////
    public function StatisticsRating(): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong, try again later",
                "status_code" => 422,
            ], 422);
        }

        $ratings = AppRating::query()->pluck('rate');

        $ratingCounts = array_fill(1, 5, 0);

        if ($ratings->isEmpty()) {
            $ratingPercentages = array_map(function () {
                return 0.0;
            }, $ratingCounts);

            $ratingPercentages['total_ratings'] = 0;
            $ratingPercentages['average_rating'] = 0.0;

            return response()->json($ratingPercentages , 200);
        }

        $totalRatings = $ratings->count();
        $sumOfRatings = $ratings->sum();

        foreach ($ratings as $rating) {
            if (isset($ratingCounts[$rating])) {
                $ratingCounts[$rating]++;
            }
        }

        $ratingPercentages = [];
        foreach ($ratingCounts as $rating => $count) {
            $ratingPercentages[$rating] = (double) number_format(($count / $totalRatings) * 100, 2);
        }

        $averageRating = (double) number_format($sumOfRatings / $totalRatings , 2);

        $ratingPercentages['total_ratings'] = $totalRatings;
        $ratingPercentages['average_rating'] = $averageRating;

        return response()->json($ratingPercentages , 200);
    }
    //////////////////////////////////////////////////////////////////////
    private function getFinishedAndConfirmedEventsAll()
    {
        $finishedEvents = UserEvent::query()
            ->where('verified' , 'Finished')
            ->get();

        $yesterday = Carbon::parse(now())->subDay();

        $confirmedEvents = UserEvent::query()
            ->where('verified','Confirmed')
            ->get()
            ->filter(function ($event) use ($yesterday) {
                $startTime = Carbon::parse($event->date . ' ' . $event->start_time);
                return $startTime->greaterThanOrEqualTo($yesterday);
            });

        return $finishedEvents->merge($confirmedEvents);
    }

    public function updateAdminLocation(Request $request):JsonResponse
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
            'name_old_id' => 'required|exists:users,id' ,
            'email_old' => 'required|email|exists:users,email' ,
            'new_admin_id' => 'required|exists:users,id',
            'location_id' => 'required|integer|exists:locations,id' ,
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }


        $not_location = Location::query()->where('id' , $request->location_id)->first();

        if($not_location->user_id != $request->name_old_id)
        {
            return response()->json([
                "message" => "The old admin doesn't even run this place",
                "status_code" => 422,
            ] ,201);
        }


        $not_location->update([
            'user_id' => $request->input(['new_admin_id'])
        ]);

        $replace = User::query()
            ->where('id' , $request->input('name_old_id'))
            ->delete();

        return response()->json([
            "message" => "admin location replaced successfully",
            "status_code" => 201,
        ] ,201);
    }
    //////////////////////////////////////////////////////////////////////
    public function AddNewAdmin(Request $request):JsonResponse
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
            'name' => 'required|regex:/^[a-zA-Z\s]+$/' ,
            'email' => 'required|email|unique:users,email' ,
            'password' => ['required' , password_rule::min(6)->numbers()->letters()->mixedCase() ] ,
            'phone_number' => 'required|starts_with:09|digits:10',
            'birthday' => 'required|date_format:Y-m-d' ,
            'gender'=>'required|in:male,female',
            'residence' => 'sometimes|nullable|string',
            'picture' => 'required|image'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $publicPath = public_path("ProfilePictures/Owners&Admins");

        $PicturePath = 'ProfilePictures/Owners&Admins/' . $request->file('picture')->getClientOriginalName();
        $request->file('picture')->move($publicPath, $PicturePath);

        $add = User::query()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'verified'=>1,
            'role' => 'Admin'
        ]);

        $add_profile = Profile::query()->create([
            'user_id' => $add->id,
            'phone_number' => $request->input('phone_number'),
            'birth_date' => $request->input('birthday') ,
            'gender'=>$request->input('gender'),
            'place_of_residence' => $request->input('residence'),
            'profile_picture' => $PicturePath
        ]);

        if(!$add || !$add_profile)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        return response()->json([
            "message" => "admin added successfully",
            "status_code" => 201,
        ], 201);

    }
    /////
    public function rechargeWallet(Request $request):JsonResponse
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
            'email' => 'required|email|exists:users,email' ,
            'value' => 'required|integer|doesnt_start_with:0|max:100000000|min:1'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $user_wallet = User::query()
            ->where('email' , $request->email)
            ->where('role' , 'User')
            ->first();

        if(!$user_wallet)
        {
            return response()->json([
                "error" => "There is a problem with the email you entered",
                "status_code" => 422,
            ], 422);
        }

        $profile = Profile::query()->where('user_id' , $user_wallet->id)->first();

        if(!$profile)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $done = WalletCharge::query()->create([
            'user_id' => $user_wallet->id ,
            'amount' => $request->input('value')
        ]);

        if(!$done)
        {
            return response()->json([
                "error" => "Something went wrong , try again later.",
                "status_code" => 422,
            ], 422);
        }

        $currentBalance = $profile->balance;
        $newBalance = $currentBalance + $request->input('value');

        if ($newBalance > 500000000) {
            return response()->json([
                "error" => "The balance exceeds the allowed limit of 500,000,000 .",
                "status_code" => 422,
            ], 422);
        }

        $profile->update([
            'balance' => $newBalance
        ]);

        $format = number_format($request->value , 2);

        return response()->json([
            "message" => "The user's wallet with Dome: $format S.P has been charged successfully",
            "status_code" => 200,
        ], 200);
    }
}
