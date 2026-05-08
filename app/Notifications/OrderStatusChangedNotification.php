<?php

namespace App\Notifications;

use App\Models\Shop\Order;
use Illuminate\Notifications\Notification;
use RalphJSmit\Filament\Notifications\Concerns\StoresNotificationInDatabase;
use RalphJSmit\Filament\Notifications\Contracts\AsFilamentNotification;
use RalphJSmit\Filament\Notifications\FilamentNotification;

class OrderStatusChangedNotification extends Notification implements AsFilamentNotification
{
    use StoresNotificationInDatabase;

    public function __construct(
        protected Order $order,
    ) {}

    public static function toFilamentNotification(): FilamentNotification
    {
        return FilamentNotification::make()
            ->message(fn (self $notification) => "Order #{$notification->order->number} status changed to {$notification->order->status->getLabel()}")
            ->description(fn (self $notification) => "The order for {$notification->order->customer->name} was updated.")
            ->icon('heroicon-o-shopping-cart', 'primary');
    }
}
