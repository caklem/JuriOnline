<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">
            Perbarui Password
        </h2>

        <p class="mt-1 text-sm leading-6 text-slate-600">
            Gunakan password yang panjang dan unik agar akun tetap aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div class="form-group">
            <x-input-label for="update_password_current_password" value="Password saat ini" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="form-group">
            <x-input-label for="update_password_password" value="Password baru" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="form-group">
            <x-input-label for="update_password_password_confirmation" value="Konfirmasi password baru" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan</x-primary-button>

            @if (session('status') === 'password-updated')
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
