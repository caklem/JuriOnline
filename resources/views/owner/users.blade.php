@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }} }">
    <div class="page-header-premium">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Area Admin Owner</p>
                <h1>Manajemen Pengguna</h1>
                <p>Kelola akun, role, dan status aktif seluruh pengguna platform.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge-premium badge-premium-info">{{ $summary['owners'] }} admin owner aktif</span>
                <button type="button" @click="createOpen = !createOpen" class="btn-premium btn-premium-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                    </svg>
                    Tambah Pengguna
                </button>
            </div>
        </div>
    </div>

    <div class="stats-grid-premium">
        <div class="stat-card-premium">
            <h3>Total Pengguna</h3>
            <div class="number">{{ $summary['total'] }}</div>
        </div>
        <div class="stat-card-premium">
            <h3>Akun Aktif</h3>
            <div class="number">{{ $summary['active'] }}</div>
        </div>
        <div class="stat-card-premium">
            <h3>Akun Nonaktif</h3>
            <div class="number">{{ $summary['inactive'] }}</div>
        </div>
        <div class="stat-card-premium">
            <h3>Admin Owner Aktif</h3>
            <div class="number">{{ $summary['owners'] }}</div>
        </div>
    </div>

    <div x-show="createOpen" x-cloak x-transition class="premium-card">
        <div class="premium-card-header">
            <div>
                <h2 class="premium-card-title">Tambah Pengguna Baru</h2>
                <p class="mt-1 text-xs text-slate-500">Akun langsung aktif dan dapat login dengan password yang Anda tetapkan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('owner.users.store') }}">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <div class="form-group-premium">
                    <label class="form-label-premium" for="name">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="form-control-premium" required autocomplete="off">
                </div>
                <div class="form-group-premium">
                    <label class="form-label-premium" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control-premium" required autocomplete="off">
                </div>
                <div class="form-group-premium">
                    <label class="form-label-premium" for="role_id">Role</label>
                    <select id="role_id" name="role_id" class="form-control-premium" required>
                        <option value="">Pilih role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-premium">
                    <label class="form-label-premium" for="event_scope">Akses Event (khusus Admin)</label>
                    <select id="event_scope" name="event_scope" class="form-control-premium">
                        <option value="all" @selected(old('event_scope', 'all') === 'all')>Semua event</option>
                        <option value="own" @selected(old('event_scope') === 'own')>Hanya event sendiri</option>
                    </select>
                </div>
                <div class="form-group-premium">
                    <label class="form-label-premium" for="password">Password</label>
                    <input id="password" name="password" type="password" class="form-control-premium" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group-premium">
                    <label class="form-label-premium" for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control-premium" required minlength="8" autocomplete="new-password">
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="btn-premium btn-premium-primary">Simpan Pengguna</button>
                <button type="button" @click="createOpen = false" class="btn-premium btn-premium-outline">Batal</button>
            </div>
        </form>
    </div>

    <div class="premium-card">
        <div class="premium-card-header">
            <div>
                <h2 class="premium-card-title">Daftar Pengguna</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $users->total() }} akun ditemukan.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('owner.users.index') }}" class="mb-6 grid gap-3 md:grid-cols-[1fr_14rem_12rem_auto]">
            <input type="search" name="q" value="{{ $search }}" class="form-control-premium" placeholder="Cari nama atau email" aria-label="Cari pengguna">
            <select name="role" class="form-control-premium" aria-label="Filter role">
                <option value="">Semua role</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->display_name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control-premium" aria-label="Filter status">
                <option value="">Semua status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn-premium btn-premium-primary">Filter</button>
                <a href="{{ route('owner.users.index') }}" class="btn-premium btn-premium-outline">Reset</a>
            </div>
        </form>

        @if($users->isEmpty())
        <div class="empty-state-premium">
            <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m17-1v-1a3 3 0 0 0-2.5-2.96M14 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm4 0a2.5 2.5 0 1 1-2.5-2.5" />
                </svg>
            </div>
            <h3>Tidak Ada Pengguna</h3>
            <p>Tidak ada akun yang cocok dengan pencarian atau filter Anda.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Akses Event</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    @php $isSelf = $user->id === auth()->id(); @endphp
                    <tr>
                        <td>
                            <strong class="font-semibold text-slate-900">{{ $user->name }}</strong>
                            @if($isSelf)
                            <span class="badge-premium badge-premium-info ml-2">Akun Anda</span>
                            @endif
                            @if($user->isEventAdmin())
                            <span class="badge-premium badge-premium-warning ml-2">Admin Event</span>
                            @endif
                            <div class="text-xs text-slate-500">{{ $user->email }}</div>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('owner.users.role', $user) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="role_id" class="form-control-premium" style="min-width: 10rem;" aria-label="Role {{ $user->name }}" @disabled($isSelf)>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->display_name }}</option>
                                    @endforeach
                                </select>
                                @unless($isSelf)
                                <button type="submit" class="btn-premium btn-premium-outline btn-sm">Simpan</button>
                                @endunless
                            </form>
                        </td>
                        <td>
                            @if($user->isAdmin())
                            <form method="POST" action="{{ route('owner.users.scope', $user) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="event_scope" class="form-control-premium" style="min-width: 10rem;" aria-label="Akses event {{ $user->name }}">
                                    <option value="all" @selected($user->event_scope === 'all')>Semua event</option>
                                    <option value="own" @selected($user->event_scope === 'own')>Hanya event sendiri</option>
                                </select>
                                <button type="submit" class="btn-premium btn-premium-outline btn-sm">Simpan</button>
                            </form>
                            @else
                            <span class="text-slate-500">-</span>
                            @endif
                        </td>
                        <td>
                            @if($user->isActive())
                            <span class="badge-premium badge-premium-success">Aktif</span>
                            @else
                            <span class="badge-premium badge-premium-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-slate-600">{{ $user->created_at?->format('d M Y') ?? '-' }}</td>
                        <td class="text-right">
                            @unless($isSelf)
                            <form method="POST" action="{{ route('owner.users.status', $user) }}">
                                @csrf
                                @method('PATCH')
                                @if($user->isActive())
                                <button type="submit" class="btn-premium btn-premium-outline btn-sm">Nonaktifkan</button>
                                @else
                                <button type="submit" class="btn-premium btn-premium-success btn-sm">Aktifkan</button>
                                @endif
                            </form>
                            @else
                            <span class="text-xs text-slate-400">Tidak tersedia</span>
                            @endunless
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 flex justify-center">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
