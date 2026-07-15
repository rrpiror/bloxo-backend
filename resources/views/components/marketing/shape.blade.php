@props([
    'type',
    'color',
])

@php
    $colorClass = match ($color) {
        'yellow' => 'text-[#ffca18]',
        'blue' => 'text-[#189cff]',
        'green' => 'text-[#19c866]',
        'pink' => 'text-[#ff0a8a]',
        default => 'text-white',
    };
@endphp

@switch($type)
    @case('circle')
        <svg {{ $attributes->merge(['class' => "h-full w-full {$colorClass}"]) }} viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
            <circle cx="50" cy="50" r="42" />
        </svg>
        @break

    @case('plus')
        <svg {{ $attributes->merge(['class' => "h-full w-full {$colorClass}"]) }} viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
            <path d="M39 8h22v31h31v22H61v31H39V61H8V39h31z" />
        </svg>
        @break

    @case('star')
        <svg {{ $attributes->merge(['class' => "h-full w-full {$colorClass}"]) }} viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
            <path d="M50 4 61 24 83 17 76 39 96 50 76 61 83 83 61 76 50 96 39 76 17 83 24 61 4 50 24 39 17 17 39 24z" />
        </svg>
        @break

    @case('diamond')
    @default
        <svg {{ $attributes->merge(['class' => "h-full w-full {$colorClass}"]) }} viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
            <rect x="20" y="20" width="60" height="60" transform="rotate(45 50 50)" />
        </svg>
@endswitch
