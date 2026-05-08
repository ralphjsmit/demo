<?php

use App\Models\User;
use RalphJSmit\Filament\Notifications\Filament\Pages\Notifications;
use RalphJSmit\Filament\Notifications\Filament\Resources\NotificationResource;
use RalphJSmit\Filament\Notifications\Models\DatabaseNotification;

return [
    'notifications' => [
        // Add the notification classes that your users are allowed to send.
        // \App\Notifications\TestNotification::class,
    ],

    'notifiables' => [
        'classes' => [
            // The models that can receive notifications.
            User::class,
        ],

        'search-attributes' => [
            // The column(s) that will be used when searching for a recipient...
            // Leave empty to use the respective `title-attribute`.
            // User::class => ['first_name', 'last_name'],
        ],

        'title-attributes' => [
            // A display-friendly attribute that should be used in the NotificationResource to display each record.
            User::class => 'name',
        ],

        'groups' => [
            // If you're using a multi-tenant application, you can specify whether you want to group
            // recipients by a relationship. You can use this feature to select notifications to
            // just a subset of recipients (for example, the recipients belonging to a tenant).
            //            User::class => [
            //                'relationship' => 'tenant',
            //                'inverse-relationship' => 'users',
            //                'title-attribute' => 'name',
            //            ],
        ],
    ],

    'register' => [
        'models' => [
            'database-notification' => DatabaseNotification::class,
        ],
        'resources' => [
            'notifications' => NotificationResource::class,
        ],
        'pages' => [
            Notifications::class,
        ],
    ],
];
