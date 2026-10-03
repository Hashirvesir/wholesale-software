<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 italic uppercase tracking-widest">User
                    Management</h3>
                <a href="{{ route('users.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none transition ease-in-out duration-150 shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New User
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
                                    <th class="px-6 py-4 text-left">User Details</th>
                                    <th class="px-6 py-4 text-left">Role</th>
                                    <th class="px-6 py-4 text-left">Member Since</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($users as $user)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="h-10 w-10 flex-shrink-0 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                                    <span
                                                        class="text-primary-700 dark:text-primary-300 font-black uppercase">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                        {{ $user->name }}</div>
                                                    <div class="text-xs text-gray-400 font-mono">{{ $user->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @foreach ($user->roles as $role)
                                                <span
                                                    class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full font-black text-[10px] uppercase border border-indigo-200 dark:border-indigo-800 tracking-tighter">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 italic">
                                            {{ $user->created_at->format('d M, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="inline-flex items-center p-2 bg-blue-100 hover:bg-blue-200 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded-lg transition transform hover:scale-110">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            @if ($user->id !== auth()->id())
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('Strict Decision! Do you really want to delete this user?')">
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
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
