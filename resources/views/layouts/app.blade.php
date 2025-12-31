@php
$host = Request::get('host');
@endphp


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopify Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
    <script>
        var AppBridge = window['app-bridge'];
        var actions = AppBridge.actions;
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

    {{-- ✅ Navbar Include --}}
    @include('layouts.navbar')

    {{-- 📄 Main Content --}}
    <main class="p-6">
        @yield('content')
    </main>

    @include('layouts.scripts')

    <!-- Shopify App Bridge Script -->
    <script>
        // Initialize Shopify App Bridge
        var AppBridge = window['app-bridge'];
        var createApp = AppBridge.default;
        var actions = AppBridge.actions;

        // Get host parameter from URL
        var host = new URLSearchParams(window.location.search).get('host');

        if (host) {
            var app = createApp({
                apiKey: '{{ config("shopify-app.api_key") }}',
                host: host,
                forceRedirect: true
            });
            window.app = app;

            // Set up app bridge actions
            var TitleBar = actions.TitleBar;
            TitleBar.create(app, {
                title: '{{ config("shopify-app.app_name") }}'
            });
        }
    </script>
    @stack('scripts')
</body>

</html>