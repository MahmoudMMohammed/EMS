<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\ReservationConfirmed;
use App\Traits\NotificationTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use NotificationTrait;
    public function getAdminNotifications(): JsonResponse
    {
        $admin = Auth::user();
        TranslateTextHelper::setTarget($admin->profile->preferred_language);

        $adminNotifications = Notification::select('id', 'data', 'created_at')
            ->whereNotifiableId($admin->id)->get();

        $notifications = collect($adminNotifications)->map(function ($notification) {
            $date = Carbon::parse($notification->created_at);
            $notification['title'] = $notification->data['title'];
            $notification['message'] = $notification->data['message'];
            $notification['time'] = $date->diffForHumans();
            $notification['user_picture'] = $notification->data['user_picture'];
            unset($notification['data']);
            return $notification;
        });

        if ( count($notifications) === 0){
            return response()->json([
                "message" => TranslateTextHelper::translate("You have no notifications to show!"),
                'status_code' => 404
            ], 404);
        }
        return response()->json($notifications);

    }

    ///////////////////////////////////////////////////////////////////////////////////////////
    public function create(Request $request): JsonResponse
    {
        $admin = Auth::user();
        TranslateTextHelper::setTarget($admin -> profile -> preferred_language);

        $validator = Validator::make( $request->all() , [
            'user_id' => 'required|exists:users,id',
            'admin_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $notification = $this->createNotification($request->user_id, $request->message, $request->title, $request->admin_id);
        if (!$notification){
            return response()->json([
                "message" => TranslateTextHelper::translate("Failed to created a notification!"),
                'status_code' => 400
            ], 400);
        }
        return response()->json([
            "message" => TranslateTextHelper::translate("Notification created successfully!"),
            'status_code' => 201
        ], 201);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////
    public function countUnreadNotifications(): JsonResponse
    {
        $user = Auth::user();

        $count = Notification::whereNotifiableId($user->id)->whereNull('read_at')->count();

        return response()->json(['count' => $count]);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function markNotificationAsRead($notification_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $notification = Notification::findOrFail($notification_id);
        $notification->markAsRead();

        return response()->json(['message' => TranslateTextHelper::translate("Notification marked as read.")]);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////
    public function getUserNotifications(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $userNotifications = Notification::select('id', 'data', 'created_at')
            ->whereNotifiableId($user->id)->get();

        $notifications = collect($userNotifications)->map(function ($notification) {
            $date = Carbon::parse($notification->created_at);
            $notification['title'] = $notification->data['title'];
            $notification['message'] = $notification->data['message'];
            $notification['time'] = $date->diffForHumans();
            $notification['admin_picture'] = $notification->data['admin_picture'];
            unset($notification['data']);
            return $notification;
        });

        if ( count($notifications) === 0){
            return response()->json([
                "message" => TranslateTextHelper::translate("You have no notifications to show!"),
                'status_code' => 404
            ], 404);
        }
        return response()->json($notifications);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////

}
