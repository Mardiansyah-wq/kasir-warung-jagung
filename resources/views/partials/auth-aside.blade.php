<aside class="auth-aside">
    <div class="auth-brand">
        <img
            src="{{ asset('img/logo-mark.png') }}"
            alt=""
            class="auth-logo"
            width="56"
            height="56"
            decoding="async"
        >
        <div>
            <span class="auth-wordmark">{{ config('app.name', 'Jagung Bakar WJT') }}</span>
            <span class="auth-tagline">Ruang administrasi</span>
        </div>
    </div>

    <div class="auth-aside-mid">
        <p class="auth-eyebrow">Hangatnya rasa, rapi ceritanya.</p>
        <h2 class="auth-headline">Dari dapur hangat,<br>untuk cerita<br>yang hebat.</h2>
        <span class="auth-rule" aria-hidden="true"></span>
        <p class="auth-amber">
            Diracik dengan sepenuh hati.<br>
            Dikelola bersama, setiap hari.
        </p>
        <p class="auth-aside-note">
            Satu ruang untuk mengelola pesanan, menu, dan orang-orang di balik setiap sajian.
        </p>
    </div>

    <p class="auth-copy">&copy; {{ date('Y') }} {{ config('app.name', 'Jagung Bakar WJT') }}</p>
</aside>
