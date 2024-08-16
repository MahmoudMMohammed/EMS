<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Feedback;
use App\Models\Food;
use App\Models\Location;
use App\Models\MainEvent;
use App\Models\User;
use App\Models\UserEvent;
use App\Models\Warehouse;
use App\Traits\NotificationTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    use NotificationTrait;
    public function WebSearchAdmin (Request $request): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $validator = Validator::make( $request->all() , [
            'search' => 'required | string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $results = User::with('profile')
            ->where('role' , 'Admin')
            ->where('name' , 'LIKE' ,  $request->search . '%')
            ->orderBy('users.name')
            ->get();

        if(!$results->count() > 0)
        {
            return response()->json([
                "message" => "No result search found !" ,
                "status_code" => 404
            ] , 404);
        }

        $data = $results->pluck('name')->toArray();
        $data = TranslateTextHelper::batchTranslate($data);
        $response = [];
        foreach ($results as $result)
        {
            $response [] = [
                'name' => $data[$result->name] ,
                'email' => $result->email ,
                'profile_picture' => $result -> profile -> profile_picture
            ];
        }

        return response()->json($response , 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    public function WebThreeStatistic(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $feedbacks = Feedback::query()->count();

        $app_downloads = User::query()->where('role' , 'User')->count();

        $warehouses = Warehouse::query()->count();

        if(!$warehouses)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $response_data = TranslateTextHelper::batchTranslateArray(['ratings counts' , 'Application downloads' , 'Warehouse censuses']);

        $response = [
            ['id' => 1 , 'number' => $feedbacks , 'description' => $response_data[0]] ,
            ['id' => 2 , 'number' => $app_downloads , 'description' => $response_data[1]] ,
            ['id' => 3 , 'number' => $warehouses , 'description' => $response_data[2]]
        ];
        return response()->json($response , 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    public function WebCounts(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $location = Location::query()->count();
        if(!$location){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Food = Food::query()->count();
        if(!$Food){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Drink = Drink::query()->count();
        if(!$Drink){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Accessory = Accessory::query()->count();
        if(!$Accessory){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $custom = 'Custom' ;

        $event = MainEvent::query()->where('name' , '!=' , $custom)->count();
        if(!$event){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $response_data = TranslateTextHelper::batchTranslateArray(['Locations' , 'Foods' , 'Drinks' , 'Accessories' , 'Events']);

        $response = [
            ['number' => $location , 'description' =>$response_data[0] ] ,
            ['number' => $Food , 'description' =>$response_data[1] ] ,
            ['number' => $Drink , 'description' => $response_data[2]] ,
            ['number' => $Accessory , 'description' => $response_data[3]] ,
            ['number' => $event , 'description' => $response_data[4]]
        ];

        return response()->json($response , 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    public function WebGetAdmins(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $admins = User::with('profile')
            ->where('role' , "Admin")
            ->where('id' , '!=' , $user->id)
            ->get();

        if(!$admins->count() > 0)
        {
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $data = $admins->pluck('name')->toArray();
        $data = TranslateTextHelper::batchTranslate($data);

        $response = [];
        foreach ($admins as $admin)
        {
            $response [] = [
                'admin_id' => $admin->id ,
                'name' => $data[$admin->name] ,
                'email' => $admin->email ,
                'profile_picture' => $admin -> profile -> profile_picture
            ];
        }

        return response()->json($response , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////
    public function acceptReservation($event_id): JsonResponse
    {
        $admin = Auth::user();
        TranslateTextHelper::setTarget($admin->profile->preferred_language);
        $event = UserEvent::find($event_id);
        $user = User::findOrFail($event->user_id);
        $location = Location::findOrFail($event->location_id);

        if ($location->user_id != $admin->id && $admin->role != 'Owner'){
            return response()->json([
                "error" => TranslateTextHelper::translate("Unauthorized, You cannot perform this action!"),
                "status_code" => 403
            ], 403);
        }

        if (!$event) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Event not found!"),
                "status_code" => 404
            ], 404);
        }

        if ($event->verified == 1){
            return response()->json([
                "error" => TranslateTextHelper::translate("Event already accepted!"),
                "status_code" => 400
            ], 400);
        }

        if ($event->verified == 2 || $event->verified == 3 ){
            return response()->json([
                "error" => TranslateTextHelper::translate("This action cannot be done!, You cannot accept Rejected or Finished reservation."),
                "status_code" => 400
            ], 400);
        }

        $event->setStatusByIndex(1);
        $event->save();

        TranslateTextHelper::setTarget($user->profile->preferred_language);
        event(new NotificationEvent($user->id,TranslateTextHelper::translate("Your reservation for event in $event->date has been approved."),TranslateTextHelper::translate("Reservation Approved")));
        $this->createNotificationForUser($user->id,TranslateTextHelper::translate("Your reservation for event in $event->date has been approved."),TranslateTextHelper::translate("Reservation Approved"),$admin->id);

        TranslateTextHelper::setTarget($admin->profile->preferred_language);
        return response()->json([
            "error" => TranslateTextHelper::translate("Event accepted successfully"),
            "status_code" => 200
        ], 200);

    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function rejectReservation($event_id): JsonResponse
    {
        $admin = Auth::user();
        TranslateTextHelper::setTarget($admin->profile->preferred_language);
        $event = UserEvent::find($event_id);
        $user = User::findOrFail($event->user_id);
        $location = Location::findOrFail($event->location_id);

        if ($location->user_id != $admin->id && $admin->role != 'Owner'){
            return response()->json([
                "error" => TranslateTextHelper::translate("Unauthorized, You cannot perform this action!"),
                "status_code" => 403
            ], 403);
        }

        if (!$event) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Event not found!"),
                "status_code" => 404
            ], 404);
        }

        if ($event->verified == 2){
            return response()->json([
                "error" => TranslateTextHelper::translate("Event already Rejected!"),
                "status_code" => 400
            ], 400);
        }

        if ($event->verified == 1 || $event->verified == 3 ){
            return response()->json([
                "error" => TranslateTextHelper::translate("This action cannot be done!, You cannot reject Confirmed or Finished reservations."),
                "status_code" => 400
            ], 400);
        }

        $event->setStatusByIndex(2);
        $event->save();

        TranslateTextHelper::setTarget($user->profile->preferred_language);
        event(new NotificationEvent($user->id,TranslateTextHelper::translate("Your reservation for event in $event->date has been rejected"),TranslateTextHelper::translate("Reservation Rejected")));
        $this->createNotificationForUser($user->id,TranslateTextHelper::translate("Your reservation for event in $event->date has been rejected."),TranslateTextHelper::translate("Reservation Rejected"),$admin->id);

        TranslateTextHelper::setTarget($admin->profile->preferred_language);
        return response()->json([
            "error" => TranslateTextHelper::translate("Event Rejected successfully"),
            "status_code" => 200
        ], 200);

    }
    ///////////////////////////////////////////////////////////////////////////////////////
    public function GetAvailableAdmin() :JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $Available = Location::query()->pluck('user_id')->toArray();
        if(!$Available)
        {
            return response()->json([
                'message' => 'No locations founded' ,
                'status_code' => 404
            ] , 404);
        }

        $Admins = User::query()
            ->whereNotIn( 'id' , $Available)
            ->where('role' , 'Admin')
            ->select('id' , 'name')
            ->get();

        if($Admins->isEmpty())
        {
            return response()->json([
                'message' => 'There is no admin available to manage this place. Dear owner, hire new people to manage this place.',
                'status_code' => 404
            ], 404);
        }

        return response()->json($Admins , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////
    public function getAllAdmin():JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $all_admin = User::query()
            ->where('role' , 'Admin')
            ->select('id' , 'name')
            ->get();

        if(!$all_admin)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }
        return response()->json($all_admin , 200);
    }
    /////////////////////////////////////////////////////////////////////////////////
    public function getAllNewAdmin():JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $locations = Location::query()->pluck('user_id')->toArray();
        if(!$locations)
        {
            return response()->json([
                'message' => 'There is no location to manage it.',
                'status_code' => 404
            ], 404);
        }

        $new_admin = User::query()
            ->whereNotIn('id' , $locations)
            ->where('role' , 'Admin')
            ->select('id' , 'name')
            ->get();

        if($new_admin->isEmpty())
        {
            return response()->json([
                'message' => 'There is no admin available to manage this place. Dear owner, hire new people to manage this place.',
                'status_code' => 404
            ], 404);
        }

        return response()->json($new_admin , 200);
    }
}

