<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>WellMeadows Hospital</title>

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- EXTERNAL CSS -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

</head>

<body>

    <div class="hero">

        <div class="overlay"></div>

        <!-- LOGIN BOX -->
        <div class="login-box">

            <div class="logo-wrapper">
                <img src="{{ asset('images/logo-green-2.png') }}" class="logos">
            </div>

            <h2>Welcome Back</h2>
            <p>Please login to continue</p>

            <!-- LARAVEL LOGIN -->
            <form method="POST" action="{{ route('staff.login') }}">
                @csrf

                <!-- EMAIL -->
                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >

                <!-- PASSWORD -->
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >
                 <div class="login-options">

                    <label class="remember-me">
                         <input type="checkbox" name="remember" value="1">
                             Remember me
                    </label>

                   <a href="{{ route('password.request') }}" class="forgot-password">
                            Forgot password?
                   </a>

                </div>

                <!-- LOGIN BUTTON -->
                <div class="buttons">
                    <button type="submit">
                        Login
                    </button>
                </div>


                <!-- ERROR -->
                @error('email')
                    <p class="error">
                        {{ $message }}
                    </p>
                @enderror

            </form>

        </div>

        <!-- BRAND -->
        <div class="brand-name">
            Well<span>Meadows</span>
        </div>

        <!-- TITLE -->
        <div class="title-box">

            <h1>
                Your Partner in<br>
                Better Healthcare
            </h1>

            <p>
                Smart solutions for efficient and compassionate healthcare management.
            </p>

        </div>

    </div>

</body>
</html>
