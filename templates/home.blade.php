<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #475569;
            --border-color: #e2e8f0;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #10b981;
            --code-bg: #1e293b;
            --code-text: #f8fafc;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 1040px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        header {
            text-align: center;
            margin-bottom: 60px;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--text-main);
            letter-spacing: -0.025em;
        }

        header p {
            font-size: 1.15rem;
            color: var(--text-muted);
            margin-top: 0;
        }

        .meta-badges {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .badge {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge-success {
            border-color: #a7f3d0;
            background-color: #ecfdf5;
            color: #065f46;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .card h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card h2 span {
            color: var(--primary);
        }

        .card p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 16px;
        }

        code-block {
            display: block;
            background-color: var(--code-bg);
            color: var(--code-text);
            padding: 12px;
            border-radius: 8px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            text-align: left;
            overflow-x: auto;
            white-space: pre;
        }

        footer {
            text-align: center;
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            font-size: 0.9rem;
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>{{ $title }}</h1>
            <p>A minimal, highly opinionated boilerplate focused on elegant Developer Experience.</p>

            <div class="meta-badges">
                <span class="badge badge-success">
                    Status: Active & Tested
                </span>
                <span class="badge">
                    Route: {{ current_url() }}
                </span>
                <span class="badge">
                    CSRF Token: {{ substr(session('_token'), 0, 12) }}...
                </span>
            </div>
        </header>

        <main class="grid">
            <!-- Card 1: Slim 4 -->
            <div class="card">
                <h2><span>⚡</span> Slim 4 Engine</h2>
                <p>Extended PSR-7 Request and Response wrapper offering intuitive, Laravel-like input and output
                    interfaces.</p>
                <code-block>$input = $request->input('name');
                    return $response->json($data);</code-block>
            </div>

            <!-- Card 2: PHP-DI -->
            <div class="card">
                <h2><span>🔌</span> PHP-DI Container</h2>
                <p>Full support for autowiring and class-attribute dependency injection. Controllers and FormRequests
                    resolve automatically.</p>
                <code-block>#[Inject]
                    private readonly GetNameService $service;</code-block>
            </div>

            <!-- Card 3: Form Requests -->
            <div class="card">
                <h2><span>🛡️</span> Secure Form Requests</h2>
                <p>Encapsulated validation payloads with built-in immunity against Parameter Overwriting (IDOR) attacks.
                </p>
                <code-block>// Route parameters safely prioritized
                    $raw = [...$raw, ...$routeArgs];</code-block>
            </div>

            <!-- Card 4: Pest PHP -->
            <div class="card">
                <h2><span>🎯</span> Pest Testing</h2>
                <p>Expressive, human-readable test suite with built-in RESTful helpers that naturally handle CSRF tokens
                    and sessions.</p>
                <code-block>$response = $this->post('/submit', [
                    'email' => 'user@example.com'
                    ]);</code-block>
            </div>

            <!-- Card 5: Method Spoofing -->
            <div class="card">
                <h2><span>🔄</span> RESTful Method Spoofing</h2>
                <p>Support for simulating PUT, PATCH, and DELETE requests from standard HTML forms using the built-in
                    Method Override middleware.</p>
                <code-block>&lt;form action="/update" method="POST"&gt;
                    @csrf
                    @method('PUT')
                    &lt;/form&gt;</code-block>
            </div>

            <!-- Card 6: Rich DX Helpers -->
            <div class="card">
                <h2><span>🧰</span> Global Helpers</h2>
                <p>Convenient access to dynamic state values, URL routing, and active sessions anywhere in your
                    application context.</p>
                <code-block>$url = route('profile', ['id' => 5]);
                    $prev = previous_url();
                    return $response->back();</code-block>
            </div>
        </main>

        <footer>
            <p>Crafted for micro-framework lovers. Powered by Slim 4, PHP-DI, BladeOne, and Pest PHP.</p>
        </footer>
    </div>
</body>

</html>
