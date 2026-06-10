<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 5vh; background-color: #f9f9f9; color: #333; }
        .form-container { background: #fff; border: 1px solid #ddd; border-radius: 8px; max-width: 400px; margin: 30px auto; padding: 30px; text-align: left; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .form-group input.error-field { border-color: #e74c3c; background-color: #fdf2f2; }
        .error-message { color: #e74c3c; font-size: 13px; margin-top: 5px; display: block; }
        button { width: 100%; padding: 12px; background-color: #3498db; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #2980b9; }
        .alert-success { background-color: #2ecc71; color: white; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; text-align: center; }
    </style>
</head>

<body>
    <h1>{{ $title }}</h1>
    <p>Powered by Slim 4, PHP-DI, BladeOne, and Pest.</p>

    <div class="form-container">
        @if(session()->getFlash()->has('success'))
            <div class="alert-success">
                {{ session()->getFlash()->get('success')[0] }}
            </div>
        @endif

        <form action="/submit" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}" 
                       class="{{ has_error('name') ? 'error-field' : '' }}" 
                       placeholder="Enter your name">
                
                @if(has_error('name'))
                    <span class="error-message">{{ error('name') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" 
                       name="email" 
                       id="email" 
                       value="{{ old('email') }}" 
                       class="{{ has_error('email') ? 'error-field' : '' }}" 
                       placeholder="Enter your email">
                
                @if(has_error('email'))
                    <span class="error-message">{{ error('email') }}</span>
                @endif
            </div>

            <button type="submit">Submit Form</button>
        </form>
    </div>
</body>

</html>