@extends('shopify-app::layouts.default')

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopify Admin</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://unpkg.com/flowbite@1.7.0/dist/flowbite.js"></script>
  <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
  <!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


  <style type="text/tailwindcss">
    @theme {
      --color-green: #4a9400;
    }
    .fa.text-white::before {
        font-size: 2.5rem !important;
    }
  </style>
</head>

<body class="bg-gray-100">

  {{-- ✅ Navbar Include --}}
  @include('layouts.navbar')

  {{-- 📄 Main Content --}}
  <main class="p-6">

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
                apiKey: '{{ config('shopify-app.api_key') }}',
                host: host,
                forceRedirect: true
            });

            // Set up app bridge actions
            var TitleBar = actions.TitleBar;
            TitleBar.create(app, {
                title: '{{ config('shopify-app.app_name') }}'
            });
        }
    </script>
</body>
</html>
