# Plan: Virtual Filament Page Instantiation for AI/LLM Tool Calling

## Context

We need a system that can programmatically instantiate any Filament page (ListRecords, EditRecord, CreateRecord, custom pages), serialize its state, and mount/call Filament actions on it — all without a browser. The primary use case is AI/LLM tool calling: an AI agent interacts with Filament pages and actions as if it were a user.

The code will live in `app/Virtualizing/`.

## Approach: Direct `HandleComponents` calls

We call Livewire's `HandleComponents::mount()` and `HandleComponents::update()` directly, bypassing the HTTP layer entirely. This is the same engine that powers both real requests and `Livewire::test()`, but without needing a simulated HTTP request or the test-only `RequestBroker`.

**Why not wrap `Livewire::test()`?**
- `SupportTesting` only registers in `testing` environment
- `RequestBroker` uses PHPUnit's `MakesHttpRequests` trait — unavailable in production
- It disables middleware and exception handling — wrong for production

**Why direct `HandleComponents` works:**
- `HandleComponents::mount($name, $params)` triggers the full lifecycle: `boot → initialize → mount → booted → render → dehydrate → destroy`. Returns HTML with `wire:snapshot` and `wire:effects` embedded as attributes.
- `HandleComponents::update($snapshot, $updates, $calls)` reconstructs from snapshot, hydrates, applies updates, calls methods, renders, dehydrates. Returns `[$newSnapshot, $effects]` directly.
- All Livewire feature hooks (lifecycle, locked properties, models, etc.) are registered by `LivewireServiceProvider` in production — they fire normally.
- Filament actions (`mountAction`, `callMountedAction`) are just public Livewire methods — they work through `update()` calls.

## Files to create

### 1. `app/Virtualizing/PageContext.php`

Sets up the Filament environment before any mount/update call.

**Responsibilities:**
- `Filament::setCurrentPanel($panelId)` + `Filament::bootCurrentPanel()`
- `auth()->guard(Filament::getAuthGuard())->setUser($user)`
- `Filament::setTenant($tenant)` (if multi-tenant)
- Ensure a synthetic `Request` exists in the container (critical for artisan/queue contexts where `request()` would fail)
- Serializable to/from array so it can be stored alongside the page state

**Key detail:** `bootCurrentPanel()` is idempotent (has `$isCurrentPanelBooted` guard). Safe to call in both web and CLI contexts.

### 2. `app/Virtualizing/PageState.php`

Serializable value object wrapping the Livewire snapshot + context.

**Structure:**
```php
class PageState implements JsonSerializable
{
    public function __construct(
        public readonly array $snapshot,    // Livewire snapshot {data, memo, checksum}
        public readonly array $effects,     // Last effects from mount/update
        public readonly array $contextData, // Serialized PageContext
    ) {}

    public function toJson(): string;
    public static function fromJson(string $json): static;
}
```

The snapshot is HMAC-checksummed with the app key — it's tamper-proof and tied to the application.

### 3. `app/Virtualizing/PageRunner.php`

The main orchestrator. Wraps `HandleComponents` with Filament context setup.

**Factory method — `make()`:**
1. Create and apply `PageContext` (panel, auth, tenant)
2. Register component name with Livewire (`app('livewire')->component()`)
3. Hook into `on('dehydrate')` to capture the component instance
4. Call `HandleComponents::mount($name, $params)` — returns HTML
5. Extract snapshot from HTML via `Utils::extractAttributeDataFromHtml()`
6. Extract effects similarly
7. Call `app('livewire')->flushState()` to clean up
8. Return a `PageRunner` instance holding the snapshot

**Interaction methods — all delegate to a private `update()` that calls `HandleComponents::update()`:**
- `set(string|array $property, mixed $value)` — property updates
- `call(string $method, ...$params)` — call any public Livewire method
- `mountAction(string $name, array $arguments, array $context)` — calls component's `mountAction()`
- `callMountedAction(array $arguments)` — calls component's `callMountedAction()`
- `callAction(string $name, array $data, array $arguments)` — convenience: mount + fill + call
- `fillActionForm(array $data)` — sets form data on the mounted action's schema

**State methods:**
- `toState(): PageState` — serialize for storage
- `fromState(PageState $state): static` — restore and continue interacting
- `getSnapshot(): array` — raw snapshot
- `getEffects(): array` — effects from last operation (redirects, notifications, dispatched events)
- `getComponent(): Component` — the last hydrated component instance (ephemeral, not serializable)

**How form data filling works for actions:**
When an action is mounted, Filament stores form state at `data.*` on the component. The `fillActionForm()` method calls `call('fillSchemaWithRawState', $schemaName, $data)` or uses the update mechanism to set `data.*` properties. We'll examine the exact mechanism Filament's `TestsActions::fillForm` uses (it calls `set()` on individual `data.{key}` paths) and replicate that.

### 4. `app/Virtualizing/VirtualizingServiceProvider.php` (if needed)

Only needed if we need to register anything at boot time. May not be necessary since `PageContext::apply()` handles all setup on-demand.

## Execution flow examples

### Mount an EditRecord page
```php
$runner = PageRunner::make(
    pageClass: EditPost::class,
    params: ['record' => $post->getKey()],
    panel: 'admin',
    user: $adminUser,
);
// EditRecord::mount(record: $post->getKey()) fires → resolves record, authorizes, fills form
```

### Call a header action
```php
$runner->callAction('delete');
// Internally: mountAction('delete') → callMountedAction()
// The DeleteAction executes, record is deleted
```

### Mount action with form, fill data, then call
```php
$runner->mountAction('import');
$runner->fillActionForm(['file_path' => '/tmp/data.csv', 'format' => 'csv']);
$runner->callMountedAction();
```

### Serialize and restore
```php
$state = $runner->toState();
$json = $state->toJson();
// Store in DB, cache, pass to next AI tool call...

// Later:
$runner = PageRunner::fromState(PageState::fromJson($json));
$runner->callAction('approve');
```

### Table actions on ListRecords
```php
$runner = PageRunner::make(ListPosts::class, panel: 'admin', user: $admin);
$runner->mountAction('edit', context: ['table' => true, 'recordKey' => $post->getKey()]);
```

## Edge cases handled

| Edge case | How it's handled |
|-----------|-----------------|
| `url()->previous()` in mount | Returns base URL — harmless for programmatic use |
| Redirects from actions | Captured in `$effects['redirect']` — consumer inspects effects |
| Notifications | Dispatched as effects — silently captured, inspectable |
| `#[Locked]` properties | Hydrated normally from snapshot, cannot be changed via updates — correct behavior |
| Model properties | `ModelSynth` serializes as `{class, key}` tuple, freshly loaded from DB on hydrate |
| Authorization checks | Run normally — `PageContext` sets up auth before mount/update |
| Database transactions | Actions use `beginDatabaseTransaction()`/`commitDatabaseTransaction()` — works normally |
| Tenant scoping | `Filament::setTenant()` fires `TenantSet` event — scoping middleware effects apply |

## Key vendor files referenced

- `vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php` — `mount()` (L37-73), `update()` (L86-120), `snapshot()` (L141-159), `fromSnapshot()` (L122-139)
- `vendor/livewire/livewire/src/Drawer/Utils.php` — `extractAttributeDataFromHtml()` (L193-201)
- `vendor/livewire/livewire/src/EventBus.php` — `trigger()` returns finisher callable (L50-81)
- `vendor/filament/filament/src/FilamentManager.php` — `setCurrentPanel()`, `bootCurrentPanel()`, `setTenant()`
- `vendor/filament/actions/src/Concerns/InteractsWithActions.php` — `mountAction()` (L111-192), `callMountedAction()` (L197-343)
- `vendor/filament/filament/src/Resources/Pages/EditRecord.php` — `mount(int|string $record)` (L87-96)
- `vendor/filament/actions/src/Testing/TestsActions.php` — reference for how test macros call actions (L29-49, L78-120)
- `vendor/livewire/livewire/src/Features/SupportTesting/Render.php` — `on('dehydrate')` pattern for capturing component instance (L14-16)

## Verification

1. **Unit test**: Create a test that instantiates `EditPost` via `PageRunner::make()`, verifies the form data matches the record, calls `save` with modified data, and asserts the DB was updated.
2. **Action test**: Mount a `DeleteAction` on an EditRecord page, call it, verify the record is deleted.
3. **Serialization test**: `make()` → `toState()` → `toJson()` → `fromJson()` → `fromState()` → verify the component can still be interacted with.
4. **Artisan command test**: Run `PageRunner::make()` from an artisan command to verify it works without HTTP context.
