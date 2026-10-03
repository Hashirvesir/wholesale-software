<x-guest-layout>
    <div class="flex min-h-screen">
        <!-- Left Side: Visual/Branding Section -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-primary-600">
            <!-- Background Image with Overlay -->
            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
                alt="Software Interface" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-40">

            <!-- Floating Decorative Elements -->
            <div class="absolute inset-0">
                <div class="absolute top-[20%] left-[10%] w-64 h-64 bg-white/10 rounded-full blur-3xl animate-pulse">
                </div>
                <div class="absolute bottom-[20%] right-[10%] w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl animate-pulse"
                    style="animation-delay: 2s"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 w-full flex flex-col justify-between p-16 text-white text-left">
                <div>
                    <img src="{{ asset('invenco.png') }}" alt="Invenco Logo"
                        class="h-16 w-auto brightness-0 invert object-contain mb-8">
                    <h1 class="text-5xl font-extrabold leading-tight tracking-tight mb-4">
                        Manage Your Wholesale <br /> Business With Ease.
                    </h1>
                    <p class="text-xl text-primary-100 max-w-lg leading-relaxed">
                        The complete ecosystem for wholesalers. Track inventory, manage sales, and monitor finances in
                        real-time with Invenco.
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex -space-x-3">
                            <img class="w-10 h-10 rounded-full border-2 border-primary-500"
                                src="https://ui-avatars.com/api/?name=A&background=random" alt="">
                            <img class="w-10 h-10 rounded-full border-2 border-primary-500"
                                src="https://ui-avatars.com/api/?name=B&background=random" alt="">
                            <img class="w-10 h-10 rounded-full border-2 border-primary-500"
                                src="https://ui-avatars.com/api/?name=C&background=random" alt="">
                        </div>
                        <p class="text-sm font-medium text-primary-100">Joined by 500+ local businesses</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form Section -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white dark:bg-gray-950">
            <div class="max-w-md w-full">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <img src="{{ asset('invenco.png') }}" alt="Invenco Logo" class="h-16 w-auto mx-auto object-contain">
                </div>

                <div class="mb-10 lg:text-left text-center">
                    <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">Login</h2>
                    <p class="mt-3 text-gray-500 dark:text-gray-400 font-medium">Please enter your credentials to access
                        your account.</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-7">
                    @csrf

                    <!-- Email Address -->
                    <div class="space-y-2">
                        <x-input-label for="email" :value="__('Email Address')"
                            class="text-xs font-bold uppercase tracking-wider text-gray-400 ml-1" />
                        <div class="relative group">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <x-text-input id="email"
                                class="block w-full pl-11 pr-4 py-4 bg-gray-50 dark:bg-gray-900/50 border-gray-100 dark:border-gray-800 focus:border-primary-500 focus:ring-primary-500 rounded-2xl transition-all duration-300"
                                type="email" name="email" :value="old('email')" required autofocus
                                autocomplete="username" placeholder="admin@invenco.com" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1 ml-1" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between ml-1">
                            <x-input-label for="password" :value="__('Password')"
                                class="text-xs font-bold uppercase tracking-wider text-gray-400" />
                            @if (Route::has('password.request'))
                                <a class="text-xs font-bold text-primary-600 hover:text-primary-700 transition-colors"
                                    href="{{ route('password.request') }}">
                                    {{ __('Forgot Password?') }}
                                </a>
                            @endif
                        </div>
                        <div class="relative group">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <x-text-input id="password"
                                class="block w-full pl-11 pr-4 py-4 bg-gray-50 dark:bg-gray-900/50 border-gray-100 dark:border-gray-800 focus:border-primary-500 focus:ring-primary-500 rounded-2xl transition-all duration-300"
                                type="password" name="password" required autocomplete="current-password"
                                placeholder="••••••••" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1 ml-1" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                            <input id="remember_me" type="checkbox"
                                class="h-4 w-4 rounded-lg bg-gray-100 dark:bg-gray-900 border-gray-100 dark:border-gray-800 text-primary-600 shadow-sm focus:ring-primary-500 transition-all cursor-pointer"
                                name="remember">
                            <span
                                class="ms-2 text-sm font-medium text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">{{ __('Stay signed in') }}</span>
                        </label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit"
                        class="w-full relative group px-8 py-4 bg-primary-600 rounded-2xl overflow-hidden transition-all duration-300 transform hover:shadow-2xl hover:shadow-primary-500/30 active:scale-95">
                        <div
                            class="absolute inset-0 w-2 bg-white/20 transition-all duration-500 ease-out group-hover:w-full">
                        </div>
                        <span class="relative text-white font-bold text-lg flex items-center justify-center">
                            {{ __('Sign In') }}
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-20 text-center">
                    <p class="text-sm text-gray-400 font-medium">
                        Powered by <span class="text-gray-900 dark:text-white font-bold">Invenco</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
