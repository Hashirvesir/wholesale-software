<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h2
                            class="text-2xl font-black text-gray-800 dark:text-gray-200 italic tracking-tighter uppercase">
                            Account Transfers</h2>
                        <a href="{{ route('account-transfers.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg shadow-indigo-200 dark:shadow-none">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            New Transfer
                        </a>
                    </div>

                    @if (session('success'))
                        <div
                            class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-r-xl">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 font-black tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">From Account</th>
                                    <th class="px-6 py-4">To Account</th>
                                    <th class="px-6 py-4 text-right">Amount</th>
                                    <th class="px-6 py-4">Note</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($transfers as $transfer)
                                    <tr
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <td class="px-6 py-4 font-bold italic">{{ $transfer->date }}</td>
                                        <td
                                            class="px-6 py-4 font-black text-red-600 dark:text-red-400 uppercase tracking-tighter">
                                            {{ $transfer->fromAccount->name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 font-black text-green-600 dark:text-green-400 uppercase tracking-tighter">
                                            {{ $transfer->toAccount->name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-right font-black text-indigo-600 dark:text-indigo-400 font-mono">
                                            Rs. {{ number_format($transfer->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-xs italic">{{ $transfer->note }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <x-dropdown align="right" width="48">
                                                <x-slot name="trigger">
                                                    <button
                                                        class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition duration-150 mx-auto">
                                                        <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                                                            <path
                                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                        </svg>
                                                    </button>
                                                </x-slot>
                                                <x-slot name="content">
                                                    <x-dropdown-link href="#"
                                                        onclick="event.preventDefault(); if(confirm('Delete this transfer?')) { document.getElementById('delete-transfer-{{ $transfer->id }}').submit(); }"
                                                        class="text-red-600 flex items-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        {{ __('Delete') }}
                                                    </x-dropdown-link>
                                                </x-slot>
                                            </x-dropdown>
                                            <form action="{{ route('account-transfers.destroy', $transfer) }}"
                                                method="POST" id="delete-transfer-{{ $transfer->id }}" class="hidden">
                                                @csrf @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic"> No
                                            transfers recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $transfers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
