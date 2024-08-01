<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Helpers\TranslateTextHelper;
use App\Models\Feedback;
use App\Models\User;
use App\Models\UserEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller
{
    public function WebGetOwners (): JsonResponse
    {
        $user = Auth::user();

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $owners = User::with('profile')->where('role' , "Owner")->get();

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


}
