@php
    $authTitle = 'Masuk ke Akun';
    $authSubtitle = 'Selamat datang kembali! Silakan masuk ke akun Anda';
@endphp

<x-guest-layout :authTitle="$authTitle" :authSubtitle="$authSubtitle">

    <form method="POST" action="{{ route('login') }}" data-login-loading>
        @csrf

        <!-- Email Address -->
        <div class="auth-form-group">
            <label for="email" class="auth-label">Email</label>
            <input id="email"
                   class="auth-input"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username"
                   placeholder="nama@email.com">
            @error('email')
            <span class="input-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div class="auth-form-group">
            <label for="password" class="auth-label">Password</label>
            <input id="password"
                   class="auth-input"
                   type="password"
                   name="password"
                   required
                   autocomplete="current-password"
                   placeholder="Masukkan password Anda">
            @error('password')
            <span class="input-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Remember Me / Forgot Password -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="auth-checkbox">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="remember_me" class="cursor-pointer">Ingat saya</label>
            </div>

            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="auth-link">
                Lupa password?
            </a>
            @endif
        </div>

        <button type="submit" class="auth-button mt-6">
            Masuk
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="ml-2 h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
            </svg>
        </button>
    </form>

    <div class="auth-footer">
        <p>
            Belum punya akun?
            <a href="{{ route('register') }}" class="auth-link">Daftar sekarang</a>
        </p>
        <p class="mt-2 text-xs leading-5 text-slate-500">
            Juri yang mendapat akun dari panitia: masukkan email dan <strong>kode akses</strong> pada kolom password.
        </p>
        <p class="mt-1 text-xs leading-5 text-slate-500">
            Ingin mendaftar sebagai juri?
            <a href="{{ route('register.jury') }}" class="auth-link">Daftar akun juri</a>
        </p>
    </div>
</x-guest-layout>
