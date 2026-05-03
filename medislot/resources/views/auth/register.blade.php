<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MEDISLOT</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
                <a href="{{ route('login') }}" class="nav-link">Sign in</a>
                <a href="{{ route('register') }}" class="btn-nav-register" style="background-color: #2d7a60;">Register</a>
            </nav>
        </header>

        <div class="content-wrapper">
            <!-- Left Side -->
            <div class="left-content">
                <h1>Sign Up Now to Get Your Appointment Reminders</h1>
                <p>Already have an account?<br>you can <a href="{{ route('login') }}">Sign In here!</a></p>
                
                <img src="https://img.freepik.com/free-vector/doctor-patient-measuring-blood-pressure_74855-6232.jpg" alt="Medical Illustration" class="illustration" style="border-radius: 20px; mix-blend-mode: multiply;">
            </div>

            <!-- Right Side (Form) -->
            <div class="right-content">
                <form action="/register" method="POST" class="auth-form">
                    @csrf
                    <h2>Create Account</h2>

                    @if ($errors->any())
                        <div class="alert alert-error">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="form-group">
                        <input type="text" name="full_name" placeholder="Full Name" value="{{ old('full_name') }}" required>
                        <i class="fa-regular fa-user icon-right"></i>
                    </div>

                    <div class="form-group">
                        <input type="email" name="email" placeholder="Enter Email" value="{{ old('email') }}" required>
                        <i class="fa-regular fa-envelope icon-right"></i>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password (min 8 characters)" required>
                        <i class="fa-solid fa-lock icon-right"></i>
                    </div>

                    <button type="submit" class="btn-submit" style="margin-top: 20px;">Register</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
