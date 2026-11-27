@php
    $autoShow = $autoShow ?? false;

    // Logo loading mengikuti brand mark JURI.ON.
    $logoSrc = asset('images/brand/jurion-mark.png');
@endphp

<div id="loginLoadingOverlay" class="login-loading-overlay" @if(! $autoShow) hidden @endif role="status" aria-live="polite" aria-busy="true">
    <div class="login-loading-card">
        <div class="login-loading-logo">
            <img src="{{ $logoSrc }}" alt="" class="login-loading-logo-img" width="48" height="48">
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
