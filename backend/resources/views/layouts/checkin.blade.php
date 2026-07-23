<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="@yield('property_favicon', asset('images/logo_aldara.png'))">
    <title>@yield('property_title', 'Check-in Online — ' . config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .step-indicator { display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 2rem; }
        .step { width: 2.5rem; height: 2.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem; }
        .step-active { background-color: #2563eb; color: white; }
        .step-completed { background-color: #16a34a; color: white; }
        .step-pending { background-color: #e5e7eb; color: #6b7280; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('head')
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="text-center mb-6">
            <img src="@yield('property_logo', asset('images/logo_aldara.png'))" alt="@yield('property_name', config('app.name'))" class="h-12 mx-auto mb-2" style="max-width: 200px;">
            <h1 class="text-2xl font-bold text-gray-900">@yield('property_name', config('app.name'))</h1>
            <p class="text-gray-500 mt-1">@yield('property_subtitle', 'Aldara, gestión de visitantes')</p>
        </div>
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
