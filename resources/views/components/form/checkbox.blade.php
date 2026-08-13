@props([
    'label' => '',
    'name' => '',
    'value' => '1',
    'checked' => false,
    'hint' => '',
])

@php
    $isChecked = old($name, $checked) == $value;
@endphp

<label class="group inline-flex items-start gap-2.5 cursor-pointer select-none">
    <div class="relative mt-0.5 shrink-0">
        <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}"
            {{ $isChecked ? 'checked' : '' }} {{ $attributes }} class="peer sr-only" />
        {{-- Custom checkbox --}}
        <div
            class="h-4 w-4 rounded border-2 border-gray-300 dark:border-gray-600
                    bg-white dark:bg-gray-800
                    peer-checked:bg-indigo-600 peer-checked:border-indigo-600
                    peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500/40 peer-focus-visible:ring-offset-1
                    transition">
            <svg class="hidden peer-checked:block h-3 w-3 text-white mx-auto mt-0.5" viewBox="0 0 12 12" fill="none"
                stroke="currentColor" stroke-width="2.5">
                <path d="M2 6l3 3 5-5" />
            </svg>
        </div>
    </div>

    <div>
        <span
            class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100 transition leading-none">
            {{ $label }}
        </span>
        @if ($hint)
            <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">{{ $hint }}</p>
        @endif
        @error($name)
            <p class="mt-0.5 text-[11px] text-red-500">{{ $message }}</p>
        @enderror
    </div>
</label>
