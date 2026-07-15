@props([
    'reviewImage',
    'writeReviewUrl' => '#',
])

@php
    $reviews = collect([
        [
            'rating' => 5,
            'author' => 'Anonymous',
            'verified' => true,
            'date' => '16/04/2025',
            'title' => 'Fun little gane',
            'body' => 'Recommended to us - not disappointed. Fast to learn, fun to play, neat size for taking out and about. App worked well on iphone',
        ],
        [
            'rating' => 5,
            'author' => 'Karen Moore',
            'verified' => true,
            'date' => '14/04/2025',
            'title' => 'Great game',
            'body' => 'Bought as a gift and they loved playing it.',
        ],
        [
            'rating' => 5,
            'author' => 'Nichola',
            'verified' => true,
            'date' => '31/12/2024',
            'title' => 'Yep, it is addictive',
            'body' => "Who would think 2 adults could get so competitive over a simple matching game. We did and now we can't stop. This is a simple game to play but when you score big and beat your opponent it's the best.",
        ],
        [
            'rating' => 5,
            'author' => 'martingreathurst',
            'verified' => false,
            'date' => '29/09/2024',
            'title' => 'Playability and Portability',
            'body' => 'A cleverly conceived game, that comes in a high quality box, Bloxo is easy to learn, engaging to play, and highly portable. Impossible to play just one game!',
        ],
        [
            'rating' => 4,
            'author' => 'Dean King',
            'verified' => false,
            'date' => '08/09/2024',
            'title' => 'Great game',
            'body' => "Got this game from a game convention after playing with the creator. It's easy to learn and very fun, I would probably score it higher if the score card was available on the play store as stated in the instructions.",
        ],
        [
            'rating' => 3,
            'author' => 'M.D.',
            'verified' => false,
            'date' => '25/08/2024',
            'title' => 'No Google app',
            'body' => "Would love to have the app to score as pen and paper is tiresome. Sadly unless your an apple user and even though the app is advertised on the game as being available on Google play it isn't. Poor advertising and makes the game less flowing",
        ],
        [
            'rating' => 3,
            'author' => 'Owain Owain',
            'verified' => false,
            'date' => '16/08/2024',
            'title' => 'Instructions in the box say Google app',
            'body' => 'Good game, but despite what the instructions say there is no Google play scoring app.',
        ],
        [
            'rating' => 5,
            'author' => 'D.L.',
            'verified' => false,
            'date' => '13/07/2024',
            'title' => 'Great Game',
            'body' => 'My significant other and I love to play this game together! Highly recommend others to give it a try. Quick to learn and fun to play.',
        ],
        [
            'rating' => 5,
            'author' => 'Helen McCashey',
            'verified' => true,
            'date' => '04/07/2024',
            'title' => '',
            'body' => 'Great game that you can take anywhere',
        ],
        [
            'rating' => 5,
            'author' => 'Ann Cole',
            'verified' => true,
            'date' => '27/06/2024',
            'title' => 'Fun Game!',
            'body' => 'Not difficult to learn or play but still a challenge. Aspect of Qwirkle but added depth. Highly enjoyable. I hope they offer an upgrade of wooden tiles as the cardboard can shift easily.',
        ],
        [
            'rating' => 5,
            'author' => 'Mariana Newton',
            'verified' => true,
            'date' => '11/06/2024',
            'title' => 'Absolutely brilliant Game',
            'body' => 'Our new family favourite game - beautifully designed and brilliant fun to play - 10/10!!!',
        ],
        [
            'rating' => 5,
            'author' => 'Sam Whiting',
            'verified' => true,
            'date' => '09/06/2024',
            'title' => 'So addictive!',
            'body' => "We bought this to take on a group holiday and we couldn't put it down! Love it!",
            'image' => $reviewImage,
        ],
        [
            'rating' => 5,
            'author' => 'TRACEY MAILES',
            'verified' => true,
            'date' => '07/06/2024',
            'title' => 'All schools should have BLOXO',
            'body' => 'Great fun, this game is ideal to have on you whenever you go out, good for adults and children.',
        ],
        [
            'rating' => 5,
            'author' => 'Caitlin Rouse',
            'verified' => true,
            'date' => '28/05/2024',
            'title' => 'Great game!',
            'body' => 'We played this for ages with my 6 year old, we.loved it and it was very easy to understand.',
        ],
    ]);

    $pages = $reviews->chunk(5)->values();

    $stars = fn (int $rating) => str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    $histogram = [
        5 => ['count' => 11, 'widthClass' => 'w-4/5'],
        4 => ['count' => 1, 'widthClass' => 'w-[8%]'],
        3 => ['count' => 2, 'widthClass' => 'w-[14%]'],
        2 => ['count' => 0, 'widthClass' => 'w-0'],
        1 => ['count' => 0, 'widthClass' => 'w-0'],
    ];
@endphp

<section class="bg-white px-5 pb-20 pt-2" aria-labelledby="customer-reviews-title" x-data="{ page: 1, pages: {{ $pages->count() }} }">
    <div class="mx-auto max-w-[1080px] border-t border-neutral-200 pt-8">
        <h2 id="customer-reviews-title" class="text-center">Customer Reviews</h2>

        <div class="mt-8 grid items-center gap-8 border-b border-neutral-200 pb-7 md:grid-cols-[1fr_1.15fr_1fr] md:gap-0">
            <div class="text-center md:border-r md:border-neutral-200">
                <div class="text-xl leading-none">★★★★☆ <span class="text-base">4.64 out of 5</span></div>
                <p class="mt-2 text-sm leading-normal">Based on 14 reviews</p>
            </div>

            <div class="mx-auto w-full max-w-[300px] md:px-12">
                @foreach ($histogram as $rating => $row)
                    <div class="grid grid-cols-[88px_1fr_24px] items-center gap-4 text-sm leading-6">
                        <span class="tracking-[0.08em]">{{ $stars($rating) }}</span>
                        <span class="h-3 bg-neutral-100">
                            <span @class(['block h-full', $row['widthClass']])></span>
                        </span>
                        <span>{{ $row['count'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-center md:border-l md:border-neutral-200">
                <a class="marketing-button min-w-[240px] rounded-none" href="{{ $writeReviewUrl }}" target="_blank" rel="noopener">Write a review</a>
            </div>
        </div>     

        <div class="min-h-[770px]">
            @foreach ($pages as $pageIndex => $reviewPage)
                <div x-cloak x-show="page === {{ $pageIndex + 1 }}">
                    @foreach ($reviewPage as $review)
                        <article class="border-b border-neutral-200 py-5">
                            <div class="flex items-start justify-between gap-5">
                                <div>
                                    <div class="text-xl tracking-[0.08em]">{{ $stars($review['rating']) }}</div>
                                    <div class="mt-3 flex items-center gap-2">
                                        
                                        <span class="font-medium>{{ $review['author'] }}</span>
                                        {{-- @if ($review['verified'])
                                            <span class="verified-badge">Verified</span>
                                        @endif --}}
                                    </div>
                                </div>

                                <time class="shrink-0 text-sm font-medium">{{ $review['date'] }}</time>
                            </div>

                            @if ($review['title'])
                                <h3 class="mt-4 text-[16px] leading-normal">{{ $review['title'] }}</h3>
                            @endif
                            <p class="mt-2 leading-7">{{ $review['body'] }}</p>

                            @if (isset($review['image']))
                                <img src="{{ $review['image'] }}" alt="Bloxo game photo shared with review" class="mt-5 h-24 w-24 object-cover">
                            @endif
                        </article>
                    @endforeach
                </div>
            @endforeach
        </div>

        <nav class="mt-5 flex items-center justify-center gap-5 font-medium" aria-label="Reviews pagination">
            <button type="button" class="text-sm" x-on:click="page = Math.max(1, page - 1)" aria-label="Previous page">‹</button>

            @foreach ($pages as $pageIndex => $reviewPage)
                <button
                    type="button"
                    class="px-2 py-1"
                    x-on:click="page = {{ $pageIndex + 1 }}"
                    x-bind:class="page === {{ $pageIndex + 1 }} ? 'border border-neutral-500 shadow-sm' : ''"
                >
                    {{ $pageIndex + 1 }}
                </button>
            @endforeach

            <button type="button" class="text-sm" x-on:click="page = Math.min(pages, page + 1)" aria-label="Next page">›</button>
            
        </nav>
    </div>
</section>
