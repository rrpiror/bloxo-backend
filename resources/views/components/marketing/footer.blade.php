@php
    $links = [
        ['label' => 'Refund policy', 'url' => route('refund-policy')],
        ['label' => 'Privacy policy', 'url' => route('privacy-policy')],
        ['label' => 'Terms of service', 'url' => route('terms-of-service')],
        ['label' => 'Contact information', 'url' => route('contact-information')],
        ['label' => 'Cookie preferences', 'url' => route('cookie-preferences')],
    ];
@endphp

<footer class="marketing-footer">
    <div class="marketing-footer-inner">
        <span>© 2026 Bloxo</span>

        <nav aria-label="Footer navigation">
            <ul class="marketing-footer-link-list">
                @foreach ($links as $link)
                    <li>
                        <a class="marketing-footer-link" href="{{ $link['url'] }}">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</footer>
