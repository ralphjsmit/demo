<?php

use App\Livewire\Form;

\Illuminate\Support\Facades\Route::get('form', Form::class);

\Illuminate\Support\Facades\Route::get('opcache', function () {
    require_once __DIR__ . '/../vendor/amnuts/opcache-gui/index.php';
});
