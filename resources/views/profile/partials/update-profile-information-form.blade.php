<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">
            Informasi Profil
        </h2>

        <p class="mt-1 text-sm leading-6 text-slate-600">
            Perbarui nama dan alamat email akun Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div class="form-group">
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" class="mt-1" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="form-group">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5">
                    <p class="text-sm leading-6 text-amber-800">
                        Alamat email Anda belum diverifikasi.

                        <button form="send-verification" class="rounded text-sm font-semibold text-indigo-600 underline underline-offset-2 transition hover:text-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-700">
                            Link verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-emerald-600"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
