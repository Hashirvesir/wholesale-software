<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family= figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.getItem('dark-mode') === 'true' || (!('dark-mode' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 overflow-hidden">
    <div class="relative flex items-top justify-center min-h-screen sm:items-center sm:pt-0">
        <!-- Background Decoration -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-0 right-0 -tr-x-1/2 -tr-y-1/2 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl">
            </div>
            <div class="absolute bottom-0 left-0 tr-x-1/2 tr-y-1/2 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl">
            </div>
        </div>

        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="flex flex-col items-center justify-center pt-8">
                <div
                    class="text-9xl font-extrabold tracking-tighter text-transparent bg-clip-text bg-gradient-to-br from-primary-600 to-indigo-600 dark:from-primary-400 dark:to-indigo-400 drop-shadow-xl mb-4 animate-bounce">
                    @yield('code')
                </div>

                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    @yield('message')
                </h1>

                <p class="text-lg text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">
                    @yield('description')
                </p>

                <div class="flex space-x-4">
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Go Home
                    </a>

                    <button onclick="window.history.back()"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-700 text-base font-medium rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Go Back
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-12 text-sm text-gray-400 dark:text-gray-500 animate-pulse">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>
    </div>
</body>

</html>
