<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MEDISLOT</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="align-items: flex-start; padding-top: 100px;">
    <div class="bg-gradient"></div>
    
    <div class="container" style="display: block;">
        <!-- Header / Nav -->
        <header class="header">
            <a href="/dashboard" class="logo">
                <div class="logo-icon"></div>
                MEDISLOT
            </a>
            <nav class="nav">
                <span class="nav-link" style="color: #333;">Hello, {{ Auth::user()->full_name }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-nav-register" style="border: none; cursor: pointer;">Logout</button>
                </form>
            </nav>
        </header>

        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-top: 40px;">
            <h2 style="margin-bottom: 20px; color: #2d7a60;">Welcome to your Dashboard</h2>
            <p style="color: #555; line-height: 1.6;">
                You have successfully logged in. 
                <br><br>
                <strong>Email:</strong> {{ Auth::user()->email }}<br>
                <strong>Role:</strong> {{ Auth::user()->role ?? 'User' }}
            </p>
        </div>
    </div>
</body>
</html>
