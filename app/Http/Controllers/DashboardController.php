<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdminOwner()) {
            return $this->adminOwnerDashboard();
        } elseif ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isJury()) {
            return $this->juryDashboard();
        } else {
            return $this->participantDashboard();
        }
    }

    private function adminOwnerDashboard()
    {
        $totalEvents = Event::count();
        $activeEvents = Event::where('status', 'active')->count();
        $draftEvents = Event::where('status', 'draft')->count();
        $totalRegistrations = Registration::count();
        $verifiedRegistrations = Registration::where('status', 'verified')->count();
        $totalRevenue = Payment::where('status', 'verified')->sum('amount');
        $pendingPayments = Payment::where('status', 'pending')->count();
        $pendingPaymentAmount = Payment::where('status', 'pending')->sum('amount');
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        $organizerStats = DB::table('events')
            ->join('users', 'users.id', '=', 'events.organizer_id')
            ->leftJoin('registrations', 'registrations.event_id', '=', 'events.id')
            ->leftJoin('payments', 'payments.registration_id', '=', 'registrations.id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('revenue')
            ->selectRaw(
                'users.id as user_id, users.name, users.email,
                count(distinct events.id) as events_count,
                count(distinct registrations.id) as registrations_count,
                coalesce(sum(case when payments.status = ? then payments.amount else 0 end), 0) as revenue',
                ['verified']
            )
            ->get();

        $roleDistribution = Role::withCount([
            'users',
            'users as active_users_count' => fn ($query) => $query->where('is_active', true),
        ])->orderByDesc('users_count')->get();

        $registrationTrend = collect(range(5, 0))->map(function (int $monthsAgo) {
            $start = now()->startOfMonth()->subMonths($monthsAgo);

            return [
                'label' => $start->format('M Y'),
                'count' => Registration::whereBetween('created_at', [$start, (clone $start)->endOfMonth()])->count(),
            ];
        });

        $topEvents = Event::with('category')
            ->withCount([
                'registrations',
                'registrations as verified_registrations_count' => fn ($query) => $query->where('status', 'verified'),
            ])
            ->orderByDesc('registrations_count')
            ->take(5)
            ->get();

        $recentEvents = Event::with(['organizer', 'category'])
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = Payment::with(['registration.event', 'registration.user'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.admin-owner', compact(
            'totalEvents',
            'activeEvents',
            'draftEvents',
            'totalRegistrations',
            'verifiedRegistrations',
            'totalRevenue',
            'pendingPayments',
            'pendingPaymentAmount',
            'totalUsers',
            'activeUsers',
            'organizerStats',
            'roleDistribution',
            'registrationTrend',
            'topEvents',
            'recentEvents',
            'recentPayments'
        ));
    }

    private function adminDashboard()
    {
        $user = auth()->user();

        $eventQuery = Event::query();
        $registrationQuery = Registration::query();
        $paymentQuery = Payment::where('status', 'pending');

        if (!$user->canManageAllEvents()) {
            $eventQuery->where('organizer_id', $user->id);
            $registrationQuery->whereHas('event', function ($query) use ($user) {
                $query->where('organizer_id', $user->id);
            });
            $paymentQuery->whereHas('registration.event', function ($query) use ($user) {
                $query->where('organizer_id', $user->id);
            });
        }

        $totalEvents = (clone $eventQuery)->count();
        $activeEvents = (clone $eventQuery)->where('status', 'active')->count();
        $pendingPayments = (clone $paymentQuery)->count();
        $totalRegistrations = (clone $registrationQuery)->count();

        $recentEvents = (clone $eventQuery)->with(['organizer', 'category'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalEvents',
            'activeEvents',
            'pendingPayments',
            'totalRegistrations',
            'recentEvents'
        ));
    }

    private function juryDashboard()
    {
        $user = auth()->user();

        $totalScored = $user->juryScores()->count();
        $hasAssignments = $user->acceptedJuryAssignments()->exists();
        $pendingInvitations = $user->pendingJuryAssignments()
            ->with(['event.organizer', 'juryFormTemplate'])
            ->latest()
            ->get();

        // Peserta terverifikasi di event yang sudah diterima DAN penilaiannya sudah
        // terbuka, tapi belum dinilai juri ini.
        $acceptedEvents = $user->assignedEvents()->get();
        $openEvents = $acceptedEvents->filter(fn ($event) => $event->isScoringOpen());
        $openEventIds = $openEvents->pluck('id');
        $waitingOpenEvents = $acceptedEvents->reject(fn ($event) => $event->isScoringOpen());

        $pendingScoreBase = Registration::query()
            ->whereIn('event_id', $openEventIds)
            ->where('status', 'verified')
            ->whereDoesntHave('juryScores', fn ($query) => $query->where('jury_id', $user->id));

        $pendingScoreCount = (clone $pendingScoreBase)->count();

        $pendingScores = (clone $pendingScoreBase)
            ->with(['user', 'event'])
            ->oldest('id')
            ->take(6)
            ->get();

        $pendingByEvent = (clone $pendingScoreBase)
            ->selectRaw('event_id, count(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        $eventsToScore = Event::whereIn('id', $openEventIds)
            ->where('status', 'active')
            ->whereHas('registrations', function ($query) {
                $query->where('status', 'verified');
            })
            ->count();

        $recentScores = $user->juryScores()
            ->with(['registration.user', 'registration.event'])
            ->latest()
            ->take(10)
            ->get();

        // Tanggal hari H terdekat dari event yang penilaiannya belum terbuka.
        $nextScoringDate = $waitingOpenEvents
            ->filter(fn ($event) => $event->scoringMode() === 'auto')
            ->map(fn ($event) => $event->scoringOpensAt())
            ->filter()
            ->sort()
            ->first();

        // Satu langkah berikutnya yang paling jelas untuk ditampilkan ke juri.
        $nextStep = match (true) {
            $pendingInvitations->isNotEmpty() => 'accept_invitation',
            $pendingScoreCount > 0 => 'score_participants',
            $hasAssignments && $openEvents->isNotEmpty() => 'all_done',
            $hasAssignments => 'waiting_open',
            default => 'waiting_invitation',
        };

        return view('dashboard.jury', compact(
            'totalScored',
            'eventsToScore',
            'recentScores',
            'hasAssignments',
            'pendingInvitations',
            'pendingScores',
            'pendingScoreCount',
            'pendingByEvent',
            'nextStep',
            'waitingOpenEvents',
            'nextScoringDate'
        ));
    }

    private function participantDashboard()
    {
        $user = auth()->user();

        $myRegistrations = Registration::where('user_id', $user->id)
            ->with(['event', 'payment', 'juryScores'])
            ->latest()
            ->get();

        $availableEvents = Event::where('status', 'active')
            ->where('registration_end', '>=', now())
            ->whereDoesntHave('registrations', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->withCount('registrations')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.participant', compact(
            'myRegistrations',
            'availableEvents'
        ));
    }
}
