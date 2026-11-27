import './bootstrap';

import Alpine from 'alpinejs';

// Invoker Commands (`command`/`commandfor`) tidak tersedia di browser lama:
// muat polyfill hanya bila API-nya benar-benar tidak ada.
if (!('commandForElement' in HTMLButtonElement.prototype)) {
    import('invokers-polyfill');
}

window.Alpine = Alpine;

Alpine.start();
