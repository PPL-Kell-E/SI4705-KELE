<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - MEDISLOT</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

{{-- HEADER --}}
<header class="auth-header">
    <a href="/" class="logo">
        <img src="{{ asset('images/logo.svg') }}" alt="MediSlot Logo" class="logo-img">
        MEDISLOT
    </a>
    <nav class="auth-nav">
        <a href="{{ route('login') }}" class="nav-link active">Sign in</a>
        <a href="{{ route('register') }}" class="btn-nav">Register</a>
    </nav>
</header>

{{-- MAIN --}}
<main class="auth-page">

    {{-- LEFT: tagline --}}
    <div class="auth-left">
        <h1>Sign In to Get Your Appointment Reminders</h1>
        <p>
            If you don't have an account<br>
            you can <a href="{{ route('register') }}">Register here!</a>
        </p>
    </div>

    {{-- CENTER: illustration --}}
    <div class="auth-illustration">
        <img src="{{ asset('images/illustration.png') }}" alt="Medical Illustration">
    </div>

    {{-- RIGHT: form --}}
    <div class="auth-right">
        <form action="{{ route('login') }}" method="POST" class="auth-form">
            @csrf
            <h2>Welcome Back</h2>

            @if ($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <input type="email" name="email" placeholder="Enter Email"
                    value="{{ old('email') }}" required autocomplete="email">
                <span class="icon-circle"><i class="fa-regular fa-envelope"></i></span>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="••••••••"
                    required autocomplete="current-password">
                <span class="icon-circle"><i class="fa-regular fa-eye-slash"></i></span>
            </div>

            <a href="#" class="forgot-password">Recover Password ?</a>

            <button type="submit" class="btn-submit">Sign In</button>
        </form>
    </div>

</main>

</body>
</html>
