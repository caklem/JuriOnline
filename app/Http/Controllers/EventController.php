<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventQuotaService;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->canManageEvents()) {
            abort(403, 'Unauthorized action.');
        }

        $eventsQuery = Event::with(['organizer', 'category']);

        if (!$user->canManageAllEvents()) {
            $eventsQuery->where('organizer_id', $user->id);
        }

        $events = $eventsQuery->latest()->paginate(15);

        return view('events.index', compact('events'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:1',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'event_date' => 'required|date|after_or_equal:event_start|before_or_equal:event_end',
            'event_start' => 'required|date',
            'event_end' => 'required|date|after_or_equal:event_start',
            'location' => 'nullable|string|max:255',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], $this->dateValidationMessages());

        $validated['status'] = 'draft';

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('banners', 'public');
        }

        try {
            $event = app(EventQuotaService::class)->create(auth()->user(), $validated);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            if (isset($validated['banner'])) {
                Storage::disk('public')->delete($validated['banner']);
            }
            throw $exception;
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event berhasil dibuat!');
    }

    public function show(Event $event)
    {
        $event->load(['organizer', 'category', 'registrations.user', 'registrations.payment']);

        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $categories = Category::all();
        return view('events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:' . $event->registered_count,
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'event_date' => 'required|date|after_or_equal:event_start|before_or_equal:event_end',
            'event_start' => 'required|date',
            'event_end' => 'required|date|after_or_equal:event_start',
            'location' => 'nullable|string|max:255',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:draft,active,closed,completed',
        ], $this->dateValidationMessages());

        if ($request->hasFile('banner')) {
            // Delete old banner
            if ($event->banner) {
                Storage::disk('public')->delete($event->banner);
            }
            $validated['banner'] = $request->file('banner')->store('banners', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event berhasil diupdate!');
    }

    public function destroy(Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        // Delete banner if exists
        if ($event->banner) {
            Storage::disk('public')->delete($event->banner);
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event berhasil dihapus!');
    }

    public function updateStatus(Event $event, Request $request)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,active,closed,completed'
        ]);

        $event->update(['status' => $validated['status']]);

        $statusText = [
            'draft' => 'Draft',
            'active' => 'Aktif',
            'closed' => 'Ditutup',
            'completed' => 'Selesai'
        ];

        return redirect()->back()
            ->with('success', "Status event berhasil diubah menjadi {$statusText[$validated['status']]}!");
    }

    /**
     * Buka / tutup penilaian peserta, atau kembalikan ke mode otomatis (hari H).
     */
    public function updateScoring(Request $request, Event $event)
    {
        $user = auth()->user();

        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'mode' => 'required|in:open,closed,auto',
        ], [
            'mode.in' => 'Mode penilaian tidak dikenali.',
        ]);

        $event->update([
            'scoring_enabled' => match ($validated['mode']) {
                'open' => true,
                'closed' => false,
                default => null,
            },
        ]);

        $message = match ($validated['mode']) {
            'open' => 'Penilaian peserta dibuka. Juri sekarang bisa menilai peserta.',
            'closed' => 'Penilaian peserta ditutup. Juri tidak bisa menilai sampai dibuka lagi.',
            default => 'Penilaian kembali ke mode otomatis: terbuka sejak tanggal hari H.',
        };

        return back()->with('success', $message);
    }

    // Public view for participants
    public function publicIndex()
    {
        // If user is authenticated, show events page
        if (auth()->check()) {
            $events = Event::where('status', 'active')->whereNull('archived_at')
                ->with(['organizer', 'category'])
                ->latest()
                ->paginate(12);

            return view('events.public', compact('events'));
        }
        
        // If guest, show landing page
        return view('home');
    }

    public function publicShow(Event $event)
    {
        if ($event->status !== 'active' || $event->archived_at !== null) {
            abort(404);
        }

        $event->load(['organizer', 'category']);
        $userRegistration = null;

        if (auth()->check()) {
            $userRegistration = $event->registrations()
                ->where('user_id', auth()->id())
                ->with('payment')
                ->first();
        }

        return view('events.detail', compact('event', 'userRegistration'));
    }

    /**
     * Pesan validasi tanggal (hari H & periode) dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    private function dateValidationMessages(): array
    {
        return [
            'registration_end.after' => 'Tanggal akhir pendaftaran harus setelah tanggal mulai pendaftaran.',
            'event_end.after_or_equal' => 'Tanggal akhir event tidak boleh sebelum tanggal mulai event.',
            'event_date.required' => 'Tanggal hari H wajib diisi.',
            'event_date.date' => 'Tanggal hari H harus berupa tanggal yang valid.',
            'event_date.after_or_equal' => 'Tanggal hari H tidak boleh sebelum tanggal mulai event.',
            'event_date.before_or_equal' => 'Tanggal hari H tidak boleh setelah tanggal akhir event.',
        ];
    }
}
