@props([
    'href',
    'current' => false,
])

<a
    href="{{ $href }}"
    wire:navigate
    {{ $attributes->class([
        'rounded-full px-4 py-1.5 text-sm font-semibold transition',
        'bg-white text-orange-600' => $current,
        'text-white hover:bg-orange-400' => ! $current,
    ]) }}
>
    {{ $slot }}
</a>
