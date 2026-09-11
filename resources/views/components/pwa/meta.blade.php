@props([
    'area' => 'aluno',
    'title' => null,
])

@php
    $area = \App\Support\PwaArea::normalize($area);
    $config = \App\Support\PwaArea::config($area);
    $appTitle = $title ?: $config['short_name'];
@endphp

<meta name="theme-color" content="{{ $config['theme_color'] }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ \Illuminate\Support\Str::limit($appTitle, 12, '') }}">
<link rel="manifest" href="{{ route('pwa.manifest', ['area' => $area]) }}">
<link rel="apple-touch-icon" href="{{ asset('img/pwa-icon-192.png') }}">
