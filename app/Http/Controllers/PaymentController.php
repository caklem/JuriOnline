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
            'proof_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $proofPath = $request->file('proof_file')->store('payment-proofs', 'local');

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

        if (!$user->canManageEvent($payment->registration->event)) {
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

    /**
     * Serve a payment proof from the private disk to authorized viewers only:
     * the participant who owns the registration, or a manager of the event.
     */
    public function proof(Payment $payment)
    {
        $user = auth()->user();
        $registration = $payment->registration;

        if (!$registration) {
            abort(404);
        }

        $isOwner = $registration->user_id === $user->id;
        $canManage = $user->canVerifyPayments() && $user->canManageEvent($registration->event);

        if (!$isOwner && !$canManage) {
            abort(403, 'Unauthorized action.');
        }

        $disk = Storage::disk('local');

        if (!$payment->proof_file || !$disk->exists($payment->proof_file)) {
            abort(404);
        }

        return $disk->response($payment->proof_file);
    }

    public function pendingPayments()
    {
        $user = auth()->user();

        if (!$user->canVerifyPayments()) {
            abort(403, 'Unauthorized action.');
        }

        $query = Payment::where('status', 'pending')
            ->with(['registration.event', 'registration.user']);

        if (!$user->canManageAllEvents()) {
            $query->whereHas('registration.event', function ($query) use ($user) {
                $query->where('organizer_id', $user->id);
            });
        }

        $payments = $query->latest()->paginate(15);

        return view('payments.pending', compact('payments'));
    }

    public function allPayments()
    {
        $user = auth()->user();

        if (!$user->canManageAllEvents()) {
            abort(403, 'Unauthorized action.');
        }

        $payments = Payment::with(['registration.event', 'registration.user', 'verifier'])
            ->latest()
            ->paginate(20);

        return view('payments.all', compact('payments'));
    }
}
