<?php

namespace App\Helpers;

use App\Models\Notifikasi;

class NotificationHelper
{
    /**
     * Create a new notification.
     *
     * @param string|array $roles
     * @param string $title
     * @param string $message
     * @param string|null $type
     * @param string|null $url
     * @return void
     */
    public static function create($roles, string $title, string $message, ?string $type = 'general', ?string $url = null)
    {
        $roleArray = is_array($roles) ? $roles : [$roles];

        foreach ($roleArray as $role) {
            Notifikasi::create([
                'role' => $role,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'url' => $url,
            ]);
        }
    }
}
