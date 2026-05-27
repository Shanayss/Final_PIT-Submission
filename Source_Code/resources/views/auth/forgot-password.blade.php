<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password</title>

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- EXTERNAL CSS -->
    <link rel="stylesheet" href="{{ asset('css/forgot-password.css') }}">

</head>

<body>

    <div class="message-box">

        <h1>Password Sent</h1>

        <p>
            We have sent it to your email. Please check.
        </p>

        <a href="{{ route('login') }}">
            Back to Login
        </a>

    </div>

</body>
</html>