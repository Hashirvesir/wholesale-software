<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 italic uppercase tracking-widest">User
                    Roles & Permissions</h3>
                <a href="{{ route('roles.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none transition ease-in-out duration-150 shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Role
                </a>
            </div>

            @if (session('success'))
                <div
                    class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900 dark:text-green-300 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-0 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto text-sm">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr
                                    class="bg-gray-50 dark:bg-gray-700 uppercase tracking-widest text-[10px] font-black text-gray-500 dark:text-gray-300">
                                    <th class="px-6 py-4 text-left">Role Name</th>
                                    <th class="px-6 py-4 text-left">Permissions Count</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($roles as $role)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-200">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-gray-100 italic tracking-wide">
                                            {{ $role->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-3 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full font-black text-[11px] border border-primary-200 dark:border-primary-800">
                                                {{ $role->permissions->count() }} Permissions
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                            <a href="{{ route('roles.edit', $role->id) }}"
                                                class="inline-flex items-center p-2 bg-blue-100 hover:bg-blue-200 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded-lg transition transform hover:scale-110">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            @if ($role->name !== 'Super Admin')
                                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('Strict Decision! Do you really want to delete this role?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center p-2 bg-red-100 hover:bg-red-200 text-red-700 dark:bg-red-900 dark:text-red-300 rounded-lg transition transform hover:scale-110">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
