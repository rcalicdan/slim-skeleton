# slim-skeleton

A minimal, opinionated PHP micro-framework skeleton built on Slim 4.
Wraps Slim's raw PSR-7 primitives with a Laravel-inspired developer experience, without pulling in the full Laravel ecosystem.

Whether you're building a traditional web app, an API, or something in between, this skeleton gives you a solid, testable, and fully statically-analyzable foundation.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [Directory Structure](#directory-structure)
- [Choosing Your Architecture](#choosing-your-architecture)
- [Architecture Deep-Dive](#architecture-deep-dive)
  - [Bootstrap Flow](#bootstrap-flow)
  - [Middleware Execution Order](#middleware-execution-order)
  - [DI Container and Registry](#di-container-and-registry)
- [Console CLI (Command Line Interface)](#console-cli-command-line-interface)
  - [Built-in Commands](#built-in-commands)
  - [Creating Custom Commands](#creating-custom-commands)
- [Configuration](#configuration)
  - [container.php](#containerphp)
  - [middleware.php](#middlewarephp)
  - [routes.php](#routesphp)
  - [blade.php](#bladephp)
  - [console.php](#consolephp)
  - [validation.php](#validationphp)
  - [session.php](#sessionphp)
  - [auth.php](#authphp)
- [Database \& Hibla Integration](#database--hibla-integration)
  - [Using the Facade](#using-the-facade)
  - [Dependency Injection \& Multi-Connection](#dependency-injection--multi-connection)
- [Authentication \& Security](#authentication--security)
  - [Crypt & Key Generation](#crypt--key-generation)
  - [The Auth Facade](#the-auth-facade)
- [HTTP Layer](#http-layer)
  - [Request](#request)
  - [Response](#response)
  - [ResponseFactory](#responsefactory)
  - [ValidatedData](#validateddata)
- [Validation](#validation)
  - [Inline Validation](#inline-validation)
  - [RequestValidator](#requestvalidator)
  - [Custom Validation Rules](#custom-validation-rules)
  - [IDOR Protection](#idor-protection)
- [Blade Templating](#blade-templating)
  - [Rendering a View](#rendering-a-view)
  - [Built-in Directives](#built-in-directives)
  - [Custom Compile-time Directives](#custom-compile-time-directives)
  - [Custom Run-time Directives](#custom-run-time-directives)
- [Error Handling](#error-handling)
- [Global Helper Functions](#global-helper-functions)
- [Middleware Reference](#middleware-reference)
  - [BindRequestMiddleware](#bindrequestmiddleware)
  - [CsrfMiddleware](#csrfmiddleware)
  - [WebValidationMiddleware](#webvalidationmiddleware)
  - [ApiValidationMiddleware](#apivalidationmiddleware)
  - [RateLimitMiddleware](#ratelimitmiddleware)
  - [AuthMiddleware & GuestMiddleware](#authmiddleware--guestmiddleware)
- [Session](#session)
- [Testing](#testing)
  - [Before You Start: Clean Up the Skeleton Tests](#before-you-start-clean-up-the-skeleton-tests)
  - [TestCase HTTP Helpers](#testcase-http-helpers)
  - [Writing Tests](#writing-tests)

---

## Tech Stack

| Package | Role |
|---|---|
| `slim/slim` ^4 | Router and middleware pipeline |
| `slim/psr7` | PSR-7 HTTP message implementation |
| `php-di/php-di` ^7 | DI container with autowiring |
| `eftec/bladeone` | Blade-compatible template engine |
| `hiblaphp/database` | Fully asynchronous, Fiber-based database layer |
| `symfony/console` ^7.1 | CLI engine |
| `rcalicdan/config-loader` | `config()` and `env()` helpers |
| `odan/session` | PSR-15 session management |
| `somnambulist/validation` | Validation factory and rules |
| `pestphp/pest` ^4 | Test framework |
| `laravel/pint` | Code style (PSR-12 preset) |
| `phpstan/phpstan` | Static analysis (level 6) |

---

## Getting Started

```bash
composer create-project rcalicdan/slim-skeleton my-app
cd my-app
cp .env.example .env

# Generate your secure application encryption key
php slim key:generate

php -S localhost:8000 -t public
```

Visit `http://localhost:8000` and you should see the skeleton welcome page.

**Environment variables** (`.env`):

```ini
APP_ENV=local       # Set to "production" to enable container and blade caching
APP_DEBUG=true      # Set to false in production
```

---

## Directory Structure

```
slim-skeleton/
├── app/
│   └── Controllers/          # Your application controllers (or handlers, actions, etc.)
├── config/
│   ├── auth.php              # Auth redirects, table names, and bcrypt rounds
│   ├── blade.php             # Template paths, cache mode, custom directives
│   ├── console.php           # Console command bindings
│   ├── container.php         # DI bindings and settings
│   ├── hibla-database.php    # Database connection pool configurations
│   ├── hibla-migrations.php  # Schema migration configurations
│   ├── hibla-seeders.php     # Schema seeder configurations
│   ├── middleware.php        # Global middleware registration
│   ├── routes.php            # Route definitions
│   ├── session.php           # Session drivers, lifetimes, and cookie security
│   └── validation.php        # Custom validation rule bindings
├── integrations/             # The framework integration layer; generally leave this alone
│   ├── Commands/
│   │   ├── ClearCacheCommand.php
│   │   └── GenerateKeyCommand.php
│   ├── Http/
│   │   ├── Exceptions/
│   │   │   └── ValidationException.php
│   │   ├── Handlers/
│   │   │   └── HttpErrorHandler.php
│   │   ├── Middleware/
│   │   │   ├── ApiValidationMiddleware.php
│   │   │   ├── AuthMiddleware.php
│   │   │   ├── BindRequestMiddleware.php
│   │   │   ├── CsrfMiddleware.php
│   │   │   ├── GuestMiddleware.php
│   │   │   ├── RateLimitMiddleware.php
│   │   │   └── WebValidationMiddleware.php
│   │   ├── RequestValidator.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── ResponseFactory.php
│   │   └── ValidatedData.php
│   ├── Session/
│   │   └── DatabaseSessionHandler.php
│   ├── View/
│   │   ├── Directives/
│   │   │   ├── EndErrorDirective.php
│   │   │   ├── EndSessionDirective.php
│   │   │   ├── ErrorDirective.php
│   │   │   ├── MethodDirective.php
│   │   │   ├── SessionDirective.php
│   │   │   └── UpperDirective.php
│   │   └── BladeRenderer.php
│   ├── Auth.php              # Static authentication facade
│   ├── Crypt.php             # AES-256-GCM Encryption handler
│   ├── functions.php         # Global helpers (autoloaded)
│   └── Registry.php          # Static container accessor
├── public/
│   └── index.php             # Application entry point
├── templates/                # Blade template files (.blade.php)
│   └── errors/               # Custom Blade error pages (404, 429, default, etc.)
├── tests/
│   ├── Feature/              # Integration and feature tests
│   ├── Integration/          # View and validation logic tests
│   ├── Pest.php
│   └── TestCase.php          # Base test case with HTTP helpers; keep this
├── slim                      # CLI Application Runner
└── .env
```

The `app/` directory is yours entirely. The `integrations/` layer is the glue between Slim and your application. You typically don't need to modify it unless you're extending the framework itself.

---

## Choosing Your Architecture

The skeleton doesn't lock you into MVC. The `app/` directory is a blank slate. Here are the most common patterns and how they fit.

### MVC (Model-View-Controller)

The default approach. Controllers handle requests, Blade templates handle views, and your models live wherever makes sense (Eloquent, Doctrine, plain classes, etc.).

```
app/
├── Controllers/
│   └── UserController.php
├── Models/
│   └── User.php
└── Services/
    └── UserService.php
```

```php
// config/routes.php
$app->get('/users/{id}', [UserController::class, 'show']);
$app->post('/users', [UserController::class, 'store']);
```

---

### ADR (Action-Domain-Responder)

One class per user action. Keeps each handler small and focused. PHP-DI's autowiring resolves dependencies automatically, so no manual registration is needed.

```
app/
├── Actions/
│   └── User/
│       ├── ShowUserAction.php
│       ├── StoreUserAction.php
│       └── DeleteUserAction.php
├── Domain/
│   └── User/
│       ├── UserRepository.php
│       └── UserService.php
└── Responders/
    └── UserResponder.php
```

```php
// config/routes.php
$app->get('/users/{id}', ShowUserAction::class);
$app->post('/users', StoreUserAction::class);
$app->delete('/users/{id}', DeleteUserAction::class);

// app/Actions/User/ShowUserAction.php
class ShowUserAction
{
    public function __construct(private readonly UserRepository $users) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $user = $this->users->findOrFail($request->route('id'));
        return $response->view('users.show', compact('user'));
    }
}
```

---

### Service-Oriented / API-only

Skip Blade entirely and return JSON from every route. Useful when this app is a backend for a JavaScript frontend.

```
app/
├── Controllers/
│   └── Api/
│       └── UserController.php
└── Services/
    └── UserService.php
```

```php
return $response->json([
    'data' => $user,
    'meta' => ['version' => '1.0'],
]);
```

Swap `WebValidationMiddleware` for `ApiValidationMiddleware` globally in `config/middleware.php` and you're done.

---

### Functional / Closure-based

Great for very small apps or rapid prototyping. Define everything inline in `config/routes.php`.

```php
$app->get('/ping', function (Request $request, Response $response): Response {
    return $response->json(['pong' => true]);
});

$app->post('/echo', function (Request $request, Response $response): Response {
    $validated = $request->validate(['message' => 'required|string']);
    return $response->json($validated->all());
});
```

---

The skeleton provides the plumbing. What you build on top is entirely up to you.

---

## Architecture Deep-Dive

### Bootstrap Flow

`public/index.php` is the single entry point. The startup sequence runs as follows:

```
1. Build PHP-DI ContainerBuilder
     └── Load settings from config/container.php
     └── Optionally enable compiled container (production)
2. Registry::set($container)         ← makes container globally accessible
3. AppFactory::create($responseFactory)
4. $container->set(App::class, $app)
5. BladeRenderer::init(...)          ← initializes Blade renderer singleton
6. Load config/middleware.php        ← registers global middleware
7. Load config/routes.php            ← registers routes
8. Request::createFromGlobals()      ← wraps PHP superglobals
9. $app->run($request)
```

In **production** (`APP_ENV=production`):
- PHP-DI compiles the container to `var/cache/di` for a faster boot.
- BladeOne uses `MODE_FAST`, skipping file change checks on templates.

In **local**:
- No container compilation.
- BladeOne uses `MODE_AUTO`, recompiling templates whenever they change.

---

### Middleware Execution Order

Slim processes middleware in LIFO (Last In, First Out) order. The middleware added last in `config/middleware.php` runs first when a request arrives.

```
Registration order (in middleware.php)     Actual execution order
─────────────────────────────────────      ───────────────────────────────────────
addBodyParsingMiddleware()                 ErrorMiddleware              (outermost)
RoutingMiddleware                            └── SessionStartMiddleware
MethodOverrideMiddleware                         └── CsrfMiddleware
BindRequestMiddleware                                └── WebValidationMiddleware
WebValidationMiddleware                                  └── BindRequestMiddleware
CsrfMiddleware                                               └── MethodOverrideMiddleware
SessionStartMiddleware                                           └── RoutingMiddleware
addErrorMiddleware()           ← last                                └── Controller
```

Why this order matters:

- `SessionStart` before `Csrf`, because CSRF reads and writes the session token.
- `MethodOverride` before `Routing`, so the router sees the spoofed method (`PUT`/`DELETE`) rather than the raw `POST`.
- `BindRequest` after `MethodOverride`, binding the already-corrected request to the container.
- `ErrorMiddleware` outermost, catching any unhandled exception from the entire pipeline.

---

### DI Container and Registry

PHP-DI is the container. Bindings live in `config/container.php` under `dependency_map`.

`Registry` is a static accessor that holds the container instance. It exists so that global helper functions, which can't receive injected arguments, can still reach container-managed services.

```php
// Stored once in public/index.php
Registry::set($container);

// Accessible anywhere
$container = Registry::get();
$session   = $container->get(SessionInterface::class);
```

> Prefer constructor injection in controllers and services. Only reach for `Registry::get()` in global functions or static contexts where injection isn't possible.

Controllers, `RequestValidator` subclasses, and single-action handlers are **autowired**, so no manual registration is needed.

---

## Console CLI (Command Line Interface)

The skeleton integrates Symfony Console (`symfony/console`) to allow running CLI commands. It uses the `slim` executable in the project's root folder as the application runner.

Because the `slim` runner boots your application's PHP-DI container before executing, **all console commands have full access to your DI-registered services, settings, and database connection pools.**

To run the console, execute it from the root directory:

```bash
# Set execute permissions (Unix/macOS)
chmod +x slim

# Run the console
php slim
```

### Built-in & Integrated Commands

Within this skeleton, you do not need to use Hibla's standalone `./vendor/bin/hibla-db` binary. All schema and migration utilities are merged directly into your unified `php slim` entry point:

| Command | Description |
|---|---|
| **Framework Utility** | |
| `key:generate` | Generate a 32-byte AES encryption key and save it to `.env`. |
| `cache:clear` | Flush both the PHP-DI container and the BladeOne compilation caches. |
| **Hibla Database Migrations** | |
| `migrate` | Run all pending database migrations. |
| `migrate:rollback` | Roll back the last batch of migrations. |
| `migrate:reset` | Roll back all migrations. |
| `migrate:refresh` | Reset and re-run all migrations. |
| `migrate:fresh` | Drop all database tables and re-run migrations from scratch. |
| `migrate:status` | Show the execution status of every migration. |
| **Hibla Code Generation** | |
| `make:migration <name>` | Generate a new schema migration file under `database/migrations/`. |
| `make:seeder <name>` | Generate a new database seeder file under `database/seeders/`. |
| **Hibla Seeders & Utilities** | |
| `db:seed` | Run database seeders (runs root `DatabaseSeeder` if present). |
| `schema:dump` | Dump the current database schema to a SQL file. |
| `status` | Show database config resolution status. |
| `publish:templates` | Publish pagination templates to your resources path. |
```

---

## Configuration

All config files return a plain PHP array (or a callable for middleware/routes), accessed via the `config('file.key')` helper.

### container.php

Controls PHP-DI settings and all explicit service bindings.

```php
'settings' => [
    'autowire'       => true,   // Enable autowiring
    'use_attributes' => true,   // Enable #[Inject] attribute
    'cache_path'     => null,   // Point to a path in production to compile the container
],

'dependency_map' => [
    // Add your own service bindings here
    MyService::class => function (ContainerInterface $c) {
        return new MyService($c->get(SomeDependency::class));
    },
],
```

Pre-bound services: `PhpSession`, `SessionManagerInterface`, `SessionInterface`, `ResponseFactoryInterface`, `RouteParserInterface`, `BladeOne`, `BladeRenderer`, `ValidationFactory`, `DatabaseConnectionInterface`.

---

### middleware.php

Returns `function(App $app): void`. Add your own global middleware here, keeping LIFO order in mind.

```php
// Runs after routing but before the controller:
$app->add(MyCustomMiddleware::class); // Add this before the BindRequestMiddleware line
```

---

### routes.php

Returns `function(App $app): void`.

```php
return function (App $app): void {
    $app->get('/', [HomeController::class, 'index']);

    // Named route
    $app->get('/users/{id}', [UserController::class, 'show'])->setName('users.show');

    // Route group
    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('/ping', PingController::class);
    })->add(ApiValidationMiddleware::class);
};
```

---

### blade.php

| Key | Description |
|---|---|
| `templates_path` | Where `.blade.php` files live (`templates/` by default) |
| `cache_path` | Where compiled `.bladec` files are stored |
| `mode` | `MODE_AUTO` (local) / `MODE_FAST` (production) |
| `directives` | Compile-time directives as `['name' => callable\|class-string]` |
| `directives_rt` | Run-time directives as `['name' => callable\|class-string]` |

---

### console.php

Register all custom CLI commands resolved via the DI Container.

```php
return [
    'commands' => [
        // \App\Console\Commands\MyCommand::class,
    ],
];
```

---

### validation.php

Register custom validation rule classes to use as strings in rule arrays:

```php
'rules' => [
    'strong_password' => App\Rules\StrongPasswordRule::class,
],
```

The rule class is resolved from the DI container, so constructor injection works. See [Custom Validation Rules](#custom-validation-rules) for full usage.

---

### session.php

Configures session lifetime, cookie settings, and the session driver (`php` or `database`). You can easily switch from the native `php` file driver to a high-performance, non-blocking `database` driver by setting `SESSION_DRIVER=database` in your `.env` file (requires running the sessions table migration).

---

### auth.php

Configures the authentication table, primary key, session key, bcrypt rounds, and default redirect paths used by the authentication middlewares.

---

## Database & Hibla Integration

The skeleton integrates Hibla Database (`hiblaphp/database`), a fully asynchronous, framework-agnostic database layer built natively on top of PHP Fibers and non-blocking socket streams. It features an expressive query builder, connection pooling, and full migration/seeding CLI support.

For complete documentation on the Query Builder, Schema Manager, and writing Migrations/Seeders, please refer directly to the [Hibla Database Repository](https://github.com/hiblaphp/database).

### Using the Facade (Recommended for Simple Cases)

For rapid prototyping, simple controllers, or closure routes, use Hibla's static `DB` facade. It automatically bootstraps the database connection pool on its first invocation:

```php
use Hibla\QueryBuilder\DB;
use function Hibla\await;

$users = await(DB::table('users')->where('active', true)->get());
```

### Dependency Injection & Multi-Connection (For Testable Architectures)

For highly testable systems or architectures utilizing multiple database connection pools, register the connections in your DI container to autowire them.

#### Step 1: Bind Connections in `config/container.php`

```php
use Hibla\QueryBuilder\Interfaces\DatabaseConnectionInterface;

'dependency_map' => [
    // Default Connection Pool
    DatabaseConnectionInterface::class => function () {
        return \Hibla\QueryBuilder\DB::connection();
    },

    // Secondary Connection Pool (e.g. PostgreSQL analytics)
    'db.pgsql' => function () {
        return \Hibla\QueryBuilder\DB::connection('pgsql');
    },
],
```

#### Step 2: Constructor Injection with `#[Inject]`

You can now inject specific connection instances into your class constructors. PHP-DI autowires the interfaces and handles parameter resolution:

```php
namespace App\Controllers;

use DI\Attribute\Inject;
use Hibla\QueryBuilder\Interfaces\DatabaseConnectionInterface;
use Integrations\Http\Request;
use Integrations\Http\Response;
use function Hibla\await;

class AnalyticsController
{
    public function __construct(
        // Autowires the default database connection
        private readonly DatabaseConnectionInterface $db,

        // Injects the custom pgsql connection pool
        #[Inject('db.pgsql')]
        private readonly DatabaseConnectionInterface $analyticsDb
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $localUser = await($this->db->table('users')->find(5));
        $analytics = await($this->analyticsDb->table('events')->where('user_id', 5)->get());

        return $response->json([
            'user'  => $localUser,
            'stats' => $analytics,
        ]);
    }
}
```

### Schema Migrations & Seeding

Since Hibla's commands are natively registered inside the `slim` entry point, managing your database lifecycle is seamless. Always run migrations and code generators via the local `slim` executable:

```bash
# 1. Generate a migration
php slim make:migration create_notes_table

# 2. Run the migration
php slim migrate

# 3. Check migration statuses across connections
php slim migrate:status --all

# 4. Generate and run a database seeder
php slim make:seeder NoteSeeder
php slim db:seed --class=NoteSeeder
```

---

## Authentication & Security

The skeleton provides a robust, decoupled authentication layer using a static `Auth` facade, AES-256-GCM encrypted sessions, and dynamic redirects.

### Crypt & Key Generation
Session IDs are encrypted before being written to the session storage to prevent tampering and session hijacking. Generate your secure application key using the CLI:

```bash
php slim key:generate
```

### The Auth Facade
Use the `Integrations\Auth` class to manage authentication state cleanly. Because `Auth::login()` expects a verified User ID (rather than an email and password), it can be used natively for Password Logins, Magic Links, or OAuth implementations!

```php
use Integrations\Auth;

Auth::login($user->id); // Encrypts the ID and stores it in the session
Auth::logout();         // Clears the session

$check = Auth::check(); // bool
$guest = Auth::guest(); // bool

// Fetch the fresh user record asynchronously via Hibla
$user = await(Auth::user());
```

---

## HTTP Layer

### Request

`Integrations\Http\Request` extends `Slim\Psr7\Request`.

#### Static Factories

| Method | Description |
|---|---|
| `Request::createFromGlobals()` | Production use; wraps PHP superglobals |
| `Request::createTestRequest(string $method, string $uri)` | Test use; creates a mock request |

#### Instance Methods

| Method | Signature | Description |
|---|---|---|
| `input` | `(string $key, mixed $default = null): mixed` | Parsed body first, falls back to query string |
| `query` | `(string $key, mixed $default = null): mixed` | Query string only |
| `has` | `(string $key): bool` | True if key exists in body or query, even if empty string |
| `filled` | `(string $key): bool` | True if key exists and is not `null` or `''` |
| `route` | `(string $key, mixed $default = null): mixed` | A route segment parameter like `{id}` |
| `allRouteArgs` | `(): array` | All route segment parameters as `['key' => 'value']` |
| `url` | `(): string` | Current URL without query string |
| `fullUrl` | `(): string` | Current URL including query string |
| `previousUrl` | `(string $fallback = '/'): string` | Value of the `Referer` header |
| `validate` | `(array\|string\|RequestValidator $rules): ValidatedData` | See [Validation](#validation) |

---

### Response

`Integrations\Http\Response` extends `Slim\Psr7\Response`.

| Method | Signature | Description |
|---|---|---|
| `json` | `(mixed $data, int $status = 0): self` | JSON body with `Content-Type: application/json`. Status defaults to the current status code when `0`. |
| `html` | `(string $html, int $status = 0): self` | Raw HTML body |
| `view` | `(string $template, array $data = [], int $status = 0): self` | Renders a Blade template |
| `redirect` | `(string $url, int $status = 302): self` | Redirect to a URL |
| `routeRedirect` | `(string $routeName, array $data = [], array $queryParams = [], int $status = 302): self` | Redirect to a named route |
| `back` | `(string $fallback = '/', int $status = 302): self` | Redirect to the `Referer` URL |

> All response methods return a **new immutable instance**. Always `return` or assign the result.

---

### ResponseFactory

`Integrations\Http\ResponseFactory` implements `ResponseFactoryInterface`.

```php
$response = $factory->createResponse(201, 'Created Successfully');
// Returns Integrations\Http\Response, not Slim's base Response
```

This is bound to `ResponseFactoryInterface` in the container, so Slim always creates your custom `Response` objects internally.

---

### ValidatedData

The immutable return value of any `validate()` call.

| Method | Signature | Description |
|---|---|---|
| `get` | `(string $key, mixed $default = null): mixed` | Single value |
| `has` | `(string $key): bool` | Key existence check |
| `only` | `(string ...$keys): array` | Subset of keys |
| `except` | `(string ...$keys): array` | All keys except those listed |
| `all` | `(): array` | Full validated array |
| `toArray` | `(): array` | Alias of `all()` |

---

## Validation

The validation engine is `somnambulist/validation`. Rules use a pipe-delimited string format like `'required|email|min:5'`, or an array of rule objects.

### Inline Validation

Validate directly on the request object with a rules array. Best for simple, one-off validations in closures or small controllers.

```php
public function store(Request $request, Response $response): Response
{
    $validated = $request->validate([
        'email'    => 'required|email',
        'username' => 'required|min:3|max:30',
        'age'      => 'required|integer|min:18',
    ]);

    $email = $validated->get('email');

    return $response->json($validated->all());
}
```

On failure, `ValidationException` is thrown. `WebValidationMiddleware` catches it for web routes and redirects back with session errors. `ApiValidationMiddleware` catches it for API routes and returns a 422 JSON response.

---

### RequestValidator

Create a class extending `RequestValidator` for reusable, encapsulated validation. Best for complex forms or when you want to keep controllers thin.

```php
// app/Http/Requests/StoreUserValidator.php
namespace App\Http\Requests;

use Integrations\Http\RequestValidator;

class StoreUserValidator extends RequestValidator
{
    public function rules(): array
    {
        return [
            'name'  => 'required|string|min:2',
            'email' => 'required|email',
        ];
    }

    // Optional: custom error messages
    public function messages(): array
    {
        return [
            'email:required' => 'An email address is mandatory.',
        ];
    }

    // Optional: rename fields in error messages
    public function attributes(): array
    {
        return ['email' => 'email address'];
    }

    // Optional: mutate input before validation runs
    public function prepareForValidation(array $data): array
    {
        $data['name'] = trim($data['name'] ?? '');
        return $data;
    }

    // Optional: run logic after validation passes, before data is returned
    public function after(array $validated): array
    {
        $validated['slug'] = strtolower(str_replace(' ', '-', $validated['name']));
        return $validated;
    }
}
```

**Using a RequestValidator in a controller:**

```php
public function store(Request $request, Response $response): Response
{
    // Resolves the RequestValidator from the DI container and validates automatically
    $validated = $request->validate(StoreUserValidator::class);
    
    return $response->json($validated->all());
}
```

**Building conditional rules** using the built-in pass-through helpers (`input()`, `query()`, `route()`, `has()`, `filled()`, `getMethod()`):

```php
public function rules(): array
{
    $rules = ['name' => 'required|string'];

    // Add a rule only when a field is present and non-empty
    if ($this->filled('company')) {
        $rules['tax_id'] = 'required|string';
    }

    // Add a rule based on the HTTP method
    if ($this->getMethod() === 'POST') {
        $rules['email'] = 'required|email';
    }

    // Add a rule based on a route parameter
    if ($this->route('id') !== null) {
        $rules['password'] = 'sometimes|min:8';
    }

    return $rules;
}
```

---

### Custom Validation Rules

There are two ways to use a custom rule and you can mix them freely.

#### Option A: Registered String Rule

Best when you want to reference the rule by name across many RequestValidators or inline validations, and when the rule needs injected dependencies like a database connection.

**Step 1: Create the rule class.**

A rule must implement `Somnambulist\Components\Validation\Contracts\Rule` or extend `Somnambulist\Components\Validation\Rules\AbstractRule`.

```php
// app/Rules/UniqueEmailRule.php
namespace App\Rules;

use Somnambulist\Components\Validation\Rules\AbstractRule;

class UniqueEmailRule extends AbstractRule
{
    protected string $message = 'The :attribute is already taken.';

    public function __construct(private readonly UserRepository $users) {}

    public function check(mixed $value): bool
    {
        return ! $this->users->existsByEmail($value);
    }
}
```

**Step 2: Register it in `config/validation.php`.**

```php
'rules' => [
    'unique_email' => App\Rules\UniqueEmailRule::class,
],
```

**Step 3: Use it anywhere by its string name.**

```php
// Inline on a request
$request->validate([
    'email' => 'required|email|unique_email',
]);

// In a RequestValidator
public function rules(): array
{
    return [
        'email' => 'required|email|unique_email',
    ];
}
```

Because the class is resolved from the DI container, `UserRepository` and any other dependency is injected automatically.

---

#### Option B: Inline Rule Object

Best for one-off rules you don't need to reuse elsewhere, or rules with no dependencies. No registration needed. Just instantiate and pass it directly.

```php
// app/Rules/StrongPasswordRule.php
namespace App\Rules;

use Somnambulist\Components\Validation\Rules\AbstractRule;

class StrongPasswordRule extends AbstractRule
{
    protected string $message = 'The :attribute must contain uppercase, lowercase, and a number.';

    public function check(mixed $value): bool
    {
        return preg_match('/[A-Z]/', $value)
            && preg_match('/[a-z]/', $value)
            && preg_match('/[0-9]/', $value);
    }
}
```

Pass an instance directly in any rule array, mixing string rules and rule objects freely:

```php
use App\Rules\StrongPasswordRule;

// Inline on a request
$request->validate([
    'password' => ['required', 'min:8', new StrongPasswordRule()],
]);

// In a RequestValidator
public function rules(): array
{
    return [
        'password' => ['required', 'min:8', new StrongPasswordRule()],
    ];
}
```

---

#### Comparison

| | String Rule (Option A) | Inline Object (Option B) |
|---|---|---|
| Registration required | Yes (`config/validation.php`) | No |
| DI / constructor injection | Yes, resolved from container | Manual via `new` |
| Reusable across requests | Yes, by name | Yes, but must instantiate each time |
| Best for | Rules with dependencies, used in many places | Simple one-off rules |

---

### IDOR Protection

`RequestValidator::validate()` automatically strips route segment arguments from the returned `ValidatedData`. This prevents a client from overwriting URL parameters through the request body, a common parameter pollution and IDOR vector.

```php
// Route: POST /users/{id}
// Attacker sends body: { "id": 99, "name": "Hacker" }

$validated = $request->validate(UpdateUserRequest::class);

$validated->get('id');   // null, stripped by the framework
$request->route('id');   // '5', the real URL parameter; always use this
```

This is enforced at the framework level. You never have to remember to handle it manually.

---

## Blade Templating

### Rendering a View

**In a controller:**
```php
return $response->view('home', ['title' => 'My App']);
// Renders templates/home.blade.php with $title available
```

**Via global helper, useful outside of controllers:**
```php
$response = blade_view('home', ['title' => 'My App'], $response);
```

Templates live in `templates/` with a `.blade.php` extension. Nested templates use dot notation: `users.show` resolves to `templates/users/show.blade.php`.

---

### Built-in Directives

#### `@csrf`

Outputs a hidden CSRF token input. Required in every HTML form that mutates state.

```blade
<form action="/submit" method="POST">
    @csrf
    ...
</form>
```

Renders as:
```html
<input type='hidden' name='_token' value='abc123...'/>
```

The token is generated by `CsrfMiddleware` on GET requests and stored in the session.

---

#### `@method('VERB')`

Outputs a hidden `_METHOD` input to spoof HTTP verbs from HTML forms. Use this when you need `PUT`, `PATCH`, or `DELETE` from a standard `<form>`.

```blade
<form action="/users/5" method="POST">
    @csrf
    @method('PUT')
    ...
</form>
```

Renders as:
```html
<input type="hidden" name="_METHOD" value="PUT"/>
```

`MethodOverrideMiddleware` reads this field before routing, so your `PUT` and `DELETE` route definitions work as expected.

---

#### `@error('field')` / `@enderror`

Renders content only if a validation error exists for the given field. Sets `$message` to the first error string for that field inside the block.

```blade
<input
    type="email"
    name="email"
    value="{{ old('email') }}"
    class="input @error('email') is-invalid @enderror"
>

@error('email')
    <p class="error-text">{{ $message }}</p>
@enderror
```

---

#### `@session('key')` / `@endsession`

Renders content if a session key (or flash message) exists. The value is automatically extracted to a `$value` variable inside the block.

```blade
@session('success')
    <div class="alert alert-success">{{ $value }}</div>
@endsession
```

---

#### `@auth` / `@endauth` and `@guest` / `@endguest`

Renders content based on the user's authentication state.

```blade
@auth
    <p>Welcome back, {{ await(auth_user())->name }}!</p>
@endauth

@guest
    <a href="/login">Sign In</a>
@endguest
```

---

#### `@upper($expression)`

Compile-time directive. Outputs the expression in uppercase.

```blade
@upper('hello')     {{-- HELLO --}}
@upper($username)   {{-- e.g. JOHN --}}
```

---

### Custom Compile-time Directives

Compile-time directives run once when a template is compiled to cache. The callback returns a string of PHP code.

**Option A: Closure directly in `config/blade.php`:**
```php
'directives' => [
    'money' => function (string $expression): string {
        return "<?php echo '$' . number_format((float) {$expression}, 2); ?>";
    },
],
```

**Option B: Invokable class resolved via DI, supporting constructor injection:**
```php
// config/blade.php
'directives' => [
    'money' => \App\View\Directives\MoneyDirective::class,
],

// app/View/Directives/MoneyDirective.php
class MoneyDirective
{
    public function __invoke(string $expression): string
    {
        return "<?php echo '$' . number_format((float) {$expression}, 2); ?>";
    }
}
```

Usage:
```blade
@money(1500.5)    {{-- $1,500.50 --}}
@money($price)
```

---

### Custom Run-time Directives

Run-time directives execute on every page load. They receive fully-evaluated PHP values and output directly via `echo`.

**Option A: Closure:**
```php
'directives_rt' => [
    'greet' => function (string $username): void {
        echo 'Hello, ' . htmlspecialchars($username);
    },
],
```

**Option B: Invokable class:**
```php
'directives_rt' => [
    'greet' => \App\View\Directives\GreetDirective::class,
],
```

Usage:
```blade
@greet($user->name)
```

> Use compile-time for pure value transformations like formatting and escaping. Use run-time when you need live state: session data, current user, active services, and so on.

---

## Error Handling

The skeleton overrides Slim's default ErrorHandler to provide seamless Blade integration for HTTP errors (like 404, 405, 429, 500).

If an error occurs, the framework automatically looks for a matching template in `templates/errors/` (e.g., `templates/errors/404.blade.php`). If a specific template doesn't exist, it falls back to `templates/errors/default.blade.php`.

API requests automatically receive a structured JSON response instead.

---

## Global Helper Functions

All helpers are autoloaded from `integrations/functions.php`.

| Helper | Signature | Description |
|---|---|---|
| `blade_view` | `(string $template, array $data = [], ?ResponseInterface $response = null): ResponseInterface` | Render a Blade template |
| `cache_path` | `(string $path): string` | Creates the directory if missing and returns the path |
| `session` | `(?string $key = null, mixed $default = null): mixed` | Returns the session instance with no key, or a session value |
| `old` | `(string $key, mixed $default = null): mixed` | Previous form input after a failed validation |
| `error` | `(string $key): ?string` | First error message for a field |
| `has_error` | `(string $key): bool` | True if a field has a validation error |
| `error_all` | `(): array` | All errors as `['field' => 'message']` |
| `route` | `(string $routeName, array $data = [], array $queryParams = []): string` | URL for a named route |
| `current_url` | `(bool $withQuery = false): string` | Current request URL |
| `previous_url` | `(string $fallback = '/'): string` | Referer URL |
| `method_field` | `(string $method): string` | Hidden `_METHOD` input HTML; prefer `@method()` in Blade templates |
| `bcrypt` | `(string $value, ?int $rounds = null): string` | Hash a value using the bcrypt algorithm with dynamically configured cost rounds |
| `auth` | `(): ?array` | Get the authenticated user session array |
| `guest` | `(): bool` | Check if the current user is a guest (unauthenticated) |
| `auth_user` | `(): PromiseInterface<object\|null>` | Asynchronously fetch the fresh user record from the database |

**Common usage in templates:**
```blade
<input name="email" value="{{ old('email', '') }}">

@error('email')
    {{ $message }}
@enderror

<a href="{{ route('users.show', ['id' => $user->id]) }}">Profile</a>
<p>You are at: {{ current_url() }}</p>
```

---

## Middleware Reference

### BindRequestMiddleware

Binds the active `Request` object into the DI container after `MethodOverrideMiddleware` has run. This makes `current_url()`, `previous_url()`, and `$response->back()` work anywhere in the app.

No configuration needed. Already registered globally.

---

### CsrfMiddleware

On **GET / HEAD / OPTIONS** requests, it generates a `_token` in the session if one doesn't exist yet. On **POST / PUT / PATCH / DELETE** requests, it reads `_token` from the parsed body and compares it to the session value, returning a `403 JSON` response on mismatch.

Always use `@csrf` in your forms. The token is included and verified automatically.

For API routes where CSRF isn't relevant, register `ApiValidationMiddleware` at the route group level instead of relying on the global middleware stack.

---

### WebValidationMiddleware

Designed for traditional web routes. When a `ValidationException` is thrown anywhere downstream, it:

1. Stores `errors` in the session as `['field' => 'first error message']`.
2. Stores `old` input in the session for repopulating form fields.
3. Adds a flash message under the `error` key.
4. Redirects back to the `Referer` header, falling back to `/` if none is present.

After a **successful** request, it automatically clears `errors` and `old` from the session.

Access these in templates via `old('field')`, `error('field')`, `has_error('field')`, and `error_all()`.

---

### ApiValidationMiddleware

Designed for API routes. Catches `ValidationException` and returns a structured `422` JSON response instead of redirecting.

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": "The email field is required.",
        "name": "The name field must be at least 2 characters."
    }
}
```

Register it on API route groups:

```php
$app->group('/api', function (RouteCollectorProxy $group) {
    $group->post('/users', [UserController::class, 'store']);
    $group->put('/users/{id}', [UserController::class, 'update']);
})->add(ApiValidationMiddleware::class);
```

---

### RateLimitMiddleware

Provides robust rate limiting using a Rolling Window algorithm stored securely in the session. Supports Content Negotiation (JSON for API, HTML/Blade for web).

```php
$app->post('/login', [AuthController::class, 'login'])
    ->add(new RateLimitMiddleware(requests: 5, window: 60, flashAndRedirect: true));
```

If `flashAndRedirect` is true, web requests are redirected back with a flashed error instead of a hard 429 page. The middleware automatically injects `X-RateLimit-*` and `Retry-After` headers.

---

### AuthMiddleware & GuestMiddleware

Guards routes based on authentication state. `AuthMiddleware` redirects guests to `/login`, and `GuestMiddleware` redirects logged-in users to `/` (configurable in `config/auth.php`).

---

## Session

The session is provided by `odan/session`. In production it uses `PhpSession`. In tests it uses `MemorySession`, so no real PHP session is started.

**Via the `session()` helper:**
```php
session('key');                     // get a value
session()->set('key', 'value');     // set a value
session()->delete('key');           // delete a key
session()->has('key');              // check existence
```

**Via constructor injection:**
```php
use Odan\Session\SessionInterface;

class MyController
{
    public function __construct(private readonly SessionInterface $session) {}

    public function index(Request $request, Response $response): Response
    {
        $userId = $this->session->get('user_id');
        // ...
    }
}
```

**Flash messages:**
```php
// Set a flash message
session()->getFlash()->add('success', 'Your changes were saved.');

// Read it on the next request, after a redirect
$messages = session()->getFlash()->get('success'); // ['Your changes were saved.']
```

---

## Testing

Tests use Pest PHP. Run them with:

```bash
composer test
```

---

### Before You Start: Clean Up the Skeleton Tests

The skeleton includes example integration tests to prove that your router, CSRF, middleware, custom directives, and response factories are working beautifully.

Once you start building your own application, you can safely delete the skeleton's example tests:

```bash
rm tests/Feature/*
rm tests/Integration/*
```

Keep `tests/TestCase.php` and `tests/Pest.php`. Those are your testing foundation. Then write your own tests using whatever structure you prefer:

```
tests/
├── Feature/
│   ├── UserRegistrationTest.php
│   └── AuthenticationTest.php
├── Unit/
│   └── UserServiceTest.php
├── Pest.php
└── TestCase.php   ← keep this
```

---

### TestCase HTTP Helpers

The base `TestCase` in `tests/TestCase.php`:
- Boots a full application instance with a real DI container per test.
- Replaces `PhpSession` with `MemorySession`, so no actual PHP session is started.
- Loads middleware and routes from your config files, making these true integration tests.
- Auto-injects a valid CSRF token on all state-changing requests.

| Method | Description |
|---|---|
| `$this->get(string $path)` | GET request |
| `$this->post(string $path, array $data = [])` | POST; auto-injects `_token` |
| `$this->put(string $path, array $data = [])` | PUT; auto-injects `_token` |
| `$this->patch(string $path, array $data = [])` | PATCH; auto-injects `_token` |
| `$this->delete(string $path, array $data = [])` | DELETE; auto-injects `_token` |
| `$this->request(string $method, string $path, array $data = [])` | Raw request with no CSRF injection |

All helpers return `Integrations\Http\Response`.

---

### Writing Tests

**Basic route test:**
```php
it('shows the user profile page', function () {
    $response = $this->get('/users/1');

    expect($response->getStatusCode())->toBe(200)
        ->and((string) $response->getBody())->toContain('John Doe');
});
```

**Testing method spoofing:**
```php
it('routes spoofed PUT requests correctly', function () {
    $this->app->put('/users/{id}', function (Request $request, Response $response) {
        return $response->json(['id' => $request->route('id')]);
    });

    $response = $this->post('/users/5', ['_METHOD' => 'PUT']);
    $data = json_decode((string) $response->getBody(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($data['id'])->toBe('5');
});