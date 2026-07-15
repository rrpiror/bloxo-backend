@props([
    'rulesUrl' => '#',
])

@php
    $examples = [
        [
            'image' => asset('images/rules/rule-1.webp'),
            'alt' => 'Correct Bloxo placement with matching edges in a straight line',
            'title' => 'Cards must be placed in straight lines with edges matching.',
            'body' => 'This would score 4 points (2 for the yellow circles and 2 for the green stars).',
        ],
        [
            'image' => asset('images/rules/rule-2.webp'),
            'alt' => 'Incorrect Bloxo placement with a staggered overlapping card',
            'title' => 'No Staggering Allowed.',
            'body' => 'Cards have to be placed as though on a grid without edges overlapping.',
        ],
        [
            'image' => asset('images/rules/rule-3.webp'),
            'alt' => 'Incorrect Bloxo placement where adjoining colours do not match',
            'title' => "All adjoining colours must match so you can't do this.",
            'body' => "You can't do this because the pink diamond and green star would be touching.",
        ],
        [
            'image' => asset('images/rules/rule-4.webp'),
            'alt' => 'Correct Bloxo placement scoring six points',
            'title' => 'But this is good!',
            'body' => 'It would score 6 points.',
        ],
        [
            'image' => asset('images/rules/rule-5.webp'),
            'alt' => 'Correct Bloxo placement scoring from multiple colours',
            'title' => 'Try to add to multiple colours to score more points.',
            'body' => 'This would score 8 points. Four for the pinks and four for the blues.',
        ],
    ];
@endphp

<section class="bg-white px-5 pb-24 pt-4" aria-labelledby="rules-preview-title">
    <h2 id="rules-preview-title" class="sr-only">How to play Bloxo</h2>

    <div class="mx-auto max-w-[920px] space-y-20 md:space-y-16">
        @foreach ($examples as $example)
            <article class="grid items-center gap-8 md:grid-cols-[390px_minmax(0,360px)] md:gap-16">
                <img
                    src="{{ $example['image'] }}"
                    alt="{{ $example['alt'] }}"
                    class="w-full max-w-[360px] justify-self-center md:max-w-none"
                >

                <div>
                    <h3 class="max-w-[360px]">{{ $example['title'] }}</h3>
                    <p class="mt-5 max-w-[370px]">{{ $example['body'] }}</p>
                </div>
            </article>
        @endforeach

        <div class="flex justify-center pt-2">
            <a class="marketing-button" href="{{ $rulesUrl }}">Read Full rules</a>
        </div>
    </div>
</section>
