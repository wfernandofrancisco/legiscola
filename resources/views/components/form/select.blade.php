@props([
    'label' => '',
    'name' => '',
    'id' => null,
    'options' => [], // ['value' => 'Label', ...] ou [['value'=>'x','label'=>'y'], ...]
    'selected' => '',
    'placeholder' => '',
    'hint' => '',
    'required' => false,
    'multiple' => false,
])

@php
    $nameForOld = preg_replace('/\[\]$/', '', $name) ?: $name;
    $fieldId = $id ?? $name;
    $errorName = $nameForOld;
    $hasError = $errors->has($errorName);
    $normalizeSelectValue = static function ($value) {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        return $value === null ? null : (string) $value;
    };
    if ($multiple) {
        $current = array_map($normalizeSelectValue, (array) old($nameForOld, $selected));
    } else {
        $current = $normalizeSelectValue(old($nameForOld, $selected));
    }
    $base =
        'block w-full rounded-lg border bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 transition focus:outline-none focus:ring-2 appearance-none cursor-pointer '
        . ($multiple ? 'pr-3' : 'pr-8');
    $normal = 'border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500/30';
    $error = 'border-red-400 dark:border-red-500 focus:border-red-500 focus:ring-red-500/30';
    $cls = $base . ' ' . ($hasError ? $error : $normal);
@endphp

<div class="relative">
    <select name="{{ $name }}" id="{{ $fieldId }}" @required($required) @if ($multiple) multiple @endif
        {{ $attributes->merge(['class' => $cls]) }}>
        @php
            $useSlot = trim((string) $slot) !== '';
        @endphp
        @if ($useSlot)
            {{ $slot }}
        @else
            @if ($placeholder)
                <option value="" disabled {{ $current === '' || $current === null ? 'selected' : '' }}>{{ $placeholder }}
                </option>
            @endif

            @foreach ($options as $optValue => $optLabel)
                @php
                    if (is_array($optLabel)) {
                        $v = $optLabel['value'];
                        $l = $optLabel['label'];
                    } else {
                        $v = $optValue;
                        $l = $optLabel;
                    }
                    $normalizedValue = $normalizeSelectValue($v);
                @endphp
                <option value="{{ $normalizedValue }}" @if ($multiple) @selected(in_array($normalizedValue, $current, true)) @else @selected($current === $normalizedValue) @endif>
                    {{ $l }}
                </option>
            @endforeach
        @endif
    </select>

    @if (! $multiple)
        {{-- Chevron --}}
        <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-gray-400 dark:text-gray-500">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd" />
            </svg>
        </span>
    @endif

    <label for="{{ $fieldId }}"
        class="absolute left-3 -top-2 bg-white dark:bg-gray-800 px-1 text-[11px] font-medium
               {{ $hasError ? 'text-red-500' : 'text-gray-800 dark:text-white' }}">
        {{ $label }}@if ($required)
            <span class="text-red-500 ml-0.5">*</span>
        @endif
    </label>

    @if ($hint && ! $hasError)
        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif

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
