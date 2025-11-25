<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdminOwner()) {
            // Admin Owner can see all events
            $events = Event::with(['organizer', 'category'])->latest()->paginate(15);
        } elseif ($user->isOrganizer()) {
            // Organizer can see their own events
            $events = Event::where('organizer_id', $user->id)
                ->with(['organizer', 'category'])
                ->latest()
                ->paginate(15);
        } else {
            // Admin can see all events
            $events = Event::with(['organizer', 'category'])->latest()->paginate(15);
        }

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
            'event_start' => 'required|date',
            'event_end' => 'required|date|after:event_start',
            'location' => 'nullable|string|max:255',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['organizer_id'] = auth()->id();
        $validated['status'] = 'draft';

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('banners', 'public');
        }

        $event = Event::create($validated);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event berhasil dibuat!');
    }

    public function show(Event $event)
    {
        $event->load(['organizer', 'category', 'registrations.user', 'registrations.payment']);

        // Check authorization
        $user = auth()->user();
        if ($user->isOrganizer() && $event->organizer_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if ($user->isOrganizer() && $event->organizer_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $categories = Category::all();
        return view('events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if ($user->isOrganizer() && $event->organizer_id !== $user->id) {
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
            'event_start' => 'required|date',
            'event_end' => 'required|date|after:event_start',
            'location' => 'nullable|string|max:255',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:draft,active,closed,completed',
        ]);

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
        if ($user->isOrganizer() && $event->organizer_id !== $user->id) {
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
        if ($user->isOrganizer() && $event->organizer_id !== $user->id) {
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

    // Public view for participants
    public function publicIndex()
    {
        // If user is authenticated, show events page
        if (auth()->check()) {
            $events = Event::where('status', 'active')
                ->where('registration_end', '>=', now())
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
        if ($event->status !== 'active') {
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
}
