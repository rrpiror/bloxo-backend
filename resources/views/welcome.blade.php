@php
    $amazonUrl = 'https://www.amazon.co.uk/Bloxo-Families-Perfect-Holidays-Camping/dp/B0D6Q4Z2YP';
    $heroImage = asset('images/bloxo-web/JT_013.jpg');
@endphp

<x-layouts.marketing
    title="Bloxo | The addictive matching game"
    description="Bloxo is the addictive pattern-matching game for the whole family. Buy Bloxo on Amazon."
    :image="$heroImage"
>

    <main>
        <x-marketing.hero
            :image="$heroImage"
            quote="Our new family favourite game. 10/10!"
            reviewer="Mariana (via reviews)"
            cta-label="Shop Now"
            :cta-url="$amazonUrl"
        />

        <x-marketing.product-spotlight
            :amazon-url="$amazonUrl"
            :image="asset('images/bloxo-web/JT_007.jpg')"
        />

        <x-marketing.rules-preview />

        <x-marketing.reviews
            :review-image="asset('images/review-gameplay.webp')"
            :write-review-url="$amazonUrl"
        />
    </main>

    <x-marketing.footer />
</x-layouts.marketing>
