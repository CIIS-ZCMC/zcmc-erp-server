<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\UserNotification;
use App\Models\User;

class NotificationService
{
    public function notify(User $user, array $notif_details):void  {
        $notification = Notification::create([
            'user_id' => auth()->user()->id,
            'title' => $notif_details['title'],
            'description' => $notif_details['description'],
            'module_path' => null,
            'seen' => false
        ]);
        $user_notification = UserNotification::create([
            'user_id' => $user->id,
            'notification_id' => $notification->id,
            'seen' => false
        ]);
    }


    public function notifyAll(array $users, array $notif_details):void
    {
        $notification = Notification::create([
            'user_id' => auth()->user()->id,
            'title' => $notif_details['title'],
            'description' => $notif_details['description'],
            'module_path' => null,
            'seen' => false
        ]);

        foreach ($users as $user) {
            $user_notification = UserNotification::create([
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'seen' => $user_id
            ]);
        }
    }
}
