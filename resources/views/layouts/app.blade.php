<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .keyboard-nav:focus {
            outline: 2px solid #2563eb !important;
            outline-offset: -2px !important;
            background-color: rgba(37, 99, 235, 0.05) !important;
        }

        .dark .keyboard-nav:focus {
            background-color: rgba(37, 99, 235, 0.2) !important;
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

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Command Palette & Shortcuts -->
    <x-command-palette />
    <x-shortcut-help />

    <script>
        // Global Keyboard Shortcuts
        window.onkeydown = function(e) {
            // Ctrl + Enter to submit any form
            if (e.ctrlKey && e.key === 'Enter') {
                const activeForm = document.activeElement.closest('form');
                if (activeForm) {
                    activeForm.dispatchEvent(new Event('submit', {
                        cancelable: true
                    }));
                    // If the form wasn't prevented by Alpine or other listeners, submit it
                    if (!e.defaultPrevented) {
                        activeForm.submit();
                    }
                }
            }

            // Alt + Key Shortcuts (Navigation/Actions)
            if (e.altKey) {
                switch (e.key.toLowerCase()) {
                    case 's': // New Sale
                        window.location.href = "{{ route('sales.create') }}";
                        break;
                    case 'p': // New Purchase
                        window.location.href = "{{ route('purchases.create') }}";
                        break;
                    case 'q': // New Product
                        window.location.href = "{{ route('products.create') }}";
                        break;
                    case 'c': // New Category
                        window.location.href = "{{ route('categories.create') }}";
                        break;
                    case 'b': // Dashboard
                        window.location.href = "{{ route('dashboard') }}";
                        break;
                }
            }
        };

        // Autofocus first input on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Find the first visible, non-hidden input/select/textarea
            const firstInput = document.querySelector(
                'form input:not([type="hidden"]), form select, form textarea');
            if (firstInput) {
                // Small delay to ensure Alpine and other scripts are ready
                setTimeout(() => firstInput.focus(), 100);
            }
        });
    </script>
</body>

</html>
