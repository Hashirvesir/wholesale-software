<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Wholesaler Software') }} - Premium Inventory Management</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .dark .glass {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        @keyframes blob {
            0% {
                transform: translate(0px, 0px) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }

            100% {
                transform: translate(0px, 0px) scale(1);
            }
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>

    <script>
        if (localStorage.getItem('dark-mode') === 'true' || (!('dark-mode' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 overflow-x-hidden">
    <!-- Animated Background -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div
            class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary-500/10 dark:bg-primary-900/20 rounded-full blur-[120px] animate-blob">
        </div>
        <div
            class="absolute top-[20%] right-[-5%] w-[35%] h-[35%] bg-indigo-500/10 dark:bg-indigo-900/20 rounded-full blur-[120px] animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute bottom-[-10%] left-[20%] w-[45%] h-[45%] bg-blue-500/10 dark:bg-blue-900/20 rounded-full blur-[120px] animate-blob animation-delay-4000">
        </div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="px-6 py-4 flex items-center justify-between max-w-7xl mx-auto w-full">
            <div class="flex items-center space-x-2">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-primary-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-16 0m16 0l-8 4-8-4m16 0v10l-8 4-8-4V7" />
                    </svg>
                </div>
                <span
                    class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ config('app.name', 'Wholesaler') }}</span>
            </div>

            <div class="flex items-center space-x-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="px-5 py-2.5 rounded-xl font-semibold text-white bg-primary-600 hover:bg-primary-700 transition-all duration-300 shadow-lg shadow-primary-500/20">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-5 py-2.5 rounded-xl font-semibold text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/5 transition-all duration-300">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="px-5 py-2.5 rounded-xl font-semibold text-white bg-gray-900 dark:bg-white dark:text-gray-900 hover:scale-105 transition-all duration-300 shadow-xl">
                                Get Started
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-grow flex items-center justify-center px-6 py-12">
            <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-left space-y-8">
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full bg-primary-50 dark:bg-primary-900/30 border border-primary-100 dark:border-primary-800">
                        <span class="flex h-2 w-2 rounded-full bg-primary-600 mr-2 animate-pulse"></span>
                        <span
                            class="text-xs font-bold text-primary-700 dark:text-primary-300 uppercase tracking-wider">Next
                            Gen Inventory Management</span>
                    </div>

                    <h1
                        class="text-5xl lg:text-7xl font-extrabold text-gray-900 dark:text-white leading-[1.1] tracking-tight">
                        Power Your <br />
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 via-indigo-600 to-blue-600">Wholesale
                            Empire</span>
                    </h1>

                    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-lg leading-relaxed">
                        The ultimate solution for managing sales, purchases, and finances with absolute precision.
                        Streamline your workflow and scale your business effortlessly.
                    </p>

                    <div class="flex flex-wrap gap-4 pt-4">
                        <a href="{{ route('login') }}"
                            class="group relative px-8 py-4 bg-primary-600 rounded-2xl overflow-hidden transition-all duration-300 transform hover:scale-105 shadow-2xl shadow-primary-500/40">
                            <div
                                class="absolute inset-0 w-3 bg-white/20 transition-all duration-[400ms] ease-out group-hover:w-full">
                            </div>
                            <span class="relative text-white font-bold text-lg flex items-center">
                                Start Free Trial
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </a>

                        <div class="flex -space-x-3 items-center ml-4">
                            <img class="w-10 h-10 rounded-full border-2 border-white dark:border-gray-950"
                                src="https://ui-avatars.com/api/?name=John+Doe&background=random" alt="">
                            <img class="w-10 h-10 rounded-full border-2 border-white dark:border-gray-950"
                                src="https://ui-avatars.com/api/?name=Jane+Smith&background=random" alt="">
                            <img class="w-10 h-10 rounded-full border-2 border-white dark:border-gray-950"
                                src="https://ui-avatars.com/api/?name=Mike+Ross&background=random" alt="">
                            <div class="pl-6 text-sm font-medium text-gray-500">Trusted by 500+ Businesses</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-8 pt-8 border-t border-gray-200 dark:border-gray-800">
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">99.9%</div>
                            <div class="text-sm text-gray-500">Uptime</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">24/7</div>
                            <div class="text-sm text-gray-500">Support</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">Secure</div>
                            <div class="text-sm text-gray-500">Encryption</div>
                        </div>
                    </div>
                </div>

                <div class="relative lg:block hidden">
                    <!-- Dashboard Mockup -->
                    <div
                        class="relative glass rounded-3xl p-4 shadow-2xl transform rotate-3 hover:rotate-0 transition-transform duration-500 overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 to-transparent"></div>
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
                            alt="App Interface"
                            class="rounded-2xl shadow-inner border border-white/10 dark:border-white/5 opacity-80 group-hover:opacity-100 transition-opacity">

                        <!-- Floating elements -->
                        <div
                            class="absolute top-[10%] left-[-5%] glass p-4 rounded-2xl shadow-xl animate-bounce animation-delay-2000">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-400 uppercase">Revenue</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">+Rs. 45,000</div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute bottom-[20%] right-[-5%] glass p-4 rounded-2xl shadow-xl animate-bounce">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                    12</div>
                                <div>
                                    <div class="text-xs font-bold text-gray-400 uppercase">New Orders</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">Ready to Ship</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="px-6 py-8 border-t border-gray-100 dark:border-gray-900">
            <div
                class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center text-gray-500 dark:text-gray-400 text-sm">
                <div>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved in Pakistan.</div>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-primary-600 transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-primary-600 transition-colors">Terms of Service</a>
                    <a href="#" class="hover:text-primary-600 transition-colors">Help Center</a>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
