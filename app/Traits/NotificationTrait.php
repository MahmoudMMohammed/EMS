<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\ReservationConfirmed;

trait NotificationTrait
{
    public function createNotification($user_id, $message, $title, $admin_id)
    {
        $user = User::findOrFail($user_id);
        $admin = User::findOrFail($admin_id);

        $data = [
            'user' => $user->name,
            'admin' => $admin->name,
            'message' => $message,
            'title' => $title,
            'admin_id' => $admin_id,
            'user_picture' => $user->profile->profile_picture,
            'admin_picture' => $admin->profile->profile_picture,
        ];

        return Notification::create([
            'type' => 'default',
            'notifiable_type' => User::class,
            'notifiable_id' => $user_id,
            'data' => json_encode($data),
        ]);
    }
}
