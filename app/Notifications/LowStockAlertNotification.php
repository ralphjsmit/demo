<?php

namespace App\Notifications;

use App\Models\Shop\Product;
use Illuminate\Notifications\Notification;
use RalphJSmit\Filament\Notifications\Concerns\StoresNotificationInDatabase;
use RalphJSmit\Filament\Notifications\Contracts\AsFilamentNotification;
use RalphJSmit\Filament\Notifications\FilamentNotification;

class LowStockAlertNotification extends Notification implements AsFilamentNotification
{
    use StoresNotificationInDatabase;

    public function __construct(
        protected Product $product,
    ) {}

    public static function toFilamentNotification(): FilamentNotification
    {
        return FilamentNotification::make()
            ->message(fn (self $notification) => "Low stock alert: {$notification->product->name}")
            ->description(fn (self $notification) => "Only {$notification->product->qty} units remaining (security stock: {$notification->product->security_stock})")
            ->icon('heroicon-o-exclamation-triangle', 'warning');
    }
}
