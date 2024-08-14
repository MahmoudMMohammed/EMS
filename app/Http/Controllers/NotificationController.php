<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\ReservationConfirmed;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\isEmpty;

class NotificationController extends Controller
{
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
    public function createNotification(Request $request): JsonResponse
    {
        $admin = Auth::user();
        TranslateTextHelper::setTarget($admin -> profile -> preferred_language);

        $validator = Validator::make( $request->all() , [
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }
        $user = User::findOrFail($request->user_id);

        $data = [
            'user' => $user->name,
            'message' => $request->message,
            'title' => $request->title
        ];

        $notification = Notification::create([
            'type' => ReservationConfirmed::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $request->user_id,
            'data' => json_encode($data),
        ]);
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

}
