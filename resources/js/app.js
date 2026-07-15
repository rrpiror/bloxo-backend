import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('click', (event) => {
    const shareButton = event.target.closest('[data-share-url]');

    if (!shareButton) {
        return;
    }

    const url = shareButton.dataset.shareUrl;
    const title = shareButton.dataset.shareTitle || document.title;
    const text = shareButton.dataset.shareText || '';

    if (navigator.share) {
        navigator.share({ title, text, url }).catch(() => {});
        return;
    }

    navigator.clipboard?.writeText(url);
});
