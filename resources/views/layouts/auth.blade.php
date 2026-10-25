<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - InvenTrack Laravel</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>
<div class="auth-wrapper">
    @yield('content')
</div>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
