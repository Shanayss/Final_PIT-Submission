<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WellMeadows</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>

<body>

<div class="container">

    <div id="brand" class="brand show">

        <div class="logo">
            <img src="{{ asset('images/logo-green-1.png') }}" alt="logo">
        </div>

        <div class="name">
            <span class="well">Well</span><span class="meadows">Meadows</span>
            <span class="hospital">Hospital</span>
        </div>

    </div>

</div>

<script src="{{ asset('js/welcome.js') }}"></script>

</body>
</html>