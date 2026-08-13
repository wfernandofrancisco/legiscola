@props([
    'theme' => 'auto',
])

@php
    $siteKey = \App\Support\Turnstile::siteKey();
@endphp

@if ($siteKey)
    <div class="cf-turnstile my-3" data-sitekey="{{ $siteKey }}" data-theme="{{ $theme }}"></div>
    @once
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endpush
    @endonce
@endif
