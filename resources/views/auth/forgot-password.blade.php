@php
    $authTitle = 'Lupa Password';
    $authSubtitle = 'Masukkan email akun Anda. Kami akan mengirimkan link untuk membuat password baru.';
@endphp

<x-guest-layout :authTitle="$authTitle" :authSubtitle="$authSubtitle">
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

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

        <button type="submit" class="auth-button">Kirim link reset</button>
    </form>

    <div class="auth-footer">
        <p>Ingat password Anda? <a href="{{ route('login') }}" class="auth-link">Masuk</a></p>
    </div>
</x-guest-layout>
