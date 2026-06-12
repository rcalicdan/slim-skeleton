<?php

declare(strict_types=1);

use function Rcalicdan\ConfigLoader\env;

return [
    /*
    |--------------------------------------------------------------------------
    | Templates & Cache Paths
    |--------------------------------------------------------------------------
    |
    | 'templates_path' defines the directory where raw .blade.php files reside.
    | 'cache_path' is where compiled .bladec PHP templates are stored.
    |
    */
    'templates_path' => __DIR__ . '/../templates',
    'cache_path' => cache_path(__DIR__ . '/../cache/blade'),

    /*
    |--------------------------------------------------------------------------
    | Blade Render Mode
    |--------------------------------------------------------------------------
    |
    | MODE_AUTO: Automatically recompiles the template if the raw file changed.
    | MODE_FAST: Never checks the raw file; serves the cache. (Ideal for prod).
    |
    */
    'mode' => env('APP_ENV', 'local') === 'production'
        ? eftec\bladeone\BladeOne::MODE_FAST
        : eftec\bladeone\BladeOne::MODE_AUTO,

    /**
     * -------------------------------------------------------------------------
     * Custom Compile-Time Directives (Non-RT)
     * -------------------------------------------------------------------------
     * Compile-time directives are evaluated ONLY ONCE when the Blade template
     * is first compiled into a cached raw PHP file (.bladec).
     *
     * How it works:
     * - The callback MUST return a string containing valid PHP code (with PHP tags).
     * - The `$expression` argument is passed as a raw, un-evaluated string of
     *   whatever is inside the parentheses in the template (e.g. "'hello'" or "$var").
     * - This mode is highly optimized because there is zero parsing overhead at runtime.
     *
     * Examples:
     * 1. Simple Closure:
     *    'money' => function ($expression) {
     *        return "<?php echo '$' . number_format((float) {$expression}, 2); ?>";
     *    }
     *
     * 2. Invokable Class (Automatically resolved via DI Container with autowiring):
     *    'money' => \App\View\Directives\MoneyDirective::class
     *
     * Usage in template: @money(1500.5)
     */
    'directives' => [
        'auth' => function () {
            return '<?php if (\Integrations\Auth::check()): ?>';
        },
        'endauth' => function () {
            return '<?php endif; ?>';
        },
        'guest' => function () {
            return '<?php if (\Integrations\Auth::guest()): ?>';
        },
        'endguest' => function () {
            return '<?php endif; ?>';
        },
    ],

    /**
     * -------------------------------------------------------------------------
     * Custom Run-Time Directives (RT)
     * -------------------------------------------------------------------------
     * Run-time directives are evaluated ON EVERY PAGE LOAD during the render
     * phase.
     *
     * How it works:
     * - Instead of returning a string of PHP code, the callback executes the
     *   logic and outputs the content directly (using echo) or returns a value.
     * - Arguments are passed as fully-evaluated PHP variables rather than raw strings.
     * - Ideal for features requiring real-time state, session data, or active DI services.
     *
     * Examples:
     * 1. Simple Closure:
     *    'greet' => function ($username) {
     *        echo "Hello, " . htmlspecialchars((string) $username);
     *    }
     *
     * 2. Invokable Class (Automatically resolved via DI Container with autowiring):
     *    'greet' => \App\View\Directives\GreetDirective::class
     *
     * Usage in template: @greet($user->name)
     */
    'directives_rt' => [
        //
    ],
];
