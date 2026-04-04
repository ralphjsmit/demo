# Architecture Best Practices
## Default Sort by Descending

When no explicit order is specified, sort by `id` or `created_at` descending. Explicit ordering prevents cross-database inconsistencies between MySQL and Postgres.

Incorrect:
```php
$posts = Post::paginate();
```

Correct:
```php
$posts = Post::latest()->paginate();
```

## Use Atomic Locks for Race Conditions

Prevent race conditions with `Cache::lock()` or `lockForUpdate()`.

```php
Cache::lock('order-processing-'.$order->id, 10)->block(5, function () use ($order) {
    $order->process();
});

// Or at query level
$product = Product::where('id', $id)->lockForUpdate()->first();
```

## Use `mb_*` String Functions

When no Laravel helper exists, prefer `mb_strlen`, `mb_strtolower`, etc. for UTF-8 safety. Standard PHP string functions count bytes, not characters.

Incorrect:
```php
strlen('José');          // 5 (bytes, not characters)
strtolower('MÜNCHEN');  // 'mÜnchen' — fails on multibyte
```

Correct:
```php
mb_strlen('José');             // 4 (characters)
mb_strtolower('MÜNCHEN');     // 'münchen'

// Prefer Laravel's Str helpers when available
Str::length('José');          // 4
Str::lower('MÜNCHEN');        // 'münchen'
```
## Use `Context` for Request-Scoped Data

The `Context` facade passes data through the entire request lifecycle — middleware, controllers, jobs, logs — without passing arguments manually.

```php
// In middleware
Context::add('tenant_id', $request->header('X-Tenant-ID'));

// Anywhere later — controllers, jobs, log context
$tenantId = Context::get('tenant_id');
```

Context data automatically propagates to queued jobs and is included in log entries. Use `Context::addHidden()` for sensitive data that should be available in queued jobs but excluded from log context. If data must not leave the current process, do not store it in `Context`.

## Use `Concurrency::run()` for Parallel Execution

Run independent operations in parallel using child processes — no async libraries needed.

```php
use Illuminate\Support\Facades\Concurrency;

[$users, $orders] = Concurrency::run([
    fn () => User::count(),
    fn () => Order::where('status', 'pending')->count(),
]);
```

Each closure runs in a separate process with full Laravel access. Use for independent database queries, API calls, or computations that would otherwise run sequentially.

## Convention Over Configuration

Follow Laravel conventions. Don't override defaults unnecessarily.

Incorrect:
```php
class Customer extends Model
{
    protected $table = 'Customer';
    protected $primaryKey = 'customer_id';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_customer', 'customer_id', 'role_id');
    }
}
```

Correct:
```php
class Customer extends Model
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
```
