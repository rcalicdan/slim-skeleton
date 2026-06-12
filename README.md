# slim-skeleton

A minimal, opinionated PHP micro-framework skeleton built on Slim 4.
Wraps Slim's raw PSR-7 primitives with a Laravel-inspired developer experience, without pulling in the full Laravel ecosystem.

Whether you're building a traditional web app, an API, or something in between, this skeleton gives you a solid, testable foundation you can shape into any architecture you prefer.

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
- [HTTP Layer](#http-layer)
  - [Request](#request)
  - [Response](#response)
  - [ResponseFactory](#responsefactory)
  - [ValidatedData](#validateddata)
- [Validation](#validation)
  - [Inline Validation](#inline-validation)
  - [FormRequest](#formrequest)
  - [Custom Validation Rules](#custom-validation-rules)
  - [IDOR Protection](#idor-protection)
- [Blade Templating](#blade-templating)
  - [Rendering a View](#rendering-a-view)
  - [Built-in Directives](#built-in-directives)
  - [Custom Compile-time Directives](#custom-compile-time-directives)
  - [Custom Run-time Directives](#custom-run-time-directives)
- [Global Helper Functions](#global-helper-functions)
- [Middleware Reference](#middleware-reference)
  - [BindRequestMiddleware](#bindrequestmiddleware)
  - [CsrfMiddleware](#csrfmiddleware)
  - [WebValidationMiddleware](#webvalidationmiddleware)
  - [ApiValidationMiddleware](#apivalidationmiddleware)
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
│   ├── blade.php             # Template paths, cache mode, custom directives
│   ├── console.php           # Console command bindings
│   ├── container.php         # DI bindings and settings
│   ├── middleware.php        # Global middleware registration
│   ├── routes.php            # Route definitions
│   └── validation.php        # Custom validation rule bindings
├── integrations/             # The framework integration layer; generally leave this alone
│   ├── Commands/
│   │   └── ClearCacheCommand.php
│   ├── Http/
│   │   ├── Exceptions/
│   │   │   └── ValidationException.php
│   │   ├── Middleware/
│   │   │   ├── ApiValidationMiddleware.php
│   │   │   ├── BindRequestMiddleware.php
│   │   │   ├── CsrfMiddleware.php
│   │   │   └── WebValidationMiddleware.php
│   │   ├── FormRequest.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── ResponseFactory.php
│   │   └── ValidatedData.php
│   ├── View/
│   │   ├── Directives/
│   │   │   ├── EndErrorDirective.php
│   │   │   ├── ErrorDirective.php
│   │   │   ├── MethodDirective.php
│   │   │   └── UpperDirective.php
│   │   └── BladeRenderer.php
│   ├── functions.php         # Global helpers (autoloaded)
│   └── Registry.php          # Static container accessor
├── public/
│   └── index.php             # Application entry point
├── templates/                # Blade template files (.blade.php)
├── tests/
│   ├── FrameworkIntegration/ # Skeleton's own integration tests; delete when starting your project
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

Controllers, `FormRequest` subclasses, and single-action handlers are **autowired**, so no manual registration is needed.

---

## Console CLI (Command Line Interface)

The skeleton integrates Symfony Console (`symfony/console`) to allow running CLI commands. It uses the `slim` file in the project's root folder as the application runner.

To run the console, execute it from the root directory:

```bash
# Set execute permissions (Unix/macOS)
chmod +x slim

# Run the console
php slim
```

### Built-in Commands

The skeleton provides a default command to clear accumulated caches:

```bash
php slim cache:clear
```

This command flushes compiled templates in `cache/blade` and compiled DI container files in `var/cache/di`.

### Creating Custom Commands

To create a custom command, extend `Symfony\Component\Console\Command\Command` and define your command's name, description, options, and arguments within the `configure()` method:

```php
namespace App\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExampleCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('example:run')
            ->setDescription('An example command description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('Command executed successfully!');

        return Command::SUCCESS;
    }
}
```

After creating your command class, register it in `config/console.php` to make it accessible to the runner. Because these commands are resolved through the DI container, they support constructor autowiring.

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

Pre-bound services: `PhpSession`, `SessionManagerInterface`, `SessionInterface`, `ResponseFactoryInterface`, `RouteParserInterface`, `BladeOne`, `BladeRenderer`, `ValidationFactory`.

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
        \Integrations\Commands\ClearCacheCommand::class,
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
| `validate` | `(array\|string\|FormRequest $rules): ValidatedData` | See [Validation](#validation) |

---

### Response

`Integrations\Http\Response` extends `Slim\Psr7\Response`.

| Method | Signature | Description |
|---|---|---|
| `json` | `(mixed $data, int $status = 0): self` | JSON body with `Content-Type: application/json`. Status defaults to the current status code when `0`. |
| `html` | `(string $html, int $status = 200): self` | Raw HTML body |
| `view` | `(string $template, array $data = [], int $status = 200): self` | Renders a Blade template |
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

### FormRequest

Create a class extending `FormRequest` for reusable, encapsulated validation. Best for complex forms or when you want to keep controllers thin.

```php
// app/Http/Requests/StoreUserRequest.php
namespace App\Http\Requests;

use Integrations\Http\FormRequest;

class StoreUserRequest extends FormRequest
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

**Using a FormRequest in a controller:**

```php
// Option A: pass the class-string to $request->validate()
public function store(Request $request, Response $response): Response
{
    $validated = $request->validate(StoreUserRequest::class);
    return $response->json($validated->all());
}

// Option B: type-hint it directly; PHP-DI autowires and injects it
public function store(StoreUserRequest $form, Response $response): Response
{
    $validated = $form->validate();
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

Best when you want to reference the rule by name across many FormRequests or inline validations, and when the rule needs injected dependencies like a database connection.

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

// In a FormRequest
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

// In a FormRequest
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

`FormRequest::validate()` automatically strips route segment arguments from the returned `ValidatedData`. This prevents a client from overwriting URL parameters through the request body, a common parameter pollution and IDOR vector.

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

The `tests/FrameworkIntegration/` directory contains tests that verify the skeleton's own wiring: CSRF, middleware, Blade directives, the HTTP layer, and so on. They exist to prove the skeleton works correctly out of the box.

Once you start building your own application, delete this directory:

```bash
rm -rf tests/FrameworkIntegration
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

**Register a test-only route inside a test:**
```php
it('validates and returns data', function () {
    $this->app->post('/test', function (Request $request, Response $response) {
        $validated = $request->validate(['name' => 'required|string|min:2']);
        return $response->json($validated->all());
    });

    $response = $this->post('/test', ['name' => 'Alice']);
    $data = json_decode((string) $response->getBody(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($data['name'])->toBe('Alice');
});
```

**Testing a validation failure on a web route, expecting a redirect:**
```php
it('redirects back with session errors on failed validation', function () {
    $this->app->post('/submit', function (Request $request, Response $response) {
        $request->validate(['email' => 'required|email']);
        return $response->json(['ok' => true]);
    });

    $response = $this->post('/submit', ['email' => 'not-an-email']);

    expect($response->getStatusCode())->toBe(302);

    $session = $this->container->get(SessionInterface::class);
    expect($session->get('errors'))->toHaveKey('email');
});
```

**Testing a validation failure on an API route, expecting 422 JSON:**
```php
it('returns 422 json on api validation failure', function () {
    $this->app->post('/api/users', function (Request $request, Response $response) {
        $request->validate(['email' => 'required|email']);
        return $response->json(['ok' => true]);
    })->add(ApiValidationMiddleware::class);

    $response = $this->request('POST', '/api/users', ['email' => 'bad']);
    $data = json_decode((string) $response->getBody(), true);

    expect($response->getStatusCode())->toBe(422)
        ->and($data['errors'])->toHaveKey('email');
});
```

**Interacting with the session directly in a test:**
```php
it('reads a value the controller stored in session', function () {
    $this->app->post('/login', function (Request $request, Response $response) {
        session()->set('user_id', 42);
        return $response->json(['ok' => true]);
    });

    $this->post('/login', []);

    $session = $this->container->get(SessionInterface::class);
    expect($session->get('user_id'))->toBe(42);
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
```