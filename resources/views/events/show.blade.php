@extends('layouts.app')

@section('title', 'Detail Event')

@section('content')
<div class="card">
    @if($event->banner)
    <div style="position: relative; cursor: pointer;" onclick="openBannerModal()">
        <img id="eventBanner" src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 5px; margin-bottom: 1.5rem; transition: all 0.3s;">
        <div style="position: absolute; bottom: 1.5rem; right: 1rem; background: rgba(0,0,0,0.6); color: white; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem; backdrop-filter: blur(5px);">
            🔍 Klik untuk melihat detail
        </div>
    </div>
    @endif

    <div style="margin-bottom: 1.5rem;">
        <h1 style="margin-bottom: 0.5rem;">{{ $event->name }}</h1>
        <div>
            @if($event->category)
            <span class="badge badge-info">{{ $event->category->name }}</span>
            @endif
            <span class="badge {{ $event->status === 'active' ? 'badge-success' : ($event->status === 'draft' ? 'badge-warning' : 'badge-info') }}">
                {{ ucfirst($event->status) }}
            </span>
        </div>
    </div>

    @if(auth()->user()->isOrganizer() && $event->organizer_id === auth()->id() || auth()->user()->isAdmin() || auth()->user()->isAdminOwner())
    <!-- Action Buttons Section -->
    <div style="margin-bottom: 2rem;">
        <!-- Primary Actions Row -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <!-- Edit Event -->
            <a href="{{ route('events.edit', $event) }}" class="btn-premium btn-premium-warning" style="padding: 1rem; border-radius: 8px; text-align: center; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">✏️</span>
                <span>Edit Event</span>
            </a>
            <!-- Kelola Form Pendaftaran -->
            <a href="{{ route('events.form.create', $event) }}" class="btn-premium" style="background: #7c3aed; color: white; padding: 1rem; border-radius: 8px; text-align: center; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; position: relative;">
                <span style="font-size: 1.5rem;">📋</span>
                <span>Kelola Form Pendaftaran</span>
                @if($event->eventForm)
                <span style="position: absolute; top: 0.5rem; right: 0.5rem; background: #16a34a; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: bold;">✓</span>
                @endif
            </a>

            @if(auth()->user()->isAdmin() || auth()->user()->isAdminOwner())
            <!-- Kelola Form Penilaian Juri -->
            <a href="{{ route('events.jury-form.create', $event) }}" class="btn-premium" style="background: #ea580c; color: white; padding: 1rem; border-radius: 8px; text-align: center; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; position: relative;">
                <span style="font-size: 1.5rem;">⚖️</span>
                <span>Kelola Form Penilaian Juri</span>
                @if($event->juryFormTemplate)
                <span style="position: absolute; top: 0.5rem; right: 0.5rem; background: #16a34a; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: bold;">✓</span>
                @endif
            </a>

            @if($event->juryFormTemplate || $event->juryFormTemplates()->count() > 0)
            <!-- Preview Form Juri -->
            <button type="button" class="btn-premium btn-premium-info" onclick="showJuryFormPreview()" style="padding: 1rem; border-radius: 8px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; cursor: pointer; border: none;">
                <span style="font-size: 1.5rem;">👁️</span>
                <span>Preview Form Juri</span>
            </button>
            @endif

            @if($event->juryFormTemplates()->count() > 0)
            <!-- Kelola Assignment Form Juri -->
            <a href="{{ route('events.jury-assignments.index', $event) }}" class="btn-premium" style="background: #7c3aed; color: white; padding: 1rem; border-radius: 8px; text-align: center; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">👥</span>
                <span>Assign Form ke Juri</span>
            </a>
            @endif
            @endif
        </div>

        <!-- Event Status Actions -->
        <div style="display: flex; justify-content: flex-start; gap: 1rem;">
            @if($event->status === 'draft')
            <form method="POST" action="{{ route('events.update-status', $event) }}" style="display: inline;">
                @csrf
                <input type="hidden" name="status" value="active">
                <button type="submit" class="btn-premium btn-premium-success" onclick="return confirm('Aktifkan event ini? Event akan terlihat oleh peserta.')" style="padding: 0.75rem 2rem; border-radius: 6px; border: none; cursor: pointer;">
                    ✨ Aktifkan Event
                </button>
            </form>
            @elseif($event->status === 'active')
            <form method="POST" action="{{ route('events.update-status', $event) }}" style="display: inline;">
                @csrf
                <input type="hidden" name="status" value="closed">
                <button type="submit" class="btn-premium btn-premium-warning" onclick="return confirm('Tutup event ini? Peserta tidak bisa mendaftar lagi.')" style="padding: 0.75rem 2rem; border-radius: 6px; border: none; cursor: pointer;">
                    🔒 Tutup Event
                </button>
            </form>
            @elseif($event->status === 'closed')
            <form method="POST" action="{{ route('events.update-status', $event) }}" style="display: inline;">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="btn-premium" onclick="return confirm('Tandai event ini sebagai selesai?')" style="background: #64748b; color: white; padding: 0.75rem 2rem; border-radius: 6px; border: none; cursor: pointer;">
                    ✅ Selesaikan Event
                </button>
            </form>
            @endif
        </div>
    </div>

    <style>
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .btn:active {
            transform: translateY(0);
        }
    </style>
    @endif

</div>

<div class="event-details-grid" style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem; margin-bottom: 2rem;">
    <!-- Deskripsi Section -->
    <div class="event-description" style="background: #ffffff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #e5e7eb;">
        <h3 style="margin-top: 0; color: #2c3e50; font-size: 1.5rem; margin-bottom: 1rem; border-bottom: 3px solid #3498db; padding-bottom: 0.5rem; display: inline-block;">📝 Deskripsi</h3>
        <p style="white-space: pre-line; color: #495057; line-height: 1.8; font-size: 1rem;">{{ $event->description }}</p>
    </div>

    <!-- Informasi Event Section -->
    <div class="event-info-wrapper">
        <div class="event-info" style="background: #2563eb; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); color: white;">
            <h3 style="margin-top: 0; font-size: 1.3rem; margin-bottom: 1.5rem; border-bottom: 2px solid rgba(255,255,255,0.3); padding-bottom: 0.75rem;">ℹ️ Informasi Event</h3>

            <!-- Organizer -->
            <div style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px; backdrop-filter: blur(10px);">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.2rem;">👤</span>
                    <strong style="font-size: 0.9rem;">Organizer</strong>
                </div>
                <div style="padding-left: 1.7rem; font-size: 1rem;">{{ $event->organizer->name }}</div>
            </div>

            <!-- Lokasi -->
            <div style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px; backdrop-filter: blur(10px);">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.2rem;">📍</span>
                    <strong style="font-size: 0.9rem;">Lokasi</strong>
                </div>
                <div style="padding-left: 1.7rem; font-size: 1rem;">{{ $event->location ?? 'Online' }}</div>
            </div>

            <!-- Harga -->
            <div style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.15); padding: 1.25rem; border-radius: 8px; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.2);">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.2rem;">💰</span>
                    <strong style="font-size: 0.9rem;">Harga</strong>
                </div>
                <div style="padding-left: 1.7rem; font-size: 1.75rem; font-weight: bold; color: #ffd700;">Rp {{ number_format($event->price, 0, ',', '.') }}</div>
            </div>

            <!-- Kuota -->
            <div style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px; backdrop-filter: blur(10px);">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.2rem;">👥</span>
                    <strong style="font-size: 0.9rem;">Kuota</strong>
                </div>
                <div style="padding-left: 1.7rem;">
                    <div style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem;">{{ $event->registered_count }} / {{ $event->quota }} peserta</div>
                    @if($event->getAvailableSlots() > 0)
                    <div style="background: rgba(46, 204, 113, 0.3); padding: 0.4rem 0.8rem; border-radius: 20px; display: inline-block; font-size: 0.875rem; border: 1px solid rgba(46, 204, 113, 0.5);">
                        ✓ {{ $event->getAvailableSlots() }} slot tersisa
                    </div>
                    @else
                    <div style="background: rgba(231, 76, 60, 0.3); padding: 0.4rem 0.8rem; border-radius: 20px; display: inline-block; font-size: 0.875rem; border: 1px solid rgba(231, 76, 60, 0.5);">
                        ✕ Kuota penuh
                    </div>
                    @endif
                </div>
            </div>

            <!-- Periode Pendaftaran -->
            <div style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px; backdrop-filter: blur(10px);">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.2rem;">📅</span>
                    <strong style="font-size: 0.9rem;">Periode Pendaftaran</strong>
                </div>
                <div style="padding-left: 1.7rem; font-size: 0.95rem;">
                    {{ $event->registration_start->format('d M Y') }}<br>
                    s/d {{ $event->registration_end->format('d M Y') }}
                </div>
            </div>

            <!-- Periode Event -->
            <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px; backdrop-filter: blur(10px);">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.2rem;">🎯</span>
                    <strong style="font-size: 0.9rem;">Periode Event</strong>
                </div>
                <div style="padding-left: 1.7rem; font-size: 0.95rem;">
                    {{ $event->event_start->format('d M Y') }}<br>
                    s/d {{ $event->event_end->format('d M Y') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links Section -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <!-- Daftar Peserta -->
    <a href="{{ route('events.participants', $event) }}" class="btn-premium btn-premium-info" style="padding: 1.5rem; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 1rem;">
        <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            👥
        </div>
        <div style="flex: 1;">
            <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.25rem;">Daftar Peserta</div>
            <div style="font-size: 0.9rem; opacity: 0.9;">{{ $event->registrations->count() }} Peserta Terdaftar</div>
        </div>
        <div style="font-size: 1.5rem;">→</div>
    </a>

    <!-- Leaderboard -->
    @if($event->registrations->isNotEmpty())
    <a href="{{ route('jury.leaderboard', $event) }}" class="btn-premium btn-premium-warning" style="padding: 1.5rem; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 1rem;">
        <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            🏆
        </div>
        <div style="flex: 1;">
            <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.25rem;">Leaderboard</div>
            <div style="font-size: 0.9rem; opacity: 0.9;">Lihat Peringkat Peserta</div>
        </div>
        <div style="font-size: 1.5rem;">→</div>
    </a>
    @endif
</div>

<style>
    a[href*="participants"]:hover,
    a[href*="leaderboard"]:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    /* Responsive Styles */
    @media (max-width: 1024px) {
        .event-details-grid {
            grid-template-columns: 1fr 350px !important;
            gap: 1.5rem !important;
        }
    }

    @media (max-width: 768px) {

        /* Grid layouts stacking */
        .event-details-grid {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
        }

        .event-description,
        .event-info {
            padding: 1.25rem !important;
        }

        .event-description h3 {
            font-size: 1.25rem !important;
        }

        .event-info h3 {
            font-size: 1.15rem !important;
        }

        .event-description p {
            font-size: 0.95rem !important;
        }

        .event-info>div {
            margin-bottom: 1rem !important;
            padding: 0.85rem !important;
        }

        .card>div[style*="grid-template-columns: repeat(auto-fill"] {
            grid-template-columns: 1fr !important;
        }

        .card>div[style*="grid-template-columns: repeat(auto-fit"] {
            grid-template-columns: 1fr !important;
        }

        /* Banner image */
        .card img {
            max-height: 250px !important;
        }

        /* All padding adjustments */
        .card>div>div[style*="padding: 2rem"],
        .card div[style*="padding: 2rem"]:not(.event-description):not(.event-info) {
            padding: 1.25rem !important;
        }

        .card>div>div[style*="padding: 1.5rem"],
        .card div[style*="padding: 1.5rem"] {
            padding: 1rem !important;
        }

        .card>div>div[style*="padding: 1rem"],
        .card div[style*="padding: 1rem"]:not(.event-description):not(.event-info) {
            padding: 0.75rem !important;
        }

        /* Font sizes */
        .card h3[style*="font-size: 1.5rem"]:not(.event-description h3):not(.event-info h3) {
            font-size: 1.25rem !important;
        }

        .card h3[style*="font-size: 1.3rem"]:not(.event-info h3) {
            font-size: 1.1rem !important;
        }

        /* Info cards adjustments */
        .card div[style*="backdrop-filter: blur"] {
            padding: 0.75rem !important;
        }

        .card div[style*="font-size: 1.75rem"] {
            font-size: 1.4rem !important;
        }

        .card div[style*="font-size: 1.25rem"] {
            font-size: 1.1rem !important;
        }
    }

    @media (max-width: 480px) {
        .card {
            padding: 0.75rem !important;
        }

        .event-details-grid {
            gap: 1rem !important;
        }

        .event-description,
        .event-info {
            padding: 1rem !important;
            border-radius: 8px !important;
        }

        .event-description h3 {
            font-size: 1.1rem !important;
            margin-bottom: 0.75rem !important;
            padding-bottom: 0.4rem !important;
        }

        .event-info h3 {
            font-size: 1rem !important;
            margin-bottom: 0.75rem !important;
        }

        .event-description p {
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
        }

        .event-info>div {
            margin-bottom: 0.75rem !important;
            padding: 0.65rem !important;
        }

        .event-info div[style*="padding-left: 1.7rem"] {
            padding-left: 1.2rem !important;
            font-size: 0.85rem !important;
        }

        .event-info span[style*="font-size: 1.2rem"] {
            font-size: 1rem !important;
        }

        .event-info strong {
            font-size: 0.8rem !important;
        }

        .card img {
            max-height: 180px !important;
            border-radius: 6px !important;
        }

        /* More aggressive padding reduction for other elements */
        .card>div>div[style*="padding"],
        .card div[style*="padding: 2rem"]:not(.event-description):not(.event-info),
        .card div[style*="padding: 1.5rem"]:not(.event-description):not(.event-info),
        .card div[style*="padding: 1.25rem"]:not(.event-description):not(.event-info),
        .card div[style*="padding: 1rem"]:not(.event-description):not(.event-info) {
            padding: 0.5rem !important;
        }

        /* Font sizes for mobile */
        .card h3:not(.event-description h3):not(.event-info h3) {
            font-size: 1rem !important;
        }

        .card div[style*="font-size: 1.75rem"] {
            font-size: 1.3rem !important;
        }

        .card div[style*="font-size: 1.5rem"],
        .card div[style*="font-size: 1.4rem"] {
            font-size: 1.1rem !important;
        }

        .card div[style*="font-size: 1.25rem"],
        .card div[style*="font-size: 1.2rem"] {
            font-size: 1rem !important;
        }

        .card div[style*="font-size: 1rem"],
        .card div[style*="font-size: 0.95rem"] {
            font-size: 0.85rem !important;
        }

        .card div[style*="font-size: 0.9rem"],
        .card div[style*="font-size: 0.875rem"] {
            font-size: 0.8rem !important;
        }

        .card div[style*="border-radius: 20px"] {
            font-size: 0.75rem !important;
            padding: 0.3rem 0.6rem !important;
        }

        /* Gap adjustments */
        .card div[style*="gap: 2rem"] {
            gap: 0.75rem !important;
        }

        .card div[style*="gap: 1rem"] {
            gap: 0.5rem !important;
        }

        /* Icon sizes */
        .card span[style*="font-size: 1.8rem"],
        .card div[style*="font-size: 1.8rem"] {
            font-size: 1.3rem !important;
        }

        .card span[style*="font-size: 1.5rem"],
        .card div[style*="font-size: 1.5rem"] {
            font-size: 1.1rem !important;
        }

        .card span[style*="font-size: 1.2rem"] {
            font-size: 1rem !important;
        }

        /* Width and height adjustments */
        .card div[style*="width: 60px"],
        .card div[style*="height: 60px"] {
            width: 45px !important;
            height: 45px !important;
            font-size: 1.3rem !important;
        }

        /* Border radius adjustments */
        .card div[style*="border-radius: 12px"] {
            border-radius: 8px !important;
        }
    }
</style>

<div style="display: flex; gap: 0.5rem;">
    <a href="{{ route('events.index') }}" class="btn" style="background: #ddd;">Kembali</a>
</div>
</div>

<!-- Banner Modal -->
@if($event->banner)
<div id="bannerModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; padding: 2rem;" onclick="closeBannerModal()">
    <div style="position: relative; max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center;">
        <button onclick="closeBannerModal()" style="position: absolute; top: -3rem; right: 0; background: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 10000;">
            ✕
        </button>
        <img src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" style="max-width: 100%; max-height: 85vh; object-fit: contain; border-radius: 8px; box-shadow: 0 8px 32px rgba(0,0,0,0.5);">
        <div style="margin-top: 1rem; color: white; text-align: center; font-size: 1.1rem; text-shadow: 0 2px 4px rgba(0,0,0,0.8);">
            {{ $event->name }}
        </div>
    </div>
</div>

<script>
    function openBannerModal() {
        document.getElementById('bannerModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeBannerModal() {
        document.getElementById('bannerModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBannerModal();
        }
    });

    // Hover effect on banner
    document.getElementById('eventBanner')?.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.02)';
        this.style.boxShadow = '0 8px 24px rgba(0,0,0,0.2)';
    });

    document.getElementById('eventBanner')?.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
        this.style.boxShadow = 'none';
    });
</script>
@endif

@if($event->juryFormTemplate)
@push('scripts')
<script>
    function showJuryFormPreview() {
        const fields = @json($event->juryFormTemplate->criteria_fields);
        const useDefaultScore = {{ $event->juryFormTemplate->use_default_score ? 'true' : 'false' }};

        let totalMax = 0;
        let previewHTML = `
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 2rem;" onclick="this.remove()">
            <div style="background: white; border-radius: 8px; padding: 2rem; max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation()">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 2px solid #3498db; padding-bottom: 1rem;">
                    <h2 style="margin: 0; color: #3498db;">👁️ Preview Form Penilaian Juri</h2>
                    <button onclick="this.closest('div[style*=fixed]').remove()" style="background: #e74c3c; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 1rem;">✕ Tutup</button>
                </div>
                
                <div style="background: #e8f5e9; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #27ae60;">
                    <h3 style="margin-top: 0; color: #27ae60;">📊 Kriteria Penilaian</h3>
                    <p style="color: #666; margin-bottom: 1rem;">Ini adalah form yang akan dilihat oleh juri saat memberikan penilaian</p>
    `;

        fields.forEach((field, index) => {
            if (field.type === 'score') {
                totalMax += field.max;
                previewHTML += `
                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #ddd;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                        ${field.label}
                        ${field.required ? '<span style="color: red;">*</span>' : ''}
                        <span style="color: #27ae60; font-weight: bold;">(Max: ${field.max} poin)</span>
                    </label>
                    ${field.description ? `<p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                    <input 
                        type="number" 
                        style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                        placeholder="0 - ${field.max}"
                        min="0"
                        max="${field.max}"
                        disabled
                    >
                </div>
            `;
            } else if (field.type === 'textarea') {
                previewHTML += `
                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #ddd;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                        ${field.label}
                        ${field.required ? '<span style="color: red;">*</span>' : ''}
                    </label>
                    ${field.description ? `<p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                    <textarea 
                        style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                        rows="3"
                        disabled
                    ></textarea>
                </div>
            `;
            } else {
                previewHTML += `
                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #ddd;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                        ${field.label}
                        ${field.required ? '<span style="color: red;">*</span>' : ''}
                    </label>
                    ${field.description ? `<p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                    <input 
                        type="text" 
                        style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                        disabled
                    >
                </div>
            `;
            }
        });

        if (totalMax > 0) {
            previewHTML += `
            <div style="background: white; padding: 1rem; border-radius: 5px; margin-top: 1rem; border: 2px solid #27ae60;">
                <strong style="color: #27ae60; font-size: 1.125rem;">Total Maksimal Poin: ${totalMax}</strong>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #666;">
                    ${useDefaultScore ? '✓ Nilai akhir akan dihitung otomatis dari total kriteria penilaian' : 'Juri akan memasukkan nilai total manual'}
                </p>
            </div>
        `;
        }

        previewHTML += `
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Feedback Publik</label>
                    <textarea 
                        style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                        rows="4"
                        placeholder="Feedback yang dapat dilihat oleh peserta..."
                        disabled
                    ></textarea>
                    <small style="color: #666;">Feedback ini akan terlihat oleh peserta dan publik</small>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Evaluasi Private</label>
                    <textarea 
                        style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                        rows="4"
                        placeholder="Evaluasi internal untuk organizer dan admin..."
                        disabled
                    ></textarea>
                    <small style="color: #666;">Evaluasi ini hanya terlihat oleh organizer, admin, dan juri</small>
                </div>
                
                <div style="text-align: center; padding-top: 1rem; border-top: 2px solid #ddd;">
                    <button onclick="this.closest('div[style*=fixed]').remove()" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">
                        Tutup Preview
                    </button>
                </div>
            </div>
        </div>
    `;

        document.body.insertAdjacentHTML('beforeend', previewHTML);
    }
</script>
@endpush
@endif

@endsection