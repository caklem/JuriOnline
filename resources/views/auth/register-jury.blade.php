@php
    $authTitle = 'Daftar Akun Juri';
    $authSubtitle = 'Buat akun juri, lalu minta panitia meng-assign event';
@endphp

<x-guest-layout :authTitle="$authTitle" :authSubtitle="$authSubtitle">

    <div class="mb-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm leading-6 text-indigo-900">
        <p class="font-semibold">Dua cara mendapatkan akun juri:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            <li>
                <strong>Disiapkan panitia:</strong> jika Anda sudah menerima email dan kode akses, tidak perlu daftar di sini. Langsung
                <a href="{{ route('login') }}" class="font-semibold underline">masuk</a>.
            </li>
            <li>
                <strong>Daftar sendiri:</strong> buat akun di bawah, lalu hubungi panitia untuk di-assign ke event.
                Anda belum bisa menilai sebelum mendapat assignment.
            </li>
        </ul>
    </div>

    <form method="POST" action="{{ route('register.jury.store') }}">
        @csrf

        <div class="auth-form-group">
            <label for="name" class="auth-label">Nama Lengkap</label>
            <input id="name"
                   class="auth-input"
                   type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   autofocus
                   autocomplete="name"
                   placeholder="Masukkan nama lengkap Anda">
            @error('name')
            <span class="input-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="email" class="auth-label">Email</label>
            <input id="email"
                   class="auth-input"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autocomplete="username"
                   placeholder="nama@email.com">
            @error('email')
            <span class="input-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password" class="auth-label">Password</label>
            <input id="password"
                   class="auth-input"
                   type="password"
                   name="password"
                   required
                   autocomplete="new-password"
                   placeholder="Minimal 8 karakter">
            @error('password')
            <span class="input-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password_confirmation" class="auth-label">Konfirmasi Password</label>
            <input id="password_confirmation"
                   class="auth-input"
                   type="password"
                   name="password_confirmation"
                   required
                   autocomplete="new-password"
                   placeholder="Ulangi password Anda">
            @error('password_confirmation')
            <span class="input-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-button mt-8">
            Daftar Sebagai Juri
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="ml-2 h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
            </svg>
        </button>
    </form>

    <div class="auth-footer">
        <p>
            Sudah punya akun?
            <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
        </p>
        <p class="mt-2 text-xs text-slate-500">
            Ingin mendaftar sebagai peserta?
            <a href="{{ route('register') }}" class="auth-link">Daftar peserta</a>
        </p>
    </div>
</x-guest-layout>
