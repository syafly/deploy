<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="websocket-url" content="{{ config('services.nodejs.ws_url') }}">

    <title>@yield('title', 'Sistem Absensi')</title>

    @vite('resources/app/index.css')

    @stack('styles')
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <div class="content-wrapper" id="contentWrapper">
        @include('partials/navbar')

        <div class="container mt-4">
            @yield('content')
        </div>

        @include('partials/footer')
    </div>

    @include('partials/sidebar')
    @include('modal/modal-crud-kelas')

    @vite('resources/app/index.js')

    @stack('scripts')
</body>
</html>