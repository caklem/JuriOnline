<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function store(Request $request, Event $event)
    {
        // Check if event is open for registration
        if (!$event->isRegistrationOpen()) {
            return back()->with('error', 'Pendaftaran event ini sudah ditutup atau kuota penuh.');
        }

        $user = auth()->user();

        // Check if user already registered
        $existingRegistration = Registration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingRegistration) {
            return back()->with('error', 'Anda sudah terdaftar di event ini.');
        }

        // Check email domain if event requires official email
        if ($event->require_official_email && $event->allowed_email_domains) {
            $userEmailDomain = substr(strrchr($user->email, "@"), 1);
            $allowedDomains = array_map('trim', explode(',', $event->allowed_email_domains));

            if (!in_array($userEmailDomain, $allowedDomains)) {
                return back()->with(
                    'error',
                    'Pendaftaran event ini hanya untuk email resmi dengan domain: ' .
                        implode(', ', $allowedDomains) . '. Email Anda (' . $user->email . ') tidak diizinkan.'
                );
            }
        }

        // Validate custom form responses if exists
        $formResponses = [];
        if ($event->eventForm) {
            $rules = [];
            foreach ($event->eventForm->form_fields as $index => $field) {
                $fieldName = "field_{$index}";
                $fieldRules = [];

                if ($field['required']) {
                    $fieldRules[] = 'required';
                }

                if ($field['type'] === 'email') {
                    $fieldRules[] = 'email';
                } elseif ($field['type'] === 'number') {
                    $fieldRules[] = 'numeric';
                } elseif ($field['type'] === 'url') {
                    $fieldRules[] = 'url';
                }

                if (!empty($fieldRules)) {
                    $rules[$fieldName] = implode('|', $fieldRules);
                }
            }

            $validated = $request->validate($rules);

            // Build responses array
            foreach ($event->eventForm->form_fields as $index => $field) {
                $fieldName = "field_{$index}";
                $formResponses[$field['label']] = $request->input($fieldName);
            }
        }

        try {
            DB::beginTransaction();

            // Re-check quota under a row lock: the earlier check runs outside the
            // transaction and would otherwise allow concurrent overbooking.
            $lockedEvent = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ($lockedEvent->status !== 'active'
                || $lockedEvent->registered_count >= $lockedEvent->quota) {
                DB::rollBack();

                return back()->with('error', 'Pendaftaran event ini sudah ditutup atau kuota penuh.');
            }

            // Create registration
            $registration = Registration::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'registration_number' => Registration::generateRegistrationNumber($event->id),
                'status' => 'pending',
                'registered_at' => now(),
            ]);

            // Save form responses if exists
            if (!empty($formResponses)) {
                RegistrationResponse::create([
                    'registration_id' => $registration->id,
                    'responses' => $formResponses
                ]);
            }

            // Increment registered count
            $lockedEvent->increment('registered_count');

            DB::commit();

            return redirect()->route('registrations.show', $registration)
                ->with('success', 'Pendaftaran berhasil! Silakan upload bukti pembayaran.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.');
        }
    }

    public function show(Registration $registration)
    {
        // Check authorization
        $user = auth()->user();

        if ($user->isParticipant() && $registration->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        if (!$user->isParticipant() && !$user->canManageEvent($registration->event)) {
            abort(403, 'Unauthorized action.');
        }

        $registration->load(['event', 'user', 'payment', 'juryScores.jury', 'response']);

        return view('registrations.show', compact('registration'));
    }

    public function myRegistrations()
    {
        $registrations = Registration::where('user_id', auth()->id())
            ->with(['event', 'payment'])
            ->latest()
            ->paginate(10);

        return view('registrations.my-registrations', compact('registrations'));
    }

    public function updateStatus(Request $request, Registration $registration)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,verified,rejected,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Check authorization
        $user = auth()->user();

        if (!$user->canManageEvent($registration->event)) {
            abort(403, 'Unauthorized action.');
        }

        $registration->update($validated);

        return back()->with('success', 'Status pendaftaran berhasil diupdate!');
    }

    public function participants(Event $event)
    {
        // Check authorization
        $user = auth()->user();

        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $registrations = $event->registrations()
            ->with(['user', 'payment', 'juryScores'])
            ->paginate(20);

        return view('registrations.participants', compact('event', 'registrations'));
    }
}
