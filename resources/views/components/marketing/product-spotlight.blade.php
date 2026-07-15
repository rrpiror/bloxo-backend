@props([
    'image',
    'amazonUrl',
])

<section class="mx-auto grid w-[min(890px,calc(100%-28px))] grid-cols-1 items-center gap-7 pb-[74px] pt-11 md:w-[min(890px,calc(100%-40px))] md:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] md:gap-16 md:pt-[30px]" aria-labelledby="product-title">
    <div>
        <img
            src="{{ $image }}"
            alt="Bloxo box, how to play booklet and cards"
            class="h-auto w-full object-contain"
        >
    </div>

    <div>
        <p class="marketing-overline">Bloxo</p>
        <h2 class="mb-4">Bloxo</h2>
        <p class="max-w-none text-[15px] font-medium leading-7 md:max-w-[330px]">
            We are currently sold out but there are still some copies available on
            <a class="marketing-link" href="{{ $amazonUrl }}" target="_blank" rel="noopener">Amazon.co.uk</a>
        </p>

        <div class="mt-6 flex max-w-none flex-col items-start gap-4 text-[13px] font-medium md:max-w-[380px] md:flex-row md:items-center md:justify-between md:gap-7">
            <button
                class="inline-flex items-center gap-2 bg-transparent p-0 text-inherit"
                type="button"
                data-share-url="{{ $amazonUrl }}"
                data-share-title="Bloxo"
                data-share-text="Bloxo - the addictive matching game for the whole family."
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3v12m0-12 4 4m-4-4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 13v5a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                Share
            </button>

            <a class="inline-flex items-center gap-3 whitespace-nowrap" href="{{ $amazonUrl }}" target="_blank" rel="noopener">
                View full details
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</section>
