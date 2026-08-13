@props([
    'label' => '',
    'name' => '',
    'id' => null,
    'type' => 'text',
    'value' => '',
    'hint' => '',
    'required' => false,
    'icon' => null, // slot de ícone SVG inline opcional
])

@php
    $fieldId = $id ?? $name;
    $errorName = preg_replace('/\[\]$/', '', $name) ?: $name;
    $hasError = $errors->has($errorName);
    $base =
        'block w-full rounded-lg border bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder-transparent transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-50';
    $normal = 'border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500/30';
    $error =
        'border-red-400 dark:border-red-500 focus:border-red-500 focus:ring-red-500/30 text-red-900 dark:text-red-200';
    $inputClass = $base . ' ' . ($hasError ? $error : $normal) . ($icon ? ' pl-9' : '');
@endphp

<div class="relative">
    {{-- Ícone opcional --}}
    @if ($icon)
        <span
            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 dark:text-gray-500 peer-focus:text-indigo-500">
            {!! $icon !!}
        </span>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $fieldId }}" value="{{ old($name, $value) }}"
        placeholder="{{ $label }}" @required($required) {{ $attributes->merge(['class' => $inputClass]) }} />

    {{-- Floating label --}}
    <label for="{{ $fieldId }}"
        class="absolute left-3 -top-2 bg-white dark:bg-gray-800 px-1 text-[11px] font-medium transition-all
               {{ $hasError ? 'text-red-500' : 'text-gray-800 dark:text-white' }}">
        {{ $label }}@if ($required)
            <span class="text-red-500 ml-0.5">*</span>
        @endif
    </label>

    {{-- Hint --}}
    @if ($hint && !$hasError)
        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif

    {{-- Erro --}}
    @error($errorName)
        <p class="mt-1 flex items-center gap-1 text-[11px] text-red-500">
            <svg class="h-3 w-3 shrink-0" viewBox="0 0 16 16" fill="currentColor">
                <path
                    d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1Zm0 3.5a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0V5.25A.75.75 0 0 1 8 4.5Zm0 7a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z" />
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
