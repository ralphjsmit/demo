<?php

use App\Actions\ResetDemoData;
use App\Actions\SeedDemoData;
use App\Livewire\Form;
use App\Models\Team;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('form', Form::class);

Route::redirect('login-redirect', '/login');

Route::post('demo/reset/{tenant}', function (Team $tenant) {
    abort_unless(auth()->check(), 403);
    abort_unless(auth()->user()->teams()->whereKey($tenant->getKey())->exists(), 403);

    ResetDemoData::run($tenant);
    SeedDemoData::run($tenant, auth()->user());

    return redirect()->back();
})->middleware('web')->name('demo.reset');
