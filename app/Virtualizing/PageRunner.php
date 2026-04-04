<?php

namespace App\Virtualizing;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\Drawer\Utils;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Mechanisms\HandleComponents\HandleComponents;

use function Livewire\on;
use function Livewire\store;

class PageRunner
{
    private ?Component $lastComponent = null;

    private function __construct(
        private array $currentSnapshot,
        private array $currentEffects,
        private PageContext $context,
    ) {}

    public static function make(
        string $pageClass,
        array $params = [],
        ?string $panel = null,
        ?Model $tenant = null,
        ?Authenticatable $user = null,
    ): static {
        $context = new PageContext($panel, $tenant, $user);
        $context->apply();

        $name = static::registerComponent($pageClass);

        $component = null;
        $off = on('dehydrate', function ($comp) use (&$component) {
            $component = $comp;
        });

        $handleComponents = app(HandleComponents::class);
        $html = $handleComponents->mount($name, $params);

        $off();

        app('livewire')->flushState();

        $snapshot = Utils::extractAttributeDataFromHtml($html, 'wire:snapshot');
        $effects = Utils::extractAttributeDataFromHtml($html, 'wire:effects');

        $runner = new static(
            currentSnapshot: $snapshot,
            currentEffects: $effects ?? [],
            context: $context,
        );

        $runner->lastComponent = $component;

        return $runner;
    }

    public static function fromState(PageState $state): static
    {
        $context = PageContext::fromArray($state->contextData);
        $context->apply();

        return new static(
            currentSnapshot: $state->snapshot,
            currentEffects: $state->effects,
            context: $context,
        );
    }

    // --- Interaction methods ---

    public function set(string|array $property, mixed $value = null): static
    {
        $updates = is_array($property) ? $property : [$property => $value];

        return $this->update(updates: $updates);
    }

    public function call(string $method, mixed ...$params): static
    {
        return $this->update(calls: [
            ['method' => $method, 'params' => $params, 'path' => ''],
        ]);
    }

    public function refresh(): static
    {
        return $this->update();
    }

    // --- Action methods ---

    public function mountAction(string $name, array $arguments = [], array $context = []): static
    {
        return $this->call('mountAction', $name, $arguments, $context);
    }

    public function callMountedAction(array $arguments = []): static
    {
        return $this->call('callMountedAction', $arguments);
    }

    public function callAction(string $name, array $data = [], array $arguments = [], array $context = []): static
    {
        $this->mountAction($name, $arguments, $context);

        if (filled($data)) {
            $this->fillActionForm($data);
        }

        return $this->callMountedAction();
    }

    public function fillActionForm(array $data): static
    {
        $mountedActions = $this->getSnapshotData('mountedActions') ?? [];
        $lastIndex = count($mountedActions) - 1;

        if ($lastIndex < 0) {
            return $this;
        }

        $schemaStatePath = "mountedActionSchema{$lastIndex}";

        $updates = [];

        foreach (Arr::dot($data) as $key => $value) {
            $updates["{$schemaStatePath}.{$key}"] = $value;
        }

        return $this->update(updates: $updates);
    }

    // --- State methods ---

    public function toState(): PageState
    {
        return new PageState(
            snapshot: $this->currentSnapshot,
            effects: $this->currentEffects,
            contextData: $this->context->toArray(),
        );
    }

    public function getSnapshot(): array
    {
        return $this->currentSnapshot;
    }

    public function getEffects(): array
    {
        return $this->currentEffects;
    }

    public function getComponent(): ?Component
    {
        return $this->lastComponent;
    }

    // --- Internal ---

    private function update(array $calls = [], array $updates = []): static
    {
        $this->context->apply();

        // Set X-Livewire header so SupportRedirects captures redirects as effects
        // instead of calling abort() with a RedirectResponse.
        $request = request();
        $hadLivewireHeader = $request->hasHeader('X-Livewire');
        $request->headers->set('X-Livewire', 'true');

        $component = null;
        $off = on('dehydrate', function ($comp) use (&$component) {
            $component = $comp;
        });

        // Skip Blade rendering during update — we only need the snapshot, not HTML.
        // We provide a minimal root element so dehydration can still produce a valid snapshot.
        $offSkip = on('hydrate', function ($comp) {
            store($comp)->set('skipRender', '<div></div>');
        });

        $handleComponents = app(HandleComponents::class);

        try {
            [$newSnapshot, $effects] = $handleComponents->update(
                $this->currentSnapshot,
                $updates,
                $calls,
            );
        } finally {
            if (! $hadLivewireHeader) {
                $request->headers->remove('X-Livewire');
            }
        }

        $off();
        $offSkip();

        app('livewire')->flushState();

        $this->currentSnapshot = $newSnapshot;
        $this->currentEffects = $effects;
        $this->lastComponent = $component;

        return $this;
    }

    private function getSnapshotData(?string $key = null): mixed
    {
        $data = $this->currentSnapshot['data'] ?? [];

        // Unwrap synth tuples to get plain values
        $data = $this->untupleify($data);

        if ($key === null) {
            return $data;
        }

        return data_get($data, $key);
    }

    private function untupleify(mixed $payload): mixed
    {
        if (Utils::isSyntheticTuple($payload)) {
            $payload = $payload[0];
        }

        if (is_array($payload)) {
            foreach ($payload as $key => $child) {
                $payload[$key] = $this->untupleify($child);
            }
        }

        return $payload;
    }

    private static function registerComponent(string $pageClass): string
    {
        $registry = app(ComponentRegistry::class);

        $name = $registry->getName($pageClass);

        if (! app('livewire')->isDiscoverable($name)) {
            app('livewire')->component($name, $pageClass);
        }

        return $name;
    }
}
