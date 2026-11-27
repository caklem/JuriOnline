<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PublicEventController extends Controller
{
    /**
     * Daftar event yang dipublikasikan (status aktif) untuk halaman publik.
     */
    public function index(): JsonResponse
    {
        $events = Event::query()
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->with(['organizer', 'category'])
            ->latest()
            ->get()
            ->map(fn (Event $event) => $this->present($event))
            ->values();

        return response()->json([
            'events' => $events,
            'total' => $events->count(),
        ]);
    }

    /**
     * Detail satu event aktif. Event non-aktif dianggap tidak ada (404),
     * sama seperti EventController@publicShow pada aplikasi Blade.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $event = Event::query()->where('slug', $slug)->firstOrFail();

        return $this->show($event);
    }

    public function show(Event $event): JsonResponse
    {
        abort_unless($event->status === 'active' && $event->archived_at === null, 404, 'Event tidak ditemukan.');

        $event->load(['organizer', 'category']);

        return response()->json([
            'event' => $this->present($event),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Event $event): array
    {
        $status = $this->status($event);

        return [
            'id' => $event->id,
            'name' => $event->name,
            'slug' => $event->slug,
            'public_url' => '/events/' . $event->slug,
            'description' => $event->description,
            'format' => $event->format ?? 'offline',
            'timezone' => $event->timezone ?? 'Asia/Jakarta',
            'contact_name' => $event->contact_name,
            'contact_email' => $event->contact_email,
            'contact_phone' => $event->contact_phone,
            'category' => $event->category?->name,
            'organizer' => $event->organizer?->name,
            'location' => $event->location,
            'event_date' => $event->event_date?->toDateString(),
            'event_start' => $event->event_start?->toDateString(),
            'event_end' => $event->event_end?->toDateString(),
            'registration_start' => $event->registration_start?->toDateString(),
            'registration_end' => $event->registration_end?->toDateString(),
            'quota' => (int) $event->quota,
            'registered_count' => (int) $event->registered_count,
            'available_slots' => $event->getAvailableSlots(),
            'price' => (float) $event->price,
            'banner_url' => $event->banner ? url(Storage::url($event->banner)) : null,
            'juknis_name' => $event->juknis_name,
            'juknis_url' => $event->juknis_file ? Storage::url($event->juknis_file) : null,
            'status' => $event->status,
            'status_label' => $status['label'],
            'status_tone' => $status['tone'],
            'registration_open' => $event->isRegistrationOpen(),
            'registration_state' => $this->registrationState($event),
            'require_official_email' => (bool) $event->require_official_email,
            'allowed_email_domains' => $event->allowed_email_domains,
        ];
    }

    /**
     * Label & nada status yang sama dengan helper $eventStatus pada Blade.
     *
     * @return array{label: string, tone: string}
     */
    private function status(Event $event): array
    {
        $now = Carbon::now();

        if ($now->gt($event->event_end)) {
            return ['label' => 'Selesai', 'tone' => 'done'];
        }

        if ($event->isRegistrationOpen()) {
            return ['label' => 'Pendaftaran dibuka', 'tone' => 'open'];
        }

        if ($now->lt($event->registration_start)) {
            return ['label' => 'Segera dibuka', 'tone' => 'soon'];
        }

        if ($event->getAvailableSlots() <= 0) {
            return ['label' => 'Kuota penuh', 'tone' => 'full'];
        }

        return ['label' => 'Pendaftaran ditutup', 'tone' => 'closed'];
    }

    /**
     * Alasan pendaftaran tidak tersedia: before | after | full | inactive | open.
     */
    private function registrationState(Event $event): string
    {
        $now = Carbon::now();

        if ($event->status !== 'active') {
            return 'inactive';
        }

        if ($now->lt($event->registration_start)) {
            return 'before';
        }

        if ($now->gt($event->registration_end)) {
            return 'after';
        }

        if ($event->registered_count >= $event->quota) {
            return 'full';
        }

        return 'open';
    }
}
