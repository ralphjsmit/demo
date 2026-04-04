<?php

namespace App\Virtualizing;

use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PageContext
{
    public function __construct(
        public readonly ?string $panelId = null,
        public readonly ?Model $tenant = null,
        public readonly ?Authenticatable $user = null,
    ) {}

    public function apply(): void
    {
        if ($this->panelId) {
            Filament::setCurrentPanel($this->panelId);
            Filament::bootCurrentPanel();
        }

        if ($this->user) {
            $guard = Filament::getAuthGuard();
            auth()->guard($guard)->setUser($this->user);
        }

        if ($this->tenant) {
            Filament::setTenant($this->tenant, isQuiet: true);
        }

        $this->ensureRequestExists();
    }

    private function ensureRequestExists(): void
    {
        if (! app()->bound('request') || ! app('request') instanceof Request) {
            app()->instance('request', Request::create('/', 'GET'));
        }
    }

    public function toArray(): array
    {
        return [
            'panelId' => $this->panelId,
            'tenantClass' => $this->tenant ? $this->tenant::class : null,
            'tenantKey' => $this->tenant?->getKey(),
            'userClass' => $this->user ? $this->user::class : null,
            'userId' => $this->user?->getAuthIdentifier(),
            'userGuard' => $this->panelId ? Filament::getPanel($this->panelId)->getAuthGuard() : null,
        ];
    }

    public static function fromArray(array $data): static
    {
        $tenant = null;

        if ($data['tenantClass'] && $data['tenantKey']) {
            $tenant = $data['tenantClass']::find($data['tenantKey']);
        }

        $user = null;

        if ($data['userClass'] && $data['userId']) {
            $user = $data['userClass']::find($data['userId']);
        }

        return new static(
            panelId: $data['panelId'],
            tenant: $tenant,
            user: $user,
        );
    }
}
