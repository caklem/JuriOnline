@extends('layouts.app')

@section('title', 'Daftar Peserta - ' . $event->name)

@section('content')
<div class="card">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
        <div>
            <h1 style="margin-bottom: 0.5rem;">👥 Daftar Peserta</h1>
            <p style="color: #666; margin: 0;">{{ $event->name }}</p>
        </div>
        <a href="{{ route('events.show', $event) }}" class="btn" style="background: #ddd; padding: 0.75rem 1.5rem;">
            ← Kembali ke Event
        </a>
    </div>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <!-- Total Peserta -->
        <div style="background: #2563eb; color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">{{ $event->registrations->count() }}</div>
            <div style="font-size: 0.9rem; opacity: 0.9;">Total Peserta</div>
        </div>

        <!-- Terverifikasi -->
        <div style="background: #16a34a; color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">{{ $event->registrations->where('status', 'verified')->count() }}</div>
            <div style="font-size: 0.9rem; opacity: 0.9;">Terverifikasi</div>
        </div>

        <!-- Pending -->
        <div style="background: #f59e0b; color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">{{ $event->registrations->where('status', 'pending')->count() }}</div>
            <div style="font-size: 0.9rem; opacity: 0.9;">Pending</div>
        </div>

        <!-- Ditolak -->
        <div style="background: #dc2626; color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">{{ $event->registrations->where('status', 'rejected')->count() }}</div>
            <div style="font-size: 0.9rem; opacity: 0.9;">Ditolak</div>
        </div>
    </div>

    <!-- Table -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
        @if($registrations->isEmpty())
        <div style="padding: 3rem; text-align: center; color: #999;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
            <p style="font-size: 1.1rem;">Belum ada peserta yang mendaftar.</p>
        </div>
        @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;" class="participants-table">
                <thead class="table-header-black">
                    <tr>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">No. Pendaftaran</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Nama Peserta</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Status Pendaftaran</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">Status Pembayaran</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600;">Nilai Rata-rata</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $index => $registration)
                    <tr style="border-bottom: 1px solid #e0e0e0; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                        <td style="padding: 1rem;">
                            <span style="font-family: monospace; background: #f1f3f4; padding: 0.25rem 0.5rem; border-radius: 4px;">{{ $registration->registration_number }}</span>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                    {{ strtoupper(substr($registration->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600;">{{ $registration->user->name }}</div>
                                    <div style="font-size: 0.85rem; color: #666;">{{ $registration->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1rem;">
                            @if($registration->status === 'verified')
                            <span style="background: #d4edda; color: #155724; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500; display: inline-block;">✓ Terverifikasi</span>
                            @elseif($registration->status === 'pending')
                            <span style="background: #fff3cd; color: #856404; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500; display: inline-block;">⏳ Pending</span>
                            @elseif($registration->status === 'rejected')
                            <span style="background: #f8d7da; color: #721c24; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500; display: inline-block;">✕ Ditolak</span>
                            @else
                            <span style="background: #d1ecf1; color: #0c5460; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500; display: inline-block;">{{ $registration->status }}</span>
                            @endif
                        </td>
                        <td style="padding: 1rem;">
                            @if($registration->payment)
                            @if($registration->payment->status === 'verified')
                            <span style="background: #d4edda; color: #155724; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500; display: inline-block;">✓ Terverifikasi</span>
                            @elseif($registration->payment->status === 'pending')
                            <span style="background: #fff3cd; color: #856404; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500; display: inline-block;">⏳ Pending</span>
                            @else
                            <span style="background: #f8d7da; color: #721c24; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500; display: inline-block;">✕ Ditolak</span>
                            @endif
                            @else
                            <span style="background: #f8d7da; color: #721c24; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500; display: inline-block;">✕ Belum Upload</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            @php
                            $avgScore = $registration->getAverageScore();
                            @endphp
                            @if($avgScore > 0)
                            <div style="display: inline-block; background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold; font-size: 1.1rem;">
                                {{ number_format($avgScore, 2) }}
                            </div>
                            @else
                            <span style="color: #999; font-size: 1.2rem;">-</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <a href="{{ route('registrations.show', $registration) }}" class="btn" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 0.6rem 1.2rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; text-decoration: none; display: inline-block; box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3); transition: all 0.3s;">
                                👁️ Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($registrations->hasPages())
        <div style="padding: 1.5rem; background: #f8f9fa; border-top: 1px solid #e0e0e0;">
            {{ $registrations->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

<style>
    .table-header-black tr {
        background-color: #000000 !important;
        background: #000000 !important;
    }

    .table-header-black th {
        background-color: #000000 !important;
        background: #000000 !important;
        color: #ffffff !important;
    }

    .participants-table thead tr {
        background-color: #000000 !important;
    }

    .participants-table thead th {
        background-color: #000000 !important;
        color: #ffffff !important;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
    }

    /* Responsive Styles */
    @media (max-width: 1024px) {
        .card>div[style*="grid-template-columns"] {
            gap: 1rem !important;
        }
    }

    @media (max-width: 768px) {
        .card>div[style*="grid-template-columns: repeat(4, 1fr)"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .participants-table {
            min-width: 800px;
        }

        .card {
            padding: 1rem !important;
        }
    }

    @media (max-width: 480px) {

        .card>div[style*="grid-template-columns: repeat(4, 1fr)"],
        .card>div[style*="grid-template-columns: repeat(2, 1fr)"] {
            grid-template-columns: 1fr !important;
        }

        .card>div>div {
            padding: 0.75rem !important;
        }

        .participants-table {
            font-size: 0.85rem;
        }

        .btn {
            padding: 0.4rem 0.8rem !important;
            font-size: 0.85rem !important;
        }
    }
</style>
@endsection