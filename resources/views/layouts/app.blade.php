<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Invenco">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- PWA & Offline Support -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#059669">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="/js/offline-sync.js"></script>
    <script src="/js/keyboard-nav.js" defer></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .keyboard-nav:focus {
            outline: 2px solid #2563eb !important;
            outline-offset: -2px !important;
            background-color: rgba(37, 99, 235, 0.05) !important;
        }

        .dark .keyboard-nav:focus {
            background-color: rgba(37, 99, 235, 0.2) !important;
        }

        /* Keyboard-First Active Row Highlight */
        .kb-selected-row {
            background-color: rgba(37, 99, 235, 0.12) !important;
            outline: 2px solid #2563eb !important;
            outline-offset: -2px !important;
            position: relative;
            z-index: 2;
            transition: background-color 0.1s ease;
        }

        .dark .kb-selected-row {
            background-color: rgba(37, 99, 235, 0.3) !important;
            outline: 2px solid #3b82f6 !important;
        }

        /* Universal Spatial Focus Ring (Web Browser TV/Accessibility Style) */
        .spatial-active-ring {
            outline: 3px solid #2563eb !important;
            outline-offset: 3px !important;
            box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.35), 0 0 16px rgba(37, 99, 235, 0.5) !important;
            border-radius: 8px !important;
            position: relative !important;
            z-index: 9999 !important;
            animation: spatialRingPulse 1.8s infinite ease-in-out !important;
        }

        .dark .spatial-active-ring {
            outline: 3px solid #60a5fa !important;
            box-shadow: 0 0 0 5px rgba(96, 165, 250, 0.4), 0 0 16px rgba(96, 165, 250, 0.6) !important;
        }

        /* Editing Mode: Active Green Ring */
        .spatial-editing-mode {
            outline: 3px solid #10b981 !important;
            outline-offset: 3px !important;
            box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.4), 0 0 18px rgba(16, 185, 129, 0.6) !important;
            border-radius: 8px !important;
            position: relative !important;
            z-index: 9999 !important;
            animation: none !important;
        }

        .dark .spatial-editing-mode {
            outline: 3px solid #34d399 !important;
            box-shadow: 0 0 0 5px rgba(52, 211, 153, 0.45), 0 0 18px rgba(52, 211, 153, 0.7) !important;
        }

        @keyframes spatialRingPulse {
            0%, 100% {
                box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.35), 0 0 16px rgba(37, 99, 235, 0.5);
            }
            50% {
                box-shadow: 0 0 0 7px rgba(59, 130, 246, 0.5), 0 0 24px rgba(59, 130, 246, 0.7);
            }
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

<body class="font-sans antialiased overflow-x-hidden">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 overflow-x-hidden">
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
        <main class="pb-24 xl:pb-6">
            {{ $slot }}
        </main>
    </div>

    <!-- Mobile Bottom Navigation Bar (Visible on mobile/tablet) -->
    @include('layouts.mobile-bottom-nav')

    <!-- Camera Barcode Scanner Modal -->
    <x-barcode-camera-scanner />

    <!-- Invenco AI Copilot Widget & Drawer -->
    @can('access ai assistant')
    <x-ai-copilot />
    <x-ai-recovery-modal />
    @endcan

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

            // Alt + Key Shortcuts (Global Navigation & Quick Actions)
            if (e.altKey) {
                switch (e.key.toLowerCase()) {
                    case 'b': // Dashboard
                        window.location.href = "{{ route('dashboard') }}";
                        break;
                    case 'x': // POS Counter
                        window.location.href = "{{ route('pos.index') }}";
                        break;
                    case 's': // New Sale
                        window.location.href = "{{ route('sales.create') }}";
                        break;
                    case 'l': // Sales Invoices List
                        window.location.href = "{{ route('sales.index') }}";
                        break;
                    case 'p': // New Purchase
                        window.location.href = "{{ route('purchases.create') }}";
                        break;
                    case 'u': // Purchases List
                        window.location.href = "{{ route('purchases.index') }}";
                        break;
                    case 'q': // New Product
                        window.location.href = "{{ route('products.create') }}";
                        break;
                    case 'i': // Products Catalog List
                        window.location.href = "{{ route('products.index') }}";
                        break;
                    case 'c': // New Category
                        window.location.href = "{{ route('categories.create') }}";
                        break;
                    case 'r': // Reports
                        window.location.href = "{{ route('reports.profit-loss') }}";
                        break;
                    case 'm': // Mobile Repairs / Lab
                        window.location.href = "{{ route('repairs.index') }}";
                        break;
                }
            }
        };

        // Global Document Share Helper (Web Share API for Android/Mobile with WhatsApp/Clipboard fallback)
        window.shareDoc = async function(data) {
            const title = data.title || 'Invenco Document';
            const text = data.text || '';
            const url = data.url || window.location.href;

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: title,
                        text: text,
                        url: url
                    });
                    return;
                } catch (err) {
                    if (err.name === 'AbortError') return; // User closed share sheet
                }
            }

            // Fallback for desktop or unsupported browsers: WhatsApp & Clipboard
            const fullText = (text ? text + "\n\n" : "") + (url ? "PDF Document: " + url : "");
            if (navigator.clipboard && url) {
                try {
                    await navigator.clipboard.writeText(url);
                } catch(e) {}
            }

            const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(fullText);
            window.open(waUrl, '_blank');
        };
    </script>
</body>

</html>
