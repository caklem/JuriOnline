@php
    $authTitle = 'Buat Akun Baru';
    $authSubtitle = 'Daftar untuk mulai menggunakan JuriOnline';
@endphp

<x-guest-layout :authTitle="$authTitle" :authSubtitle="$authSubtitle">

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
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

        <!-- Email Address -->
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

        <!-- Password -->
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

        <!-- Confirm Password -->
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
            Daftar
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
        <p class="mt-2 text-xs leading-5 text-slate-500">
            Ingin menjadi juri?
            <a href="{{ route('register.jury') }}" class="auth-link">Daftar akun juri</a>
        </p>
        <p class="mt-1 text-xs leading-5 text-slate-500">
            Juri yang sudah dibuatkan panitia tidak perlu daftar lagi; gunakan email dan kode akses dari panitia.
        </p>
    </div>
</x-guest-layout>
