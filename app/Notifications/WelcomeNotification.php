<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use RalphJSmit\Filament\Notifications\Concerns\StoresNotificationInDatabase;
use RalphJSmit\Filament\Notifications\Contracts\AsFilamentNotification;
use RalphJSmit\Filament\Notifications\FilamentNotification;

class WelcomeNotification extends Notification implements AsFilamentNotification
{
    use StoresNotificationInDatabase;

    public static function toFilamentNotification(): FilamentNotification
    {
        return FilamentNotification::make()
            ->message('Welcome to the demo!')
            ->description('Explore all the plugins and features available in this demo application.')
            ->icon('heroicon-o-sparkles', 'success');
    }
}
