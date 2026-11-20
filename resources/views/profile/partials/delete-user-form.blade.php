<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">
            Hapus Akun
        </h2>

        <p class="mt-1 text-sm leading-6 text-slate-600">
            Setelah akun dihapus, seluruh data dan sumber daya yang terkait akan dihapus secara permanen. Unduh data yang ingin Anda simpan sebelum melanjutkan.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus Akun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold tracking-tight text-slate-950">
                Yakin ingin menghapus akun Anda?
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-600">
                Tindakan ini permanen. Masukkan password Anda untuk mengonfirmasi penghapusan akun.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Password" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1"
                    placeholder="Password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button>
                    Hapus Akun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
