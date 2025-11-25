@extends('layouts.app')

@section('title', 'Rekapitulasi Nilai - ' . $event->name)

@push('styles')
<style>
    /* Force all text colors in recap page */
    #recap-page .card,
    #recap-page .card * {
        color: inherit !important;
    }
    
    #recap-page .card h1 {
        color: #2c3e50 !important;
    }
    
    #recap-page .card p {
        color: #495057 !important;
    }
    
    #recap-page table tbody tr td {
        color: #2c3e50 !important;
    }
    
    #recap-page table thead tr th {
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="card" id="recap-page">
    <div style="margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0.5rem; color: #2c3e50;">REKAPITULASI NILAI {{ strtoupper($event->name) }}</h1>
        <p style="color: #495057; margin-bottom: 0.5rem;">Event: {{ $event->name }}</p>
        @if(isset($totalJuries) && $totalJuries > 0)
        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1rem; padding: 1rem; background: #e8f5e9; border-radius: 8px; border-left: 4px solid #27ae60;">
            <div style="color: #2c3e50;">
                <strong style="color: #27ae60;">👥 Jumlah Juri:</strong> {{ $totalJuries }} Juri
            </div>
            <div style="color: #2c3e50;">
                <strong style="color: #27ae60;">📋 Jumlah Form Juri:</strong> {{ $totalFormTemplates ?? 0 }} Form
            </div>
            <div style="color: #2c3e50;">
                <strong style="color: #27ae60;">📊 Sistem Penilaian:</strong> Rata-rata dari {{ $totalFormTemplates ?? 0 }} Form Juri
            </div>
        </div>
        @endif
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('events.show', $event) }}" class="btn" style="background: #3498db; color: white; padding: 0.5rem 1rem; border-radius: 5px; text-decoration: none;">
                ← Kembali ke Detail Event
            </a>
            <button onclick="window.print()" class="btn" style="background: #27ae60; color: white; padding: 0.5rem 1rem; border-radius: 5px; border: none; cursor: pointer;">
                🖨️ Cetak
            </button>
        </div>
    </div>

    @if($recapData->isEmpty())
    <div style="text-align: center; padding: 3rem; background: #f8f9fa; border-radius: 8px;">
        <p style="color: #495057; font-size: 1.1rem;">Belum ada data penilaian untuk event ini.</p>
    </div>
    @else
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); color: #2c3e50;">
            <thead>
                <tr style="background: #1e293b; color: #ffffff;">
                    <th style="padding: 1rem; text-align: center; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">NO</th>
                    <th style="padding: 1rem; text-align: center; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">NO. PESERTA</th>
                    <th style="padding: 1rem; text-align: left; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">NAMA GROUP</th>
                    <th style="padding: 1rem; text-align: left; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">ALAMAT</th>
                    
                    @if($juryFormTemplates && $juryFormTemplates->count() > 0)
                    <th colspan="{{ $juryFormTemplates->count() }}" style="padding: 1rem; text-align: center; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">
                        NILAI AKHIR PER FORM
                    </th>
                    @else
                    <th style="padding: 1rem; text-align: center; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">NILAI</th>
                    @endif
                    
                    <th style="padding: 1rem; text-align: center; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">JUMLAH</th>
                    <th style="padding: 1rem; text-align: center; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">RATA-RATA</th>
                    <th style="padding: 1rem; text-align: center; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">JURI</th>
                    <th style="padding: 1rem; text-align: center; border: 1px solid rgba(255,255,255,0.3); font-weight: bold; color: #ffffff;">RANK</th>
                </tr>
                
                @if($juryFormTemplates && $juryFormTemplates->count() > 0)
                <tr style="background: rgba(44, 62, 80, 0.9); color: #ffffff;">
                    <th style="padding: 0.5rem; border: 1px solid rgba(255,255,255,0.3); color: #ffffff;"></th>
                    <th style="padding: 0.5rem; border: 1px solid rgba(255,255,255,0.3); color: #ffffff;"></th>
                    <th style="padding: 0.5rem; border: 1px solid rgba(255,255,255,0.3); color: #ffffff;"></th>
                    <th style="padding: 0.5rem; border: 1px solid rgba(255,255,255,0.3); color: #ffffff;"></th>
                    @foreach($juryFormTemplates as $formTemplate)
                    <th style="padding: 0.5rem; text-align: center; border: 1px solid rgba(255,255,255,0.3); font-size: 0.875rem; font-weight: normal; color: #ffffff;">
                        {{ $formTemplate->name ?? 'Form Juri' }}
                    </th>
                    @endforeach
                    <th style="padding: 0.5rem; border: 1px solid rgba(255,255,255,0.3); color: #ffffff;"></th>
                    <th style="padding: 0.5rem; border: 1px solid rgba(255,255,255,0.3); color: #ffffff;"></th>
                    <th style="padding: 0.5rem; border: 1px solid rgba(255,255,255,0.3); color: #ffffff;"></th>
                    <th style="padding: 0.5rem; border: 1px solid rgba(255,255,255,0.3); color: #ffffff;"></th>
                </tr>
                @endif
            </thead>
            <tbody style="color: #2c3e50 !important;">
                @foreach($recapData as $index => $data)
                <tr style="border-bottom: 1px solid #dee2e6; {{ $index % 2 === 0 ? 'background: #f8f9fa;' : 'background: white;' }} color: #2c3e50 !important;">
                    <td style="padding: 0.75rem; text-align: center; border: 1px solid #dee2e6; color: #000000 !important; font-weight: 500;">{{ $index + 1 }}</td>
                    <td style="padding: 0.75rem; text-align: center; border: 1px solid #dee2e6; font-weight: 600; color: #000000 !important;">{{ $data['registration_number'] }}</td>
                    <td style="padding: 0.75rem; border: 1px solid #dee2e6; font-weight: 600; color: #000000 !important;">{{ $data['participant_name'] }}</td>
                    <td style="padding: 0.75rem; border: 1px solid #dee2e6; color: #000000 !important;">{{ $data['address'] }}</td>
                    
                    @if($juryFormTemplates && $juryFormTemplates->count() > 0)
                        @foreach($juryFormTemplates as $formTemplate)
                            @php
                                $formScore = collect($data['form_details'] ?? [])->firstWhere('name', $formTemplate->name ?? 'Form Juri');
                                $scoreValue = $formScore ? $formScore['score'] : 0;
                            @endphp
                            <td style="padding: 0.75rem; text-align: center; border: 1px solid #dee2e6; color: #000000 !important; font-weight: 500;">
                                {{ $scoreValue > 0 ? number_format($scoreValue, 2, ',', '.') : '-' }}
                            </td>
                        @endforeach
                    @else
                    <td style="padding: 0.75rem; text-align: center; border: 1px solid #dee2e6; color: #000000 !important; font-weight: 500;">
                        {{ number_format($data['total_score'], 2, ',', '.') }}
                    </td>
                    @endif
                    
                    <td style="padding: 0.75rem; text-align: center; border: 1px solid #dee2e6; font-weight: bold; color: #000000 !important;">
                        {{ number_format($data['total_score'], 2, ',', '.') }}
                    </td>
                    <td style="padding: 0.75rem; text-align: center; border: 1px solid #dee2e6; font-weight: bold; color: #000000 !important;">
                        {{ number_format($data['average_score'], 8, ',', '.') }}
                    </td>
                    <td style="padding: 0.75rem; text-align: center; border: 1px solid #dee2e6; color: #000000 !important; font-size: 0.9rem;">
                        <span style="background: #3498db; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-weight: 600;">{{ $data['jury_count'] ?? 0 }} Juri</span>
                    </td>
                    <td style="padding: 0.75rem; text-align: center; border: 1px solid #dee2e6; font-weight: bold; font-size: 1.1rem; color: #000000 !important;">
                        @if($data['rank'] <= 3)
                            <span style="color: #f39c12;">🏆</span>
                        @endif
                        <span style="color: #000000 !important;">{{ $data['rank'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<style>
    /* Ensure all text is visible - Force override */
    #recap-page {
        color: #2c3e50 !important;
    }
    
    #recap-page .card {
        color: #2c3e50 !important;
    }
    
    #recap-page h1 {
        color: #2c3e50 !important;
    }
    
    #recap-page p {
        color: #495057 !important;
    }
    
    #recap-page table {
        color: #2c3e50 !important;
    }
    
    #recap-page table thead th {
        color: #ffffff !important;
        background: #2c3e50 !important;
    }
    
    #recap-page table tbody {
        color: #000000 !important;
    }
    
    #recap-page table tbody tr {
        color: #000000 !important;
    }
    
    #recap-page table tbody td {
        color: #000000 !important;
    }
    
    #recap-page table tbody td * {
        color: #000000 !important;
    }
    
    #recap-page table tbody td span {
        color: #000000 !important;
    }
    
    @media print {
        .card > div:first-child {
            display: none;
        }
        
        body {
            background: white;
            color: #2c3e50 !important;
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
            color: #2c3e50 !important;
        }
        
        table thead th {
            color: #ffffff !important;
            background: #2c3e50 !important;
        }
        
        table tbody td {
            color: #2c3e50 !important;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }
</style>
@endsection
