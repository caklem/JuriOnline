<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\JuryScore;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('role');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->name,
                'role_label' => $user->roleLabel(),
                'event_scope' => $user->event_scope,
            ],
            'summary' => $this->summary($user),
        ]);
    }

    /**
     * @return array<int, array{label: string, value: int, hint?: string}>
     */
    private function summary(User $user): array
    {
        if ($user->isAdminOwner()) {
            return [
                ['label' => 'Event', 'value' => Event::count()],
                ['label' => 'Peserta', 'value' => Registration::count()],
                ['label' => 'Pembayaran Menunggu', 'value' => Payment::where('status', 'pending')->count()],
                ['label' => 'Pengguna', 'value' => User::count()],
            ];
        }

        if ($user->isAdmin()) {
            $eventIds = $user->canManageAllEvents()
                ? Event::query()->pluck('id')
                : Event::where('organizer_id', $user->id)->pluck('id');

            return [
                ['label' => 'Event', 'value' => $eventIds->count()],
                ['label' => 'Peserta', 'value' => Registration::whereIn('event_id', $eventIds)->count()],
                [
                    'label' => 'Pembayaran Menunggu',
                    'value' => Payment::where('status', 'pending')
                        ->whereHas('registration', fn ($query) => $query->whereIn('event_id', $eventIds))
                        ->count(),
                ],
                [
                    'label' => 'Penilaian Juri',
                    'value' => JuryScore::whereHas('registration', fn ($query) => $query->whereIn('event_id', $eventIds))->count(),
                ],
            ];
        }

        if ($user->isJury()) {
            $acceptedEvents = $user->assignedEvents()->get();
            $openEvents = $acceptedEvents->filter(fn (Event $event) => $event->isScoringOpen());
            $openEventIds = $openEvents->pluck('id');

            $pendingScores = Registration::query()
                ->whereIn('event_id', $openEventIds)
                ->where('status', 'verified')
                ->whereDoesntHave('juryScores', fn ($query) => $query->where('jury_id', $user->id))
                ->count();

            return [
                ['label' => 'Event Di-assign', 'value' => $acceptedEvents->count()],
                ['label' => 'Undangan Menunggu', 'value' => $user->pendingJuryAssignments()->count()],
                ['label' => 'Belum Dinilai', 'value' => $pendingScores],
                ['label' => 'Sudah Dinilai', 'value' => $user->juryScores()->count()],
            ];
        }

        return [
            ['label' => 'Pendaftaran', 'value' => Registration::where('user_id', $user->id)->count()],
            ['label' => 'Terverifikasi', 'value' => Registration::where('user_id', $user->id)->where('status', 'verified')->count()],
            [
                'label' => 'Pembayaran Menunggu',
                'value' => Payment::where('status', 'pending')
                    ->whereHas('registration', fn ($query) => $query->where('user_id', $user->id))
                    ->count(),
            ],
        ];
    }
}
