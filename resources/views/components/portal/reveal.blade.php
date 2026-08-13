{{--
  Animate.css ao scroll (via JS global em #portal-main). Não altera cores.
  Uso: <x-portal.reveal animation="fadeInLeft">...</x-portal.reveal>
  Nomes: https://animate.style/ (ex.: fadeInUp, fadeInLeft, zoomIn, fadeIn)
--}}
@props([
    'animation' => 'fadeInUp',
])

<div data-animate="{{ $animation }}" {{ $attributes }}>
    {{ $slot }}
</div>
