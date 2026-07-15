@props([
    'image',
    'quote',
    'reviewer',
    'ctaLabel',
    'ctaUrl',
])

<section class="relative min-h-[470px] overflow-hidden sm:min-h-[420px] lg:min-h-[448px]" aria-label="Bloxo hero">
    <img
        src="{{ $image }}"
        alt=""
        class="absolute inset-0 h-full w-full object-cover object-top sm:object-[center_46%]"
        aria-hidden="true"
    >

    <div class="absolute left-1/2 top-1/2 w-[calc(100%-28px)] max-w-[810px] -translate-x-1/2 -translate-y-1/2 bg-white/95 px-6 py-7 text-center sm:w-[calc(100%-40px)] sm:px-10 sm:py-8">
        <h1>"{{ $quote }}"</h1>
        <p class="marketing-meta mb-5 mt-3">{{ $reviewer }}</p>
        <a
            class="marketing-button"
            href="{{ $ctaUrl }}"
            target="_blank"
            rel="noopener"
        >
            {{ $ctaLabel }}
        </a>
    </div>
</section>
