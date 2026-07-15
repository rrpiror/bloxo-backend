@php
    $amazonUrl = 'https://www.amazon.co.uk/Bloxo-Families-Perfect-Holidays-Camping/dp/B0D6Q4Z2YP';
@endphp

<header class="marketing-navbar">
    <nav class="marketing-navbar-inner" aria-label="Main navigation">
        <ul class="marketing-navbar-links">
            <li>
                <a class="marketing-navbar-link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">
                    Home
                </a>
            </li>
            <li>
                <a class="marketing-navbar-link" href="{{ $amazonUrl }}" target="_blank" rel="noopener">
                    Buy Now
                </a>
            </li>
            <li>
                <a class="marketing-navbar-link {{ request()->routeIs('learn-to-play') ? 'is-active' : '' }}" href="{{ route('learn-to-play') }}">
                    Learn to Play
                </a>
            </li>
        </ul>

        <a class="marketing-navbar-logo" href="{{ route('home') }}" aria-label="Bloxo homepage">
            <img src="{{ asset('images/bloxo_logo.png') }}" alt="Bloxo">
        </a>

        <a class="marketing-navbar-cta" href="{{ route('scorer') }}">
            Use our online game calculator
        </a>
    </nav>
</header>
