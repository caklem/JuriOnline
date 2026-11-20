@extends('layouts.app')

@section('title', 'Buat Event Baru')

@section('content')
<div class="premium-card">
    <div class="premium-card-header">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Buat Event Baru</h1>
            <p class="mt-1 text-sm text-slate-500">Lengkapi informasi event, periode pendaftaran, dan banner event Anda.</p>
        </div>
        <a href="{{ route('events.index') }}" class="btn-premium btn-premium-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6" />
            </svg>
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">Nama Event *</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="category_id" class="form-label">Kategori</label>
            <select name="category_id" id="category_id" class="form-control">
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Deskripsi *</label>
            <textarea name="description" id="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="form-group">
                <label for="price" class="form-label">Harga (Rp) *</label>
                <input type="number" name="price" id="price" class="form-control" value="{{ old('price', 0) }}" min="0" step="1000" required>
            </div>

            <div class="form-group">
                <label for="quota" class="form-label">Kuota Peserta *</label>
                <input type="number" name="quota" id="quota" class="form-control" value="{{ old('quota') }}" min="1" required>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="form-group">
                <label for="registration_start" class="form-label">Mulai Pendaftaran *</label>
                <input type="date" name="registration_start" id="registration_start" class="form-control" value="{{ old('registration_start') }}" required>
            </div>

            <div class="form-group">
                <label for="registration_end" class="form-label">Akhir Pendaftaran *</label>
                <input type="date" name="registration_end" id="registration_end" class="form-control" value="{{ old('registration_end') }}" required>
            </div>
        </div>

        <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4">
            <div class="form-group">
                <label for="event_date" class="form-label">Tanggal Hari H *</label>
                <input type="date" name="event_date" id="event_date" class="form-control" value="{{ old('event_date') }}" required>
                <span class="form-help">Tanggal pelaksanaan utama yang tampil ke juri. Paket event aktif 1 bulan sejak hari H.</span>
                <p class="mt-1 text-xs font-semibold text-indigo-700" id="eventDateHint"></p>
                @error('event_date') <span class="input-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="form-group">
                <label for="event_start" class="form-label">Mulai Event *</label>
                <input type="date" name="event_start" id="event_start" class="form-control" value="{{ old('event_start') }}" required>
            </div>

            <div class="form-group">
                <label for="event_end" class="form-label">Akhir Event *</label>
                <input type="date" name="event_end" id="event_end" class="form-control" value="{{ old('event_end') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="location" class="form-label">Lokasi</label>
            <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}" placeholder="Contoh: Hotel Bintang 5 Jakarta atau Online via Zoom">
        </div>

        <div class="form-group">
            <label for="banner" class="form-label">Banner Event (JPG, PNG - Max 2MB)</label>
            <input type="file" name="banner" id="banner" class="form-control" accept="image/jpeg,image/png">
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
            <button type="submit" class="btn btn-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                </svg>
                Buat Event
            </button>
            <a href="{{ route('events.index') }}" class="btn btn-cancel">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    (function () {
        const day = document.getElementById('event_date');
        const start = document.getElementById('event_start');
        const end = document.getElementById('event_end');
        const hint = document.getElementById('eventDateHint');
        if (!day) return;

        const fmt = (date) => date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const update = () => {
            if (day.value) {
                if (start && !start.value) start.value = day.value;
                if (end && !end.value) end.value = day.value;
            }
            if (!hint) return;
            if (!day.value) { hint.textContent = ''; return; }
            const base = new Date(day.value + 'T00:00:00');
            const until = new Date(base.getFullYear(), base.getMonth() + 1, base.getDate());
            hint.textContent = 'Paket aktif sampai ' + fmt(until) + ' (1 bulan sejak hari H).';
        };

        day.addEventListener('change', update);
        update();
    })();
</script>
@endpush
@endsection
