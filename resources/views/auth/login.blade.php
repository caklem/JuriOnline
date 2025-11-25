@php
    $authTitle = 'Masuk ke Akun';
    $authSubtitle = 'Selamat datang kembali! Silakan masuk ke akun Anda';
@endphp

<x-guest-layout :authTitle="$authTitle" :authSubtitle="$authSubtitle">

    <form method="POST" action="{{ route('login') }}">
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

        <!-- Remember Me -->
        <div class="auth-checkbox">
            <input id="remember_me" type="checkbox" name="remember">
            <label for="remember_me">Ingat saya</label>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; flex-wrap: wrap; gap: 1rem;">
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="auth-link">
                Lupa password?
            </a>
            @else
            <div></div>
            @endif

            <button type="submit" class="auth-button" style="width: auto; padding: 1rem 2.5rem;">
                Masuk
            </button>
        </div>
    </form>

    <div class="auth-footer">
        <p>
            Belum punya akun? 
            <a href="{{ route('register') }}" class="auth-link">Daftar sekarang</a>
        </p>
    </div>
</x-guest-layout>
