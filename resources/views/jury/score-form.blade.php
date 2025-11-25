@extends('layouts.app')

@section('title', isset($juryScore) ? 'Edit Penilaian' : 'Berikan Penilaian')

@section('content')
<div class="card">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('jury.participants', $registration->event) }}" style="color: #3498db; text-decoration: none;">
            ← Kembali ke Daftar Peserta
        </a>
    </div>

    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 1rem;">
        {{ isset($juryScore) ? 'Edit Penilaian' : 'Berikan Penilaian' }}
    </h1>
</div>

<div class="card">
    <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">Informasi Peserta</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <strong>Nama Peserta:</strong><br>
            {{ $registration->user->name }}
        </div>
        <div>
            <strong>Email:</strong><br>
            {{ $registration->user->email }}
        </div>
        <div>
            <strong>Event:</strong><br>
            {{ $registration->event->name }}
        </div>
        <div>
            <strong>Status:</strong><br>
            <span class="badge badge-success">{{ ucfirst($registration->status) }}</span>
        </div>
    </div>

    @if($registration->notes)
    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
        <strong>Catatan Peserta:</strong><br>
        {{ $registration->notes }}
    </div>
    @endif

    @if($registration->getAverageScore())
    <div style="background: #d1ecf1; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
        <strong>Nilai Rata-rata Saat Ini:</strong> {{ $registration->getAverageScore() }}/100
    </div>
    @endif
</div>

<div class="card">
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
        <div style="background: #e8f5e9; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #27ae60;">
            <h3 style="margin-top: 0;">📊 Kriteria Penilaian</h3>
            @if($formTemplate->name)
            <p style="color: #27ae60; font-weight: bold; margin-bottom: 0.5rem;">Form: {{ $formTemplate->name }}</p>
            @endif
            <p style="color: #666; margin-bottom: 1rem;">Berikan penilaian berdasarkan kriteria yang telah ditentukan</p>

            @php
            $totalMaxScore = 0;
            $existingResponses = isset($juryScore) && $juryScore->criteria_responses ? $juryScore->criteria_responses : [];
            @endphp

            <!-- Hidden field for form template ID -->
            <input type="hidden" name="jury_form_template_id" value="{{ $formTemplate->id }}">

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
                <label for="criteria_{{ $index }}">
                    {{ $field['label'] }}
                    @if($field['required'])
                    <span style="color: red;">*</span>
                    @endif
                    @if($field['type'] === 'score')
                    <span style="color: #27ae60; font-weight: bold;">(Max: {{ $field['max'] }} poin)</span>
                    @endif
                </label>
                @if(isset($field['description']) && $field['description'])
                <p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">{{ $field['description'] }}</p>
                @endif

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
                    {{ $field['required'] ? 'required' : '' }}>
                @elseif($field['type'] === 'textarea')
                <textarea
                    class="form-control"
                    id="criteria_{{ $index }}"
                    name="criteria_{{ $index }}"
                    rows="3"
                    {{ $field['required'] ? 'required' : '' }}>{{ old('criteria_'.$index, $existingValue) }}</textarea>
                @else
                <input
                    type="text"
                    class="form-control"
                    id="criteria_{{ $index }}"
                    name="criteria_{{ $index }}"
                    value="{{ old('criteria_'.$index, $existingValue) }}"
                    {{ $field['required'] ? 'required' : '' }}>
                @endif
            </div>
            @endforeach

            @if($totalMaxScore > 0)
            <div style="background: white; padding: 1rem; border-radius: 5px; margin-top: 1rem;">
                <strong style="color: #27ae60;">Total Maksimal Poin: {{ $totalMaxScore }}</strong>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #666;">Nilai akhir akan dihitung otomatis dari total kriteria penilaian</p>
            </div>
            @endif
        </div>

        <!-- Hidden field for auto-calculated score -->
        <input type="hidden" name="score" value="0">
        @else
        <!-- Standard Score Form -->
        <div class="form-group">
            <label for="score">Nilai (0-100) <span style="color: red;">*</span></label>
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
            <small style="color: #666;">Berikan nilai antara 0 sampai 100</small>
        </div>
        @endif

        <div class="form-group">
            <label for="public_feedback">Feedback Publik</label>
            <textarea
                class="form-control"
                id="public_feedback"
                name="public_feedback"
                rows="4"
                placeholder="Feedback yang dapat dilihat oleh peserta...">{{ old('public_feedback', $juryScore->public_feedback ?? '') }}</textarea>
            <small style="color: #666;">Feedback ini akan terlihat oleh peserta dan publik</small>
        </div>

        <div class="form-group">
            <label for="private_evaluation">Evaluasi Private</label>
            <textarea
                class="form-control"
                id="private_evaluation"
                name="private_evaluation"
                rows="4"
                placeholder="Evaluasi internal untuk organizer dan admin...">{{ old('private_evaluation', $juryScore->private_evaluation ?? '') }}</textarea>
            <small style="color: #666;">Evaluasi ini hanya terlihat oleh organizer, admin, dan juri</small>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                {{ isset($juryScore) ? 'Update Penilaian' : 'Simpan Penilaian' }}
            </button>
            <a href="{{ route('jury.participants', $registration->event) }}" class="btn" style="background-color: #95a5a6; color: white;">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection