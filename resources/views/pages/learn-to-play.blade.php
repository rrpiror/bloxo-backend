@php
    $setupRules = [
        '2 players: Discard 1 card',
        '3 players: Discard 0 cards',
        '4 players: Discard 3 cards',
    ];

    $placementRules = [
        [
            'image' => asset('images/rules/rule-1.webp'),
            'alt' => 'Correct Bloxo placement with matching edges in a straight line',
            'title' => 'Cards must be placed in straight lines with edges matching.',
            'body' => 'This would score 4 points (2 for the yellow circles and 2 for the green stars).',
        ],
        [
            'image' => asset('images/rules/rule-2.webp'),
            'alt' => 'Incorrect Bloxo placement with a staggered card',
            'title' => 'No Staggering Allowed.',
            'body' => 'Cards have to be placed as though on a grid without edges overlapping.',
        ],
        [
            'image' => asset('images/rules/rule-3.webp'),
            'alt' => 'Incorrect Bloxo placement where adjoining colours do not match',
            'title' => "All adjoining colours must match so you can't do this.",
            'body' => "You can't do this because the pink and green star would be touching.",
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

<x-layouts.marketing
    title="Learn to Play Bloxo | Bloxo"
    description="Learn the rules of Bloxo, including setup, scoring and placement rules."
>
    <main class="marketing-page">
        <section class="learn-page-content">
            <div class="learn-page-intro">
                <p class="marketing-overline">Rules</p>
                <h1>Learn to Play Bloxo</h1>
                <p>
                    It's so simple we almost didn't have to write this page! But just so that everyone is clear, these are the rules of Bloxo.
                </p>
            </div>

            <div class="learn-section-list">
                <section class="learn-section">
                    <h2>Aim</h2>
                    <p>Placing one card per turn, try to create the biggest blocks of shapes to score the most points.</p>
                </section>

                <section class="learn-section">
                    <h2>Equipment</h2>
                    <p>Bloxo Cards (40).</p>
                    <p>Bloxo Scoring App (optional) or pen and paper for scoring.</p>
                </section>

                <section class="learn-section">
                    <h2>Set Up</h2>
                    <p>Depending on the number of players, discard the relevant number of cards, as follows:</p>

                    <ul class="learn-setup-list">
                        @foreach ($setupRules as $rule)
                            <li>{{ $rule }}</li>
                        @endforeach
                    </ul>

                    <p>
                        Deal 6 cards to each player. Place the remaining cards on the table as the draw pile. Place the top card from the draw pile face up in the middle of the table to start the game. If this card is a single colour (i.e. 4 green stars) you can choose to place it back in the deck and deal a new starting card if you wish. Choose a player to go first.
                    </p>
                </section>

                <section class="learn-section">
                    <h2>Playing</h2>
                    <p>On their turn, a player can choose to:</p>
                    <p>Place a card following the placement rules. After playing a card, draw a new card from the draw pile.</p>
                    <p><em>or</em></p>
                    <p>
                        Pass their turn. If a player can not go (or chooses not to go) they should select a card from their hand, set it to one side in a discard pile and draw a new card from the deck (if there are any left). The discarded cards are out of the game and the player who passed immediately loses 4 points.
                    </p>
                </section>

                <section class="learn-section">
                    <h2>Scoring</h2>
                    <p>
                        Players score one point for every shape in the block they make or add to. For example, if a green block already has 6 stars in it and a player adds 2 green stars to the block they score eight. If a subsequent player adds another green star to the block they score nine. Use the Bloxo app or pen and paper to keep scores as you go.
                    </p>
                </section>

                <section class="learn-section">
                    <h2>Winning</h2>
                    <p>The game ends once all players have played their last card or if no more moves can be played by any player. The player with the highest score wins!</p>
                </section>
            </div>
        </section>

        <section class="learn-rules-section" aria-labelledby="placement-rules-title">
            <div class="learn-rules-inner">
                <h2 id="placement-rules-title">Placement Rules</h2>

                <div class="learn-rule-list">
                    @foreach ($placementRules as $rule)
                        <article class="learn-rule">
                            <img
                                src="{{ $rule['image'] }}"
                                alt="{{ $rule['alt'] }}"
                                class="learn-rule-image"
                            >

                            <div class="learn-rule-copy">
                                <h3>{{ $rule['title'] }}</h3>
                                <p>{{ $rule['body'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <x-marketing.footer />
</x-layouts.marketing>
