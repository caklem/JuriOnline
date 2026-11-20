@extends('layouts.app')

@section('title', 'Rekapitulasi Nilai - ' . $event->name)

@push('styles')
<style>
    #recap-page .recap-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        color: #0f172a;
    }

    #recap-page .recap-table th,
    #recap-page .recap-table td {
        border: 1px solid #e2e8f0;
        padding: 0.75rem;
        text-align: center;
        vertical-align: middle;
    }

    #recap-page .recap-table thead th {
        background: #0f172a;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    #recap-page .recap-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }

    #recap-page .recap-table tbody td {
        font-size: 0.875rem;
        color: #0f172a;
    }

    @media print {
        .card > div:first-child {
            display: none;
        }

        body {
            background: white;
            color: #0f172a !important;
        }

        .sidebar, .top-navbar, .mobile-menu-toggle {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }

        table {
            page-break-inside: auto;
            color: #0f172a !important;
        }

        table thead th {
            background: #0f172a !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        table tbody td {
            color: #0f172a !important;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="card" id="recap-page">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">REKAPITULASI NILAI {{ strtoupper($event->name) }}</h1>
        <p class="mt-1 text-sm text-slate-600">Event: {{ $event->name }}</p>

        @if(isset($totalJuries) && $totalJuries > 0)
        <div class="mt-4 flex flex-wrap gap-4 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3 text-sm">
            <div>
                <strong class="text-emerald-700">Jumlah Juri:</strong>
                <span class="text-slate-900">{{ $totalJuries }} Juri</span>
            </div>
            <div>
                <strong class="text-emerald-700">Jumlah Form Juri:</strong>
                <span class="text-slate-900">{{ $totalFormTemplates ?? 0 }} Form</span>
            </div>
            <div>
                <strong class="text-emerald-700">Sistem Penilaian:</strong>
                <span class="text-slate-900">Rata-rata dari {{ $totalFormTemplates ?? 0 }} Form Juri</span>
            </div>
        </div>
        @endif

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Detail Event
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M6 14h12v7H6v-7Z" />
                </svg>
                Cetak
            </button>
        </div>
    </div>

    @if($recapData->isEmpty())
    <div class="empty-state-premium">
        <h3>Belum ada data</h3>
        <p>Belum ada data penilaian untuk event ini.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="recap-table">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NO. PESERTA</th>
                    <th style="text-align: left;">NAMA GROUP</th>
                    <th style="text-align: left;">ALAMAT</th>

                    @if($juryFormTemplates && $juryFormTemplates->count() > 0)
                    <th colspan="{{ $juryFormTemplates->count() }}">
                        NILAI AKHIR PER FORM
                    </th>
                    @else
                    <th>NILAI</th>
                    @endif

                    @if($hasLegacyScores && $juryFormTemplates && $juryFormTemplates->count() > 0)
                    <th>NILAI LAIN</th>
                    @endif

                    <th>JUMLAH</th>
                    <th>RATA-RATA</th>
                    <th>JURI</th>
                    <th>RANK</th>
                </tr>

                @if($juryFormTemplates && $juryFormTemplates->count() > 0)
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    @foreach($juryFormTemplates as $formTemplate)
                    <th style="font-size: 0.75rem; font-weight: 500;">
                        {{ $formTemplate->name ?? 'Form Juri' }}
                    </th>
                    @endforeach
                    @if($hasLegacyScores)
                    <th></th>
                    @endif
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                @endif
            </thead>
            <tbody>
                @foreach($recapData as $index => $data)
                <tr>
                    <td class="font-medium">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $data['registration_number'] }}</td>
                    <td style="text-align: left;" class="font-semibold">{{ $data['participant_name'] }}</td>
                    <td style="text-align: left;">{{ $data['address'] }}</td>

                    @if($juryFormTemplates && $juryFormTemplates->count() > 0)
                        @foreach($juryFormTemplates as $formTemplate)
                            @php
                                $formScore = collect($data['form_details'] ?? [])->firstWhere('name', $formTemplate->name ?? 'Form Juri');
                                $scoreValue = $formScore ? $formScore['score'] : 0;
                            @endphp
                            <td class="font-medium">
                                {{ $scoreValue > 0 ? number_format($scoreValue, 2, ',', '.') : '-' }}
                            </td>
                        @endforeach
                    @else
                    <td class="font-medium">
                        {{ number_format($data['total_score'], 2, ',', '.') }}
                    </td>
                    @endif

                    @if($hasLegacyScores && $juryFormTemplates && $juryFormTemplates->count() > 0)
                    <td class="font-medium">
                        {{ ($data['legacy_score'] ?? 0) > 0 ? number_format($data['legacy_score'], 2, ',', '.') : '-' }}
                    </td>
                    @endif

                    <td class="font-bold">
                        {{ number_format($data['total_score'], 2, ',', '.') }}
                    </td>
                    <td class="font-bold">
                        {{ number_format($data['average_score'], 2, ',', '.') }}
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $data['jury_count'] ?? 0 }} Juri</span>
                    </td>
                    <td class="text-base font-bold">
                        @if($data['rank'] <= 3)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mr-1 inline-block h-4 w-4 text-amber-500" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4m-5-9H4a2 2 0 0 1-2-2V8h4m14 4h-3m3 0a2 2 0 0 1-2 2h-1M8 8h8m-8 0a4 4 0 0 0 8 0M6 8V5h12v3" />
                        </svg>
                        @endif
                        <span>{{ $data['rank'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
