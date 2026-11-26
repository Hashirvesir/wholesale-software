<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter italic">
                        Add New Salesman
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Enter details to register a salesman / sales representative
                    </p>
                </div>
                <a href="{{ route('salesmen.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-300 transition">
                    &larr; Back to List
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <form action="{{ route('salesmen.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Salesman Name *" class="text-xs font-bold uppercase text-gray-600 dark:text-gray-400" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-xl"
                            :value="old('name')" required placeholder="e.g. Muhammad Bilal" autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="phone" value="Phone Number / Mobile" class="text-xs font-bold uppercase text-gray-600 dark:text-gray-400" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full rounded-xl"
                                :value="old('phone')" placeholder="e.g. 0300-1234567" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email (Optional)" class="text-xs font-bold uppercase text-gray-600 dark:text-gray-400" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl"
                                :value="old('email')" placeholder="e.g. bilal@example.com" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="commission_rate" value="Commission Rate (%)" class="text-xs font-bold uppercase text-gray-600 dark:text-gray-400" />
                            <x-text-input id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full rounded-xl"
                                :value="old('commission_rate', '0.00')" placeholder="0.00" />
                            <x-input-error class="mt-2" :messages="$errors->get('commission_rate')" />
                            <p class="text-[10px] text-gray-400 mt-1 italic">Optional: Percentage on sales or collection</p>
                        </div>
                        <div>
                            <x-input-label for="status" value="Status *" class="text-xs font-bold uppercase text-gray-600 dark:text-gray-400" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl shadow-sm text-sm">
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active (Available for Assignment)</option>
                                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="address" value="Address / Notes" class="text-xs font-bold uppercase text-gray-600 dark:text-gray-400" />
                        <textarea id="address" name="address" rows="3"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl shadow-sm text-sm"
                            placeholder="Home address or assigned area/route...">{{ old('address') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('address')" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('salesmen.index') }}"
                            class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-gray-200 transition">
                            Cancel
                        </a>
                        <x-primary-button class="py-2.5 px-6 font-black uppercase tracking-wider text-xs shadow-lg shadow-primary-200 dark:shadow-none">
                            Save Salesman
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
