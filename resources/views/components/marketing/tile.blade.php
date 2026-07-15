@props([
    'symbols',
])

<div {{ $attributes->merge(['class' => 'bloxo-tile']) }}>
    @foreach ($symbols as $symbol)
        <x-marketing.shape :type="$symbol[0]" :color="$symbol[1]" />
    @endforeach
</div>
