<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pranav Diagnostics Centre</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ asset('storage/meta_logo.png') }}">
    <meta property="og:image" content="{{ asset('storage/meta_logo.png') }}">
    <meta name="twitter:image" content="{{ asset('storage/meta_logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-gray-100">
    <div id="app">
        <router-view></router-view>
    </div>
</body>

</html>