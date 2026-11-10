<!-- Mobile Bottom Navigation Bar (App Bar for WebView / Mobile App) -->
<nav x-data="{
    openMenu() {
        window.dispatchEvent(new CustomEvent('toggle-mobile-menu'));
    }
}"
class="xl:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-gray-800/95 backdrop-blur-lg border-t border-gray-200/80 dark:border-gray-700/80 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] dark:shadow-[0_-4px_20px_rgba(0,0,0,0.3)] transition-all select-none"
style="padding-bottom: max(0.4rem, env(safe-area-inset-bottom));">
    <div class="max-w-md mx-auto px-2 h-16 flex items-center justify-around relative">

        <!-- 1. Home / Dashboard -->
        <a href="{{ route('dashboard') }}"
            class="flex flex-col items-center justify-center flex-1 py-1 px-1 transition-all group active:scale-95"
            :class="{{ request()->routeIs('dashboard') ? 'true' : 'false' }} ? 'text-primary-600 dark:text-primary-400 font-black' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
            <div class="relative">
                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                @if(request()->routeIs('dashboard'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-primary-600 dark:bg-primary-400 rounded-full"></span>
                @endif
            </div>
            <span class="text-[10px] mt-1 tracking-tight">Home</span>
        </a>

        <!-- 2. Sales -->
        @canany(['view sales', 'create sales'])
        <a href="{{ route('sales.index') }}"
            class="flex flex-col items-center justify-center flex-1 py-1 px-1 transition-all group active:scale-95"
            :class="{{ request()->routeIs('sales.*') && !request()->routeIs('pos.*') ? 'true' : 'false' }} ? 'text-primary-600 dark:text-primary-400 font-black' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
            <div class="relative">
                <svg class="w-5 h-5 {{ request()->routeIs('sales.*') && !request()->routeIs('pos.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                @if(request()->routeIs('sales.*') && !request()->routeIs('pos.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-primary-600 dark:bg-primary-400 rounded-full"></span>
                @endif
            </div>
            <span class="text-[10px] mt-1 tracking-tight">Sales</span>
        </a>
        @endcanany

        <!-- 3. CENTER ACTION: POS Counter Floating Button -->
        @canany(['view pos', 'create sales'])
        <div class="flex flex-col items-center justify-center -mt-6 flex-1 relative z-10">
            <a href="{{ route('pos.index') }}"
                class="w-14 h-14 rounded-full bg-gradient-to-tr from-emerald-600 via-emerald-500 to-teal-400 hover:from-emerald-700 hover:to-teal-500 text-white shadow-xl shadow-emerald-500/30 flex items-center justify-center border-4 border-white dark:border-gray-800 active:scale-90 transition-all duration-150 group"
                title="POS Counter">
                <svg class="w-6 h-6 stroke-[2.5] transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </a>
            <span class="text-[10px] font-black mt-0.5 tracking-tight {{ request()->routeIs('pos.*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-300' }}">POS</span>
        </div>
        @endcanany

        <!-- 4. Parties / Customers -->
        @canany(['view parties', 'create parties'])
        <a href="{{ route('parties.index') }}"
            class="flex flex-col items-center justify-center flex-1 py-1 px-1 transition-all group active:scale-95"
            :class="{{ request()->routeIs('parties.*') ? 'true' : 'false' }} ? 'text-primary-600 dark:text-primary-400 font-black' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
            <div class="relative">
                <svg class="w-5 h-5 {{ request()->routeIs('parties.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @if(request()->routeIs('parties.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-primary-600 dark:bg-primary-400 rounded-full"></span>
                @endif
            </div>
            <span class="text-[10px] mt-1 tracking-tight">Parties</span>
        </a>
        @endcanany

        <!-- 5. More Menu -->
        <button type="button" @click="openMenu()"
            class="flex flex-col items-center justify-center flex-1 py-1 px-1 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all group active:scale-95">
            <svg class="w-5 h-5 stroke-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="text-[10px] mt-1 tracking-tight">More</span>
        </button>

    </div>
</nav>
