@php
    $authTitle = 'Verifikasi Email';
    $authSubtitle = 'Satu langkah lagi sebelum Anda mulai menggunakan JuriOnline.';
@endphp

<x-guest-layout :authTitle="$authTitle" :authSubtitle="$authSubtitle">
    <p class="text-sm leading-6 text-slate-600">
        Terima kasih telah mendaftar. Sebelum memulai, verifikasi alamat email Anda melalui link yang sudah kami kirimkan. Jika tidak menerima email, kami dapat mengirimkannya kembali.
    </p>

    @if (session('status') == 'verification-link-sent')
    <div class="alert alert-success mt-4">
        Link verifikasi baru sudah dikirim ke alamat email yang Anda daftarkan.
    </div>
    @endif

    <div class="mt-6 space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="auth-button">Kirim ulang email verifikasi</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="text-sm font-medium text-slate-500 transition hover:text-slate-900">Keluar</button>
        </form>
    </div>
</x-guest-layout>
