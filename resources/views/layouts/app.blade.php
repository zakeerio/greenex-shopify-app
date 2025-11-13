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
  <style type="text/tailwindcss">
    @theme {
      --color-green: #4a9400;
    }
  </style>
</head>

<body class="bg-gray-100">

  {{-- ✅ Navbar Include --}}
  @include('layouts.navbar')

  {{-- 📄 Main Content --}}
  <main class="p-6">
    @yield('content')
    </main>

    @include('layouts.scripts')
</body>
</html>
