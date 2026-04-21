<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MEDISLOT</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Load Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="bg-gradient"></div>
    
    <div class="container">
        <!-- Header / Nav -->
        <header class="header">
            <a href="/" class="logo">
                <div class="logo-icon"></div>
                MEDISLOT
            </a>
            <nav class="nav">
                <a href="{{ route('login') }}" class="nav-link active">Sign in</a>
                <a href="{{ route('register') }}" class="btn-nav-register">Register</a>
            </nav>
        </header>

        <div class="content-wrapper">
            <!-- Left Side -->
            <div class="left-content">
                <h1>Sign Up Now to Get Your Appointment Reminders</h1>
                <p>if you don't have an account<br>you can <a href="{{ route('register') }}">Register here!</a></p>
                
                <!-- Illustration Placeholder using generic health SVG from unDraw or similar, or just an img tag if provided later -->
                <img src="https://img.freepik.com/free-vector/doctor-patient-measuring-blood-pressure_74855-6232.jpg" alt="Medical Illustration" class="illustration" style="border-radius: 20px; mix-blend-mode: multiply;">
            </div>

            <!-- Right Side (Form) -->
            <div class="right-content">
                <form action="/login" method="POST" class="auth-form">
                    @csrf
                    <h2>Welcome Back</h2>

                    @if ($errors->any())
                        <div class="alert alert-error">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="form-group">
                        <input type="email" name="email" placeholder="Enter Email" value="{{ old('email') }}" required>
                        <i class="fa-regular fa-envelope icon-right"></i>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" placeholder="••••••••" required>
                        <i class="fa-regular fa-eye-slash icon-right"></i>
                    </div>

                    <a href="#" class="forgot-password">Recover Password ?</a>

                    <button type="submit" class="btn-submit">Sign In</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
