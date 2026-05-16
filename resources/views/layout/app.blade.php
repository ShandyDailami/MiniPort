<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MiniStack @yield('title')</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    @if(!request()->routeIs('login') && !request()->routeIs('register') && !request()->routeIs('forgot-password'))
        @include('components.navbar')
    @endif

    @yield('content')

    @if(!request()->routeIs('login') && !request()->routeIs('register') && !request()->routeIs('forgot-password'))
        @include('components.footer')
    @endif
</body>
</html>
