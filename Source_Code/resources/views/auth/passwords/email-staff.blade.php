<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h2>Forgot Password</h2>

    @if (session('status'))
        <div style="color:green">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('staff.password.email') }}">
        @csrf

        <label>Email</label>
        <input type="email" name="email" required value="{{ old('email') }}">

        @error('email')
            <div style="color:red">{{ $message }}</div>
        @enderror

        <button type="submit">Send reset link</button>
    </form>

    <p><a href="{{ route('login') }}">Back to login</a></p>
</body>
</html>
