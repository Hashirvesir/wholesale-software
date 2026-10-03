<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Unit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('units.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                    :value="old('name')" required autofocus placeholder="Kilogram, Piece, etc." />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Short Name -->
                            <div>
                                <x-input-label for="short_name" :value="__('Short Name')" />
                                <x-text-input id="short_name" class="block mt-1 w-full" type="text" name="short_name"
                                    :value="old('short_name')" placeholder="kg, pc, ctn" />
                                <x-input-error :messages="$errors->get('short_name')" class="mt-2" />
                            </div>

                            <!-- Base Unit -->
                            <div>
                                <x-input-label for="base_unit" :value="__('Base Unit (Optional)')" />
                                <x-text-input id="base_unit" class="block mt-1 w-full" type="text" name="base_unit"
                                    :value="old('base_unit')" placeholder="Secondary unit (e.g. Gram)" />
                                <x-input-error :messages="$errors->get('base_unit')" class="mt-2" />
                            </div>

                            <!-- Conversion Value -->
                            <div>
                                <x-input-label for="conversion_value" :value="__('Conversion Value')" />
                                <x-text-input id="conversion_value" class="block mt-1 w-full" type="number"
                                    step="0.0001" name="conversion_value" :value="old('conversion_value', '1.0000')" required />
                                <span class="text-xs text-gray-500 mt-1">Default 1.0000 if this is base unit.</span>
                                <x-input-error :messages="$errors->get('conversion_value')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('units.index') }}"
                                class="mr-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 italic transition duration-150">Cancel</a>
                            <x-primary-button>
                                {{ __('Create Unit') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
