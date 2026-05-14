<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MEDISLOT</title>
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
        <a href="{{ route('login') }}" class="nav-link">Sign in</a>
        <a href="{{ route('register') }}" class="btn-nav">Register</a>
    </nav>
</header>

{{-- MAIN --}}
<main class="auth-page">
<div class="auth-inner">

    {{-- LEFT: tagline --}}
    <div class="auth-left">
        <h1>Sign Up Now to Get Your Appointment Reminders</h1>
        <p>
            if you already have an account<br>
            you can <a href="{{ route('login') }}">Login here!</a>
        </p>
    </div>

    {{-- CENTER: illustration --}}
    <div class="auth-illustration">
        <img src="{{ asset('images/illustration.png') }}" alt="Medical Illustration">
    </div>

    {{-- RIGHT: form --}}
    <div class="auth-right">
        <form action="{{ route('register') }}" method="POST" class="auth-form">
            @csrf
            <h2>Welcome User</h2>

            @if ($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <input type="text" name="full_name" placeholder="Full Name"
                    value="{{ old('full_name') }}" required autocomplete="name">
                <span class="icon-circle"><i class="fa-regular fa-user"></i></span>
            </div>

            <div class="form-group">
                <input type="email" name="email" placeholder="Enter Email"
                    value="{{ old('email') }}" required autocomplete="email">
                <span class="icon-circle"><i class="fa-regular fa-envelope"></i></span>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="••••••••"
                    required autocomplete="new-password">
                <span class="icon-circle"><i class="fa-regular fa-eye-slash"></i></span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="number" name="age" placeholder="Age"
                        min="1" max="120" value="{{ old('age') }}">
                    <span class="icon-circle"><i class="fa-solid fa-hashtag"></i></span>
                </div>
                <div class="form-group">
                    <select name="gender">
                        <option value="" disabled selected>Gender</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    <span class="icon-circle"><i class="fa-solid fa-chevron-down"></i></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="number" name="height" placeholder="Height (cm)"
                        min="50" max="250" value="{{ old('height') }}">
                    <span class="icon-circle"><i class="fa-solid fa-ruler-vertical"></i></span>
                </div>
                <div class="form-group">
                    <input type="number" name="weight" placeholder="Weight (kg)"
                        min="1" max="300" value="{{ old('weight') }}">
                    <span class="icon-circle"><i class="fa-solid fa-weight-scale"></i></span>
                </div>
            </div>

            <a href="#" class="forgot-password">Having Problem ?</a>

            <button type="submit" class="btn-submit">Register</button>
        </form>
    </div>

</div>
</main>

</body>
</html>
