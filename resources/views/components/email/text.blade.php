@props([
    'muted' => false,
    'size' => 15,
    'spacing' => '0 0 10px',
])

@php
    $class = $muted ? 'em-muted' : 'em-text';
    $color = $muted ? '#5f6368' : '#1a1c1e';
@endphp
<p class="{{ $class }}" style="margin:{{ $spacing }};font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:{{ $color }};font-size:{{ $size }}px;line-height:1.6;">
    {{ $slot }}
</p>
