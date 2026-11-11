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
                @if (session('error'))
                    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-950/40 border-l-4 border-amber-500 text-amber-800 dark:text-amber-200 rounded-r-xl text-xs font-bold">
                        {{ session('error') }}
                    </div>
                @endif

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

                <!-- Dedicated App Downloads Section -->
                <div x-data="{
                    isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true || window.isNativeApp === true,
                    deferredPrompt: null,
                    canInstallPwa: false
                }"
                x-init="
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        deferredPrompt = e;
                        canInstallPwa = true;
                    });
                "
                x-show="!isStandalone"
                class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <div class="text-center mb-4">
                        <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            Dedicated Desktop & Mobile Apps
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Windows Desktop App -->
                        <a href="{{ route('downloads.app', 'windows') }}"
                            class="group p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/60 hover:bg-blue-50/70 dark:hover:bg-blue-950/30 border border-gray-200/80 dark:border-gray-800 hover:border-blue-300 dark:hover:border-blue-700 transition-all flex flex-col items-center text-center shadow-xs">
                            <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-[#252ebf] dark:text-blue-300 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-[#252ebf] dark:group-hover:text-blue-400 transition-colors">Windows App</span>
                            <span class="text-[10px] text-gray-400 font-medium mt-0.5">Desktop (.exe)</span>
                        </a>

                        <!-- Android Mobile App -->
                        <a href="{{ route('downloads.app', 'android') }}"
                            class="group p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/60 hover:bg-emerald-50/70 dark:hover:bg-emerald-950/30 border border-gray-200/80 dark:border-gray-800 hover:border-emerald-300 dark:hover:border-emerald-700 transition-all flex flex-col items-center text-center shadow-xs">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993.0001.5511-.4482.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993 0 .5511-.4483.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5902 8.4116 13.8533 8.1 12 8.1s-3.5902.3116-5.1367.8497L4.841 5.4467a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3432-4.1021-2.6889-7.5743-6.1185-9.4396"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Android App</span>
                            <span class="text-[10px] text-gray-400 font-medium mt-0.5">Mobile (.apk)</span>
                        </a>
                    </div>

                    <!-- PWA Install Prompt -->
                    <template x-if="canInstallPwa">
                        <button type="button" @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then(() => { canInstallPwa = false; })"
                            class="w-full mt-3 p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 text-xs font-bold flex items-center justify-center gap-2 hover:bg-indigo-100 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Install on this Device (Web App)</span>
                        </button>
                    </template>
                </div>

                <!-- Footer -->
                <div class="mt-10 text-center">
                    <p class="text-sm text-gray-400 font-medium">
                        Powered by <span class="text-gray-900 dark:text-white font-bold">Invenco</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
