@extends('layouts.app')

@section('title', isset($juryScore) ? 'Edit Penilaian' : 'Berikan Penilaian')

@section('content')
<div class="mb-4">
    <a href="{{ route('jury.participants', $registration->event) }}" class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18" />
        </svg>
        Kembali ke Daftar Peserta
    </a>
</div>

<div class="page-header-premium">
    <h1>{{ isset($juryScore) ? 'Edit Penilaian' : 'Berikan Penilaian' }}</h1>
    <p>{{ $registration->user->name }} &middot; {{ $registration->event->name }}</p>
</div>

<div class="premium-card">
    <h2 class="mb-4 text-base font-semibold text-slate-900">Informasi Peserta</h2>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nama Peserta</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $registration->user->name }}</p>
        </div>
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Email</p>
            <p class="mt-1 text-sm text-slate-700 break-all">{{ $registration->user->email }}</p>
        </div>
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Event</p>
            <p class="mt-1 text-sm text-slate-700">{{ $registration->event->name }}</p>
        </div>
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</p>
            <p class="mt-1"><span class="badge badge-success">{{ $registration->status === 'verified' ? 'Terverifikasi' : ucfirst($registration->status) }}</span></p>
        </div>
    </div>

    @if($registration->notes)
    <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-700 ring-1 ring-slate-100">
        <strong class="text-slate-900">Catatan Peserta:</strong>
        <span class="mt-1 block whitespace-pre-line">{{ $registration->notes }}</span>
    </div>
    @endif

    @if($registration->getAverageScore())
    <div class="mt-4 rounded-xl bg-sky-50 px-4 py-3 text-sm text-sky-800 ring-1 ring-sky-100">
        <strong>Rata-rata nilai peserta dari seluruh juri:</strong> {{ number_format($registration->getAverageScore(), 2) }}
        <span class="text-xs text-sky-700">(nilai Anda akan ditambahkan ke rata-rata ini)</span>
    </div>
    @endif
</div>

<div class="premium-card">
    <form method="POST" action="{{ isset($juryScore) ? route('jury.update-score', $juryScore) : route('jury.score.store', $registration) }}">
        @csrf
        @if(isset($juryScore))
        @method('PATCH')
        @endif

        @php
        // Use assigned form template if available, otherwise fallback
        $formTemplate = $assignedFormTemplate ?? (isset($juryScore) && $juryScore->juryFormTemplate ? $juryScore->juryFormTemplate : ($registration->event->juryFormTemplate ?? null));
        @endphp

        @if($formTemplate)
        <!-- Custom Criteria Form -->
        <div class="rounded-xl border border-slate-200 border-l-4 border-l-indigo-500 bg-white p-5">
            <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5 text-indigo-600" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3" />
                </svg>
                Kriteria Penilaian
            </h3>
            @if($formTemplate->name)
            <p class="mt-1 text-sm font-semibold text-indigo-700">Form: {{ $formTemplate->name }}</p>
            @endif
            <p class="mt-1 text-sm text-slate-600">Isi semua kolom bertanda <span class="text-rose-600">*</span>. Total nilai dijumlahkan otomatis.</p>

            @php
            $totalMaxScore = 0;
            $existingResponses = isset($juryScore) && $juryScore->criteria_responses ? $juryScore->criteria_responses : [];
            $existingTotal = 0;
            foreach ($existingResponses as $existingValue) {
                if (is_array($existingValue) && isset($existingValue['score'])) {
                    $existingTotal += (float) $existingValue['score'];
                } elseif (is_numeric($existingValue)) {
                    $existingTotal += (float) $existingValue;
                }
            }
            @endphp

            <!-- Hidden field for form template ID -->
            <input type="hidden" name="jury_form_template_id" value="{{ $formTemplate->id }}">

            <div class="mt-5">
                @foreach($formTemplate->criteria_fields as $index => $field)
                @php
                if ($field['type'] === 'score') {
                $totalMaxScore += $field['max'];
                }
                $existingValue = $existingResponses[$field['label']] ?? null;
                if (is_array($existingValue) && isset($existingValue['score'])) {
                $existingValue = $existingValue['score'];
                }
                @endphp

                <div class="form-group">
                    <label for="criteria_{{ $index }}" class="form-label">
                        {{ $field['label'] }}
                        @if($field['required'])
                        <span class="text-rose-600">*</span>
                        @endif
                        @if($field['type'] === 'score')
                        <span class="font-semibold text-indigo-700">(Max: {{ $field['max'] }} poin)</span>
                        @endif
                    </label>
                    @if(isset($field['description']) && $field['description'])
                    <p id="criteria_{{ $index }}_hint" class="mb-2 mt-1 text-sm text-slate-500">{{ $field['description'] }}</p>
                    @endif

                    @php
                    $describedBy = (!empty($field['description']) ? "criteria_{$index}_hint" : '');
                    @endphp

                    @if($field['type'] === 'score')
                    <input
                        type="number"
                        class="form-control"
                        id="criteria_{{ $index }}"
                        name="criteria_{{ $index }}"
                        min="0"
                        max="{{ $field['max'] }}"
                        step="0.5"
                        value="{{ old('criteria_'.$index, $existingValue) }}"
                        @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
                        aria-invalid="false"
                        data-error="criteria_{{ $index }}_error"
                        {{ $field['required'] ? 'required' : '' }}>
                    @elseif($field['type'] === 'textarea')
                    <textarea
                        class="form-control"
                        id="criteria_{{ $index }}"
                        name="criteria_{{ $index }}"
                        rows="3"
                        @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
                        aria-invalid="false"
                        data-error="criteria_{{ $index }}_error"
                        {{ $field['required'] ? 'required' : '' }}>{{ old('criteria_'.$index, $existingValue) }}</textarea>
                    @else
                    <input
                        type="text"
                        class="form-control"
                        id="criteria_{{ $index }}"
                        name="criteria_{{ $index }}"
                        value="{{ old('criteria_'.$index, $existingValue) }}"
                        @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
                        aria-invalid="false"
                        data-error="criteria_{{ $index }}_error"
                        {{ $field['required'] ? 'required' : '' }}>
                    @endif

                    @if(!empty($field['required']))
                    <p id="criteria_{{ $index }}_error" class="field-error">
                        @if($field['type'] === 'score')
                        Isi nilai antara 0 dan {{ $field['max'] }}.
                        @else
                        {{ $field['label'] }} wajib diisi.
                        @endif
                    </p>
                    @endif
                </div>
                @endforeach
            </div>

            @if($totalMaxScore > 0)
            <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <strong class="text-slate-900">Total Nilai: <span id="juriScoreTotal" class="text-indigo-700">{{ $existingTotal }}</span></strong>
                    <span class="text-xs font-medium text-slate-500">Maksimal {{ $totalMaxScore }} poin</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Nilai akhir dihitung otomatis dari jumlah kriteria. Angka di atas berubah saat Anda mengisi.</p>
            </div>
            @endif
        </div>

        <!-- Hidden field for auto-calculated score -->
        <input type="hidden" name="score" value="0">
        @else
        <!-- Standard Score Form -->
        <div class="form-group">
            <label for="score" class="form-label">Nilai (0-100) <span class="text-rose-600">*</span></label>
            <input
                type="number"
                class="form-control"
                id="score"
                name="score"
                min="0"
                max="100"
                step="0.01"
                value="{{ old('score', $juryScore->score ?? '') }}"
                required>
            <small class="mt-1 block text-xs text-slate-500">Berikan nilai antara 0 sampai 100.</small>
        </div>
        @endif

        <div class="form-group mt-5">
            <label for="public_feedback" class="form-label">Feedback Publik</label>
            <textarea
                class="form-control"
                id="public_feedback"
                name="public_feedback"
                rows="4"
                placeholder="Feedback yang dapat dilihat oleh peserta...">{{ old('public_feedback', $juryScore->public_feedback ?? '') }}</textarea>
            <small class="mt-1 block text-xs text-slate-500">Feedback ini akan terlihat oleh peserta dan publik.</small>
        </div>

        <div class="form-group">
            <label for="private_evaluation" class="form-label">Evaluasi Private</label>
            <textarea
                class="form-control"
                id="private_evaluation"
                name="private_evaluation"
                rows="4"
                placeholder="Evaluasi internal untuk admin...">{{ old('private_evaluation', $juryScore->private_evaluation ?? '') }}</textarea>
            <small class="mt-1 block text-xs text-slate-500">Evaluasi ini hanya terlihat oleh admin dan juri.</small>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">
                {{ isset($juryScore) ? 'Update Penilaian' : 'Simpan Penilaian' }}
            </button>
            <a href="{{ route('jury.participants', $registration->event) }}" class="btn btn-outline-secondary">
                Batal
            </a>
        </div>
        <p class="mt-3 text-xs text-slate-500">
            Setelah disimpan, sistem otomatis membawa Anda ke peserta berikutnya yang belum dinilai.
        </p>
    </form>
</div>

@push('scripts')
<script>
    (function () {
        const total = document.getElementById('juriScoreTotal');
        const inputs = Array.from(document.querySelectorAll('input[name^="criteria_"]'));
        if (!total || inputs.length === 0) return;

        const format = (value) => (Number.isInteger(value) ? String(value) : value.toFixed(2));
        const update = () => {
            const sum = inputs.reduce((acc, el) => acc + (parseFloat(el.value) || 0), 0);
            total.textContent = format(sum);
        };

        inputs.forEach((el) => el.addEventListener('input', update));
        update();
    })();

    // :user-invalid mengatur tampilan; aria-invalid dan aria-describedby
    // disinkronkan agar pembaca layar hanya membaca pesan error saat benar-benar invalid.
    (function () {
        document.querySelectorAll('[data-error]').forEach((control) => {
            const errorId = control.dataset.error;
            const hintIds = (control.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);

            const sync = (invalid) => {
                control.setAttribute('aria-invalid', invalid ? 'true' : 'false');
                const ids = invalid ? [...hintIds, errorId] : hintIds;
                if (ids.length > 0) control.setAttribute('aria-describedby', ids.join(' '));
                else control.removeAttribute('aria-describedby');
            };

            control.addEventListener('invalid', () => sync(true));
            ['input', 'change', 'blur'].forEach((type) => {
                control.addEventListener(type, () => sync(control.matches(':user-invalid')));
            });
        });
    })();
</script>
@endpush
@endsection
