@php
    $autoShow = $autoShow ?? false;

    // Ganti logo cukup dengan menimpa file di public/images/logo-loading.*
    // Urutan prioritas: PNG/WEBP/JPG dulu, lalu SVG placeholder.
    $logoSrc = null;
    foreach (['png', 'webp', 'jpg', 'jpeg', 'svg'] as $extension) {
        $relativePath = "images/logo-loading.{$extension}";
        if (file_exists(public_path($relativePath))) {
            $logoSrc = asset($relativePath);
            break;
        }
    }
@endphp

<div id="loginLoadingOverlay" class="login-loading-overlay" @if(! $autoShow) hidden @endif role="status" aria-live="polite" aria-busy="true">
    <div class="login-loading-card">
        <div class="login-loading-logo">
            @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="" class="login-loading-spinner login-loading-logo-img" width="48" height="48">
            @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="login-loading-spinner" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
            </svg>
            @endif
        </div>
        <p class="login-loading-text">Memuat dashboard…</p>
    </div>
</div>

<noscript>
    <style>
        #loginLoadingOverlay {
            display: none !important;
        }
    </style>
</noscript>

<script>
    (function () {
        const overlay = document.getElementById('loginLoadingOverlay');
        if (!overlay) {
            return;
        }

        const MIN_VISIBLE_MS = 600;
        const FADE_MS = 250;
        let shownAt = performance.now();

        function hide() {
            if (overlay.hidden) {
                return;
            }

            const remaining = Math.max(0, MIN_VISIBLE_MS - (performance.now() - shownAt));

            setTimeout(function () {
                overlay.classList.add('is-hiding');
                setTimeout(function () {
                    overlay.hidden = true;
                    overlay.classList.remove('is-hiding');
                }, FADE_MS);
            }, remaining);
        }

        function show() {
            shownAt = performance.now();
            overlay.hidden = false;
            overlay.classList.remove('is-hiding');
        }

        @if($autoShow)
        if (document.readyState === 'complete') {
            hide();
        } else {
            window.addEventListener('load', hide, { once: true });
        }
        @endif

        document.querySelectorAll('form[data-login-loading]').forEach(function (form) {
            form.addEventListener('submit', function () {
                show();

                const button = form.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                }
            });
        });

        // Kembali lewat tombol Back (bfcache): jangan tinggalkan overlay menutup halaman.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                overlay.hidden = true;
                overlay.classList.remove('is-hiding');
            }
        });
    })();
</script>
