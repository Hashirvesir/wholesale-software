<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6">
                <a href="{{ route('roles.index') }}"
                    class="p-2 bg-white dark:bg-gray-800 rounded-full shadow-md border border-gray-200 dark:border-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 italic uppercase tracking-widest">Create
                    New Role</h3>
            </div>

            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div
                    class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <div class="mb-8">
                        <label for="name"
                            class="block text-xs font-black uppercase text-gray-500 mb-2 tracking-widest">Role
                            Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full md:w-1/2 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic font-bold">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <h4
                        class="text-sm font-black uppercase text-gray-400 mb-6 tracking-widest border-b pb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Assign Permissions
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach ($permissions as $module => $modulePermissions)
                            <div
                                class="bg-gray-50 dark:bg-gray-900/50 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 transition hover:shadow-lg">
                                <h5
                                    class="text-[11px] font-black uppercase text-primary-600 dark:text-primary-400 mb-4 tracking-tighter flex justify-between items-center bg-primary-50 dark:bg-primary-900/20 px-3 py-1 rounded-full border border-primary-100 dark:border-primary-800">
                                    {{ $module }}
                                    <span class="text-[9px] text-gray-400 lowercase font-medium">module</span>
                                </h5>
                                <div class="space-y-3">
                                    @foreach ($modulePermissions as $permission)
                                        <label class="flex items-center group cursor-pointer">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="permissions[]"
                                                    value="{{ $permission->name }}"
                                                    class="w-5 h-5 text-primary-600 bg-white border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 transition duration-200">
                                            </div>
                                            <span
                                                class="ml-3 text-xs font-bold text-gray-600 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition italic capitalize">
                                                {{ str_replace(' ' . $module, '', $permission->name) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-10 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <x-primary-button type="submit"
                            class="bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 shadow-xl scale-110">
                            Save Role & Permissions
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
