<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Maya Sree Fashion </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- <link rel="favicon" href="{{ asset('/profile/logo.png') }}"> -->
    <link rel="shortcut icon" href="/asset/profile/logo.png" type="image/x-icon">

    <!-- Preload Logo and Silk Texture for instant Splash Screen display -->
    <link rel="preload" href="/asset/profile/logo.png" as="image">
    <link rel="preload" href="/asset/profile/white_silk_bg.png" as="image">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div id="app"></div>
</body>

</html>