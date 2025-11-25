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

        <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
            <button type="submit" class="auth-button">
                Daftar
            </button>
        </div>
    </form>

    <div class="auth-footer">
        <p>
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
        </p>
    </div>
</x-guest-layout>
