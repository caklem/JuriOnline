@php
    $authTitle = 'Buat Password Baru';
    $authSubtitle = 'Masukkan password baru untuk akun Anda.';
@endphp

<x-guest-layout :authTitle="$authTitle" :authSubtitle="$authSubtitle">
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="auth-form-group">
            <label for="email" class="auth-label">Email</label>
            <input id="email"
                   class="auth-input"
                   type="email"
                   name="email"
                   value="{{ old('email', $request->email) }}"
                   required
                   autofocus
                   autocomplete="username"
                   placeholder="nama@email.com">
            @error('email')
            <span class="input-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password" class="auth-label">Password baru</label>
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
            <label for="password_confirmation" class="auth-label">Konfirmasi password baru</label>
            <input id="password_confirmation"
                   class="auth-input"
                   type="password"
                   name="password_confirmation"
                   required
                   autocomplete="new-password"
                   placeholder="Ulangi password baru Anda">
            @error('password_confirmation')
            <span class="input-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-button">Simpan password baru</button>
    </form>
</x-guest-layout>
