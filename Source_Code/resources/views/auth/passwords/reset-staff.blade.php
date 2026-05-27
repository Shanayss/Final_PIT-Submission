<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>

    <form method="POST" action="{{ route('staff.password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <label>Email</label>
        <input type="email" name="email" value="{{ $email ?? old('email') }}" required>

        <label>New Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" required>

        @error('email')
            <div style="color:red">{{ $message }}</div>
        @enderror

        <button type="submit">Reset Password</button>
    </form>

    <p><a href="{{ route('login') }}">Back to login</a></p>
</body>
</html>
