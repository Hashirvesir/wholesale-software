@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3.5 pe-4 py-2 rounded-xl text-start text-xs font-bold text-[#252ebf] dark:text-blue-300 bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/70 dark:border-blue-900/40 transition duration-150 ease-in-out'
            : 'block w-full ps-3.5 pe-4 py-2 rounded-xl text-start text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100/70 dark:hover:bg-gray-700/50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
