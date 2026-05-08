<?php

namespace App\Filament\Pages\Auth;

use App\Models\Shop\Product;
use App\Models\Team;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Login extends \Filament\Auth\Pages\Login
{
    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function authenticate(): ?\Filament\Auth\Http\Responses\Contracts\LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        $email = $data['email'];
        $name = $data['name'];

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(Str::random(32)),
            ],
        );

        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

        if ($user->teams()->doesntExist()) {
            $team = Team::create(['name' => "{$user->name}'s Demo"]);
            $user->teams()->attach($team);
        } else {
            $team = $user->teams()->first();
        }

        if (! Product::where('team_id', $team->getKey())->exists()) {
            \App\Actions\SeedDemoData::run($team, $user);
        }

        Filament::auth()->login($user);

        session()->regenerate();

        return app(\Filament\Auth\Http\Responses\Contracts\LoginResponse::class);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
            ]);
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Name')
            ->required()
            ->autocomplete('name')
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete()
            ->autocomplete();
    }

    protected function getAuthenticateFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('authenticate')
            ->label('Start demo')
            ->submit('authenticate');
    }

    public function getSubheading(): string | Htmlable | null
    {
        return new \Illuminate\Support\HtmlString(
            'Welcome to the Filament Plugins demo application! Enter your email to seed your own testing playground. A tour of all plugins awaits.'
        );
    }

    public function getHeading(): string | Htmlable | null
    {
        return null;
    }
}
