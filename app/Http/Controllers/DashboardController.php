<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdminOwner()) {
            return $this->adminOwnerDashboard();
        } elseif ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isOrganizer()) {
            return $this->organizerDashboard();
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
        $totalRegistrations = Registration::count();
        $totalRevenue = Payment::where('status', 'verified')->sum('amount');
        $pendingPayments = Payment::where('status', 'pending')->count();

        $recentEvents = Event::with(['organizer', 'category'])
            ->latest()
            ->take(10)
            ->get();

        $recentPayments = Payment::with(['registration.event', 'registration.user'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.admin-owner', compact(
            'totalEvents',
            'activeEvents',
            'totalRegistrations',
            'totalRevenue',
            'pendingPayments',
            'recentEvents',
            'recentPayments'
        ));
    }

    private function adminDashboard()
    {
        $totalEvents = Event::count();
        $activeEvents = Event::where('status', 'active')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $totalRegistrations = Registration::count();

        $recentEvents = Event::with(['organizer', 'category'])
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

    private function organizerDashboard()
    {
        $user = auth()->user();

        $myEvents = Event::where('organizer_id', $user->id)->count();
        $activeEvents = Event::where('organizer_id', $user->id)
            ->where('status', 'active')
            ->count();
        $totalParticipants = Registration::whereHas('event', function ($query) use ($user) {
            $query->where('organizer_id', $user->id);
        })->count();
        $pendingPayments = Payment::where('status', 'pending')
            ->whereHas('registration.event', function ($query) use ($user) {
                $query->where('organizer_id', $user->id);
            })
            ->count();

        $events = Event::where('organizer_id', $user->id)
            ->withCount('registrations')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.organizer', compact(
            'myEvents',
            'activeEvents',
            'totalParticipants',
            'pendingPayments',
            'events'
        ));
    }

    private function juryDashboard()
    {
        $user = auth()->user();

        $totalScored = $user->juryScores()->count();
        $eventsToScore = Event::where('status', 'active')
            ->whereHas('registrations', function ($query) {
                $query->where('status', 'verified');
            })
            ->count();

        $recentScores = $user->juryScores()
            ->with(['registration.user', 'registration.event'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.jury', compact(
            'totalScored',
            'eventsToScore',
            'recentScores'
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
