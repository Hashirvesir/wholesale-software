<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6">
                <a href="{{ route('users.index') }}"
                    class="p-2 bg-white dark:bg-gray-800 rounded-full shadow-md border border-gray-200 dark:border-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 italic uppercase tracking-widest">Create
                    New User</h3>
            </div>

            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div
                    class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label for="name"
                                class="block text-xs font-black uppercase text-gray-500 mb-2 tracking-widest">Full
                                Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic font-bold">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email"
                                class="block text-xs font-black uppercase text-gray-500 mb-2 tracking-widest">Email
                                Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic font-bold">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="role"
                                class="block text-xs font-black uppercase text-gray-500 mb-2 tracking-widest">Assign
                                Role</label>
                            <select name="role" id="role" required
                                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic">
                                <option value="">Select a Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password"
                                    class="block text-xs font-black uppercase text-gray-500 mb-2 tracking-widest">Password</label>
                                <input type="password" name="password" id="password" required
                                    class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic">
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation"
                                    class="block text-xs font-black uppercase text-gray-500 mb-2 tracking-widest">Confirm
                                    Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                    class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <x-primary-button type="submit"
                            class="bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 shadow-xl scale-110">
                            Create User Account
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
