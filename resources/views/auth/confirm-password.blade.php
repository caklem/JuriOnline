@php
    $authTitle = 'Konfirmasi Password';
    $authSubtitle = 'Area ini memerlukan konfirmasi password sebelum Anda melanjutkan.';
@endphp

<x-guest-layout :authTitle="$authTitle" :authSubtitle="$authSubtitle">
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

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

        <button type="submit" class="auth-button">Konfirmasi</button>
    </form>
</x-guest-layout>
