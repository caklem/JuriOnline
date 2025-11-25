<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function create(Registration $registration)
    {
        // Check if user owns this registration
        if ($registration->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if payment already exists
        if ($registration->payment) {
            return redirect()->route('registrations.show', $registration)
                ->with('error', 'Pembayaran sudah diupload sebelumnya.');
        }

        return view('payments.create', compact('registration'));
    }

    public function store(Request $request, Registration $registration)
    {
        // Check if user owns this registration
        if ($registration->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if payment already exists
        if ($registration->payment) {
            return back()->with('error', 'Pembayaran sudah diupload sebelumnya.');
        }

        $validated = $request->validate([
            'proof_file' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $proofPath = $request->file('proof_file')->store('payment-proofs', 'public');

        Payment::create([
            'registration_id' => $registration->id,
            'amount' => $registration->event->price,
            'proof_file' => $proofPath,
            'status' => 'pending',
        ]);

        return redirect()->route('registrations.show', $registration)
            ->with('success', 'Bukti pembayaran berhasil diupload! Mohon tunggu verifikasi.');
    }

    public function verify(Request $request, Payment $payment)
    {
        // Check authorization
        $user = auth()->user();

        if (!$user->canVerifyPayments()) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->isOrganizer() && $payment->registration->event->organizer_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'verification_notes' => 'nullable|string',
        ]);

        $payment->update([
            'status' => $validated['status'],
            'verified_by' => $user->id,
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
        ]);

        // Update registration status
        if ($validated['status'] === 'verified') {
            $payment->registration->update(['status' => 'verified']);
        } else {
            $payment->registration->update(['status' => 'rejected']);
        }

        return back()->with('success', 'Pembayaran berhasil diverifikasi!');
    }

    public function pendingPayments()
    {
        $user = auth()->user();

        if ($user->isOrganizer()) {
            // Organizer only sees payments for their events
            $payments = Payment::where('status', 'pending')
                ->whereHas('registration.event', function ($query) use ($user) {
                    $query->where('organizer_id', $user->id);
                })
                ->with(['registration.event', 'registration.user'])
                ->latest()
                ->paginate(15);
        } else {
            // Admin and Admin Owner see all pending payments
            $payments = Payment::where('status', 'pending')
                ->with(['registration.event', 'registration.user'])
                ->latest()
                ->paginate(15);
        }

        return view('payments.pending', compact('payments'));
    }

    public function allPayments()
    {
        $user = auth()->user();

        if (!$user->isAdminOwner() && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $payments = Payment::with(['registration.event', 'registration.user', 'verifier'])
            ->latest()
            ->paginate(20);

        return view('payments.all', compact('payments'));
    }
}
