<?php

namespace App\Http\Controllers;

use App\Models\EventJuryFormAssignment;
use App\Notifications\JuryAssignmentResponded;
use Illuminate\Http\Request;

class JuryInvitationController extends Controller
{
    /**
     * Daftar undangan menjadi juri untuk pengguna yang sedang login.
     */
    public function index()
    {
        $user = auth()->user();

        $pending = $user->juryFormAssignments()
            ->pending()
            ->with(['event.organizer', 'juryFormTemplate'])
            ->latest()
            ->get();

        $responded = $user->juryFormAssignments()
            ->whereIn('status', [
                EventJuryFormAssignment::STATUS_ACCEPTED,
                EventJuryFormAssignment::STATUS_DECLINED,
            ])
            ->with(['event', 'juryFormTemplate'])
            ->latest('responded_at')
            ->get();

        return view('jury.invitations', compact('pending', 'responded'));
    }

    /**
     * Juri menyetujui undangan.
     */
    public function accept(Request $request, EventJuryFormAssignment $assignment)
    {
        return $this->respond($request, $assignment, EventJuryFormAssignment::STATUS_ACCEPTED);
    }

    /**
     * Juri menolak undangan.
     */
    public function decline(Request $request, EventJuryFormAssignment $assignment)
    {
        return $this->respond($request, $assignment, EventJuryFormAssignment::STATUS_DECLINED);
    }

    private function respond(Request $request, EventJuryFormAssignment $assignment, string $status)
    {
        $user = $request->user();

        if ($assignment->jury_id !== $user->id) {
            abort(403, 'Undangan ini bukan milik Anda.');
        }

        if (!$assignment->isPending()) {
            return back()->with('error', 'Undangan ini sudah Anda jawab sebelumnya.');
        }

        $assignment->update([
            'status' => $status,
            'responded_at' => now(),
        ]);

        $assignment->load(['event.organizer', 'jury']);

        // Beri tahu panitia event bahwa undangan sudah dijawab.
        $assignment->event?->organizer?->notify(new JuryAssignmentResponded($assignment, $status));

        // Tandai notifikasi undangan terkait sebagai sudah dibaca.
        $user->unreadNotifications()
            ->where('data->assignment_id', $assignment->id)
            ->get()
            ->each->markAsRead();

        $message = $status === EventJuryFormAssignment::STATUS_ACCEPTED
            ? "Undangan diterima. Sekarang nilai peserta event \"{$assignment->event?->name}\" satu per satu."
            : "Undangan ditolak. Panitia event \"{$assignment->event?->name}\" akan melihat jawaban Anda.";

        // Setelah menerima, antar langsung ke daftar peserta agar alurnya jelas:
        // terima undangan -> pilih peserta -> isi nilai.
        if ($status === EventJuryFormAssignment::STATUS_ACCEPTED && $assignment->event) {
            return redirect()
                ->route('jury.participants', $assignment->event)
                ->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->each->markAsRead();

        return back();
    }
}
