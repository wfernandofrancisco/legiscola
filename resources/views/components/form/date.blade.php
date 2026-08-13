@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'id' => null,
    'hint' => '',
    'required' => false,
])

@php
    $fieldId = $id ?? $name;
    $hasError = $errors->has($name);
    $base =
        'block w-full rounded-lg border bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 transition focus:outline-none focus:ring-2 [color-scheme:light] dark:[color-scheme:dark]';
    $normal = 'border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500/30';
    $error = 'border-red-400 dark:border-red-500 focus:border-red-500 focus:ring-red-500/30';
    $cls = $base . ' ' . ($hasError ? $error : $normal);
@endphp

<div class="relative">
    <input type="date" name="{{ $name }}" id="{{ $fieldId }}" value="{{ old($name, $value) }}"
        @required($required) {{ $attributes->merge(['class' => $cls]) }} />

    <label for="{{ $fieldId }}"
        class="absolute left-3 -top-2 bg-white dark:bg-gray-800 px-1 text-[11px] font-medium
               {{ $hasError ? 'text-red-500' : 'text-indigo-600 dark:text-indigo-400' }}">
        {{ $label }}@if ($required)
            <span class="text-red-500 ml-0.5">*</span>
        @endif
    </label>

    @if ($hint && !$hasError)
        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 flex items-center gap-1 text-[11px] text-red-500">
            <svg class="h-3 w-3 shrink-0" viewBox="0 0 16 16" fill="currentColor">
                <path
                    d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1Zm0 3.5a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0V5.25A.75.75 0 0 1 8 4.5Zm0 7a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z" />
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
