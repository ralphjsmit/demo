<?php

namespace App\Actions;

use App\Models\Team;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

class ResetDemoData
{
    public static function run(Team $team): void
    {
        $teamId = $team->getKey();

        Activity::where(function ($query) use ($teamId): void {
            $query->whereHasMorph('subject', '*', fn ($q) => $q->where('team_id', $teamId));
        })->delete();

        $team->posts()->each(fn ($post) => $post->comments()->delete());
        $team->posts()->delete();
        $team->authors()->delete();
        $team->blogCategories()->delete();

        $team->orders()->each(function ($order): void {
            $order->items()->delete();
            $order->payments()->delete();
            $order->address()?->delete();
            $order->forceDelete();
        });

        $team->products()->each(fn ($product) => $product->comments()->delete());
        $team->products()->each(fn ($product) => $product->mediaLibraryItems()->detach());
        $team->products()->delete();
        $team->customers()->forceDelete();
        $team->brands()->delete();
        $team->shopCategories()->delete();

        Storage::disk('r2_public')->deleteDirectory($team->getStoragePrefix());
    }
}
