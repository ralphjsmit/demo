<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- filament/filament (FILAMENT) - v4
- laravel/framework (LARAVEL) - v12
- laravel/nightwatch (NIGHTWATCH) - v1
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v3
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- rector/rector (RECTOR) - v2
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v4
## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`
=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel file structure.
- This is perfectly fine and recommended by Laravel. Follow the existing structure from Laravel 10. We do not need to migrate to the new Laravel structure unless the user explicitly requests it.

## Laravel 10 Structure

- Middleware typically lives in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration happens in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule register in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.
=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
=== ralphjsmit/guidelines rules ===

# Coding standards

- When creating a new branch, it MUST start with `rjs/`. This applies also to renaming existing branches.
- Do NOT run Laravel Pint or any other code linter/formatter. This is always automatically performed on CI.
- When writing headings, do not capitalize each word.
- Always launch Opus subagents.
- When committing, NEVER use any "Generated by Claude" or "Generated by Cursor" bylines in the commit message.

# PHP standards

Follow PSR-1, PSR-2, and PSR-12. **Follow Laravel conventions first.**

## Naming and types

- camelCase for non-public-facing strings, PascalCase for enum values
- Short nullable: `?string` not `string|null`
- Always specify `void` return types
- Use string interpolation over concatenation

## Truthy checks

- Use truthy/falsy directly: `if ($items)` not `if (count($items) > 0)`
- Never use `empty()` — rely on falsy instead

## Class structure

- Typed properties, not docblocks
- Constructor property promotion when all properties can be promoted
- One trait per line
- Only `public` or `protected` methods — NEVER `private`
- Constructor promotion: each property on its own line, even with one property:

```php
public function __construct(
    protected AuthenticateSatisRequest $request
) {}
```

## Docblocks

- Omit docblocks for fully type-hinted methods (unless description needed)
- Always import classnames — never fully qualified names in docblocks
- Always format over 3 lines, never single-line (`/** @var string */` is wrong, use 3 lines)
- Most common type first in multi-type docblocks
- If one param needs docblock, document all params
- Document iterables with generics: `Collection<int, User>`, `array<int, MyObject>`
- Array shapes on multiple lines: `array{ first: SomeClass, second: SomeClass, }`

## Control flow

- Happy path last: handle errors first, success case last
- Avoid `else`: use early returns
- Always use curly brackets, even for single statements
- Multi-line ternaries: each part on its own line

## Functions

- ALWAYS put function definitions on one line, never span across multiple lines, regardless of argument count:

```php
public function execute(Leg $leg, AppTrackingType $appTrackingType, string $description, ?string $url, Model $trackable): AppTracking
```

## Method chaining

- When chaining 2+ methods, put each `->` call on its own line:

```php
Notification::make()
    ->title('Satis build queued')
    ->success()
    ->send();
```

## Comments and whitespace

- Avoid comments — write expressive code instead
- Add blank lines between statements for readability
- No extra empty lines between `{}` brackets

# Laravel conventions

## Routes

- URLs: kebab-case (`/open-source`), route names: kebab-case, parameters: camelCase (`{userId}`)
- Use tuple notation: `[Controller::class, 'method']`

## Controllers

- Plural resource names (`PostController`), stick to CRUD methods
- Extract new controllers for non-CRUD actions

## Configuration

- Config files: kebab-case, config keys: snake_case
- Add service configs to `config/services.php`, don't create new files
- Use `config()` helper, never `env()` outside config files

## Artisan commands

- Names: kebab-case (`delete-old-records`)
- Put output BEFORE processing each item (easier debugging)

## Migrations

- NEVER use `cascadeOnDelete()` or `cascadeOnUpdate()`. Only use `constrained()`, and defer cascade logic to an observer.

## Validation

- Use array notation for rules: `'email' => ['required', 'email']`

## Blade templates

- Indent with 4 spaces, one space after control structures: `@if ($condition)`

## Authorization

- Policies use camelCase, CRUD words, `view` instead of `show`

## Translations

- Use `__()` over `@lang`

## Strings

- NEVER use PHP string functions (`str_contains()`, `strtolower()`). Always use `Str::contains()`, `Str::lower()` etc.
- For chained operations, use `str()` helper

## Carbon

- NEVER use `Carbon\Carbon`. Always use `Illuminate\Support\Carbon`.

## Collections

- Only use `Collection` when returning a collection that must be wrapped, or when chaining multiple operations
- Use PHP array functions for single operations

## Eloquent queries

- Always wrap `or` conditions in a `->where()` closure
- Always `return` nested query closures (return type `Builder`, not `void`)
- Use `->whereBelongsTo($model)` instead of hard-coding relation keys
- Use `$model->getKey()` not `$model->id`, `->getQualifiedKeyName()` not `->qualifyColumn('id')`

## Naming conventions

- Classes: PascalCase, methods/variables: camelCase, routes: kebab-case
- Config files: kebab-case, config keys: snake_case, artisan commands: kebab-case

## File structure

- Controllers: `PostController`, views: `open-source.blade.php`
- Jobs: `CreateUserJob`, events: `UserCreated`, listeners: `SendUserCreatedNotificationListener`
- Commands: `PublishScheduledPostsCommand`, notifications: `UserCreatedNotification`
- Resources: `UserResource`, enums: `OrderStatus`, `BookingType`

## Testing

- Keep test classes in same file when possible
- Descriptive test method names, arrange-act-assert pattern

</laravel-boost-guidelines>
