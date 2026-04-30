<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    {{-- Tailwind CSS via CDN (replace with your build if using Vite/Mix) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">

    {{-- Nav --}}
    <nav class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <a href="/" class="font-semibold text-gray-800 text-lg">{{ config('app.name') }}</a>

        <div class="flex items-center gap-4 text-sm">
            @auth
            
            @else

            @endauth
        </div>
    </nav>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="max-w-2xl mx-auto mt-4 px-4">
            <div class="p-4 bg-green-50 border border-green-200 rounded text-green-700 text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="max-w-2xl mx-auto mt-4 px-4">
            <div class="p-4 bg-blue-50 border border-blue-200 rounded text-blue-700 text-sm">
                {{ session('info') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-2xl mx-auto mt-4 px-4">
            <div class="p-4 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Page content --}}
    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>