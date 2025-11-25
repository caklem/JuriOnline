<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\JuryScore;
use Illuminate\Http\Request;

class JuryScoreController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get events where this jury is assigned, or all active events if no assignment (backward compatibility)
        $assignedEventIds = $user->juryFormAssignments()
            ->pluck('event_id')
            ->unique()
            ->toArray();

        if (!empty($assignedEventIds)) {
            // Only show events where jury is assigned
            $events = Event::where('status', 'active')
                ->whereIn('id', $assignedEventIds)
                ->with(['organizer', 'category', 'registrations'])
                ->latest()
                ->paginate(10);
        } else {
            // Backward compatibility: show all active events if no assignment
            $events = Event::where('status', 'active')
                ->with(['organizer', 'category', 'registrations'])
                ->latest()
                ->paginate(10);
        }

        return view('jury.events', compact('events', 'assignedEventIds'));
    }

    public function participants(Event $event)
    {
        $user = auth()->user();

        // Check if jury is assigned to this event
        $assignedFormTemplate = $user->getAssignedFormTemplate($event);
        
        // If assignment exists, verify jury can access this event
        if ($user->juryFormAssignments()->where('event_id', $event->id)->exists()) {
            // Jury is assigned, allow access
        } else {
            // Check if there are any assignments at all
            if ($user->juryFormAssignments()->exists()) {
                // Jury has assignments but not for this event - deny access
                abort(403, 'Anda tidak di-assign untuk event ini.');
            }
            // No assignments at all - allow access (backward compatibility)
        }

        $registrations = $event->registrations()
            ->where('status', 'verified')
            ->with(['user', 'juryScores' => function ($query) use ($user) {
                $query->where('jury_id', $user->id);
            }])
            ->paginate(20);

        return view('jury.participants', compact('event', 'registrations', 'assignedFormTemplate'));
    }

    public function create(Registration $registration)
    {
        $user = auth()->user();
        $event = $registration->event;

        // Check if already scored by this jury
        $existingScore = JuryScore::where('registration_id', $registration->id)
            ->where('jury_id', $user->id)
            ->first();

        if ($existingScore) {
            return redirect()->route('jury.edit-score', $existingScore);
        }

        // Get assigned form template for this jury
        $assignedFormTemplate = $user->getAssignedFormTemplate($event);
        
        // If assignment exists, enforce it
        if ($user->juryFormAssignments()->where('event_id', $event->id)->exists()) {
            if (!$assignedFormTemplate) {
                abort(403, 'Form penilaian belum di-assign untuk Anda. Silakan hubungi admin.');
            }
        } else {
            // If no assignment, fallback to first form (for backward compatibility)
            if (!$assignedFormTemplate) {
                $assignedFormTemplate = $event->juryFormTemplate ?? $event->juryFormTemplates()->first();
            }
        }

        return view('jury.score-form', compact('registration', 'assignedFormTemplate'));
    }

    public function store(Request $request, Registration $registration)
    {
        $user = auth()->user();

        // Check if already scored
        $existingScore = JuryScore::where('registration_id', $registration->id)
            ->where('jury_id', $user->id)
            ->first();

        if ($existingScore) {
            return back()->with('error', 'Anda sudah memberikan nilai untuk peserta ini.');
        }

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'public_feedback' => 'nullable|string',
            'private_evaluation' => 'nullable|string',
        ]);

        // Handle criteria responses if jury form exists
        $criteriaResponses = [];
        $event = $registration->event;
        $juryFormTemplate = null;

        // Get assigned form template for this jury
        $assignedFormTemplate = $user->getAssignedFormTemplate($event);
        
        // Get form template - check if specific form template ID is provided, otherwise use assigned form
        $formTemplateId = $request->input('jury_form_template_id');
        if ($formTemplateId) {
            $juryFormTemplate = $event->juryFormTemplates()->find($formTemplateId);
            // Verify that this is the assigned form (security check)
            if ($assignedFormTemplate && $juryFormTemplate && $juryFormTemplate->id !== $assignedFormTemplate->id) {
                return back()->with('error', 'Anda tidak diizinkan menggunakan form ini.');
            }
        } else {
            // Use assigned form template, or fallback to first form (for backward compatibility)
            $juryFormTemplate = $assignedFormTemplate ?? $event->juryFormTemplate ?? $event->juryFormTemplates()->first();
        }

        if ($juryFormTemplate) {
            $totalScore = 0;
            foreach ($juryFormTemplate->criteria_fields as $index => $field) {
                $fieldName = "criteria_{$index}";

                if ($field['type'] === 'score') {
                    $criteriaScore = $request->input($fieldName, 0);
                    $criteriaResponses[$field['label']] = [
                        'score' => $criteriaScore,
                        'max' => $field['max']
                    ];
                    $totalScore += $criteriaScore;
                } else {
                    $criteriaResponses[$field['label']] = $request->input($fieldName);
                }
            }

            // If using criteria scoring, calculate final score
            if ($juryFormTemplate->use_default_score && $totalScore > 0) {
                $validated['score'] = $totalScore;
            }
        }

        JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $user->id,
            'jury_form_template_id' => $juryFormTemplate ? $juryFormTemplate->id : null,
            'score' => $validated['score'],
            'public_feedback' => $validated['public_feedback'],
            'private_evaluation' => $validated['private_evaluation'],
            'criteria_responses' => !empty($criteriaResponses) ? $criteriaResponses : null,
        ]);

        return redirect()->route('jury.participants', $registration->event)
            ->with('success', 'Penilaian berhasil disimpan!');
    }

    public function edit(JuryScore $juryScore)
    {
        // Check if jury owns this score
        if ($juryScore->jury_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $registration = $juryScore->registration;

        return view('jury.score-form', compact('registration', 'juryScore'));
    }

    public function update(Request $request, JuryScore $juryScore)
    {
        // Check if jury owns this score
        if ($juryScore->jury_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'public_feedback' => 'nullable|string',
            'private_evaluation' => 'nullable|string',
        ]);

        // Handle criteria responses if jury form exists
        $criteriaResponses = [];
        $event = $juryScore->registration->event;
        $juryFormTemplate = $juryScore->juryFormTemplate;

        // If no form template linked, try to get from event
        if (!$juryFormTemplate) {
            $formTemplateId = $request->input('jury_form_template_id');
            if ($formTemplateId) {
                $juryFormTemplate = $event->juryFormTemplates()->find($formTemplateId);
            } else {
                $juryFormTemplate = $event->juryFormTemplate ?? $event->juryFormTemplates()->first();
            }
        }

        if ($juryFormTemplate) {
            $totalScore = 0;
            foreach ($juryFormTemplate->criteria_fields as $index => $field) {
                $fieldName = "criteria_{$index}";

                if ($field['type'] === 'score') {
                    $criteriaScore = $request->input($fieldName, 0);
                    $criteriaResponses[$field['label']] = [
                        'score' => $criteriaScore,
                        'max' => $field['max']
                    ];
                    $totalScore += $criteriaScore;
                } else {
                    $criteriaResponses[$field['label']] = $request->input($fieldName);
                }
            }

            // If using criteria scoring, calculate final score
            if ($juryFormTemplate->use_default_score && $totalScore > 0) {
                $validated['score'] = $totalScore;
            }
        }

        $validated['criteria_responses'] = !empty($criteriaResponses) ? $criteriaResponses : null;
        if ($juryFormTemplate) {
            $validated['jury_form_template_id'] = $juryFormTemplate->id;
        }

        $juryScore->update($validated);

        return redirect()->route('jury.participants', $juryScore->registration->event)
            ->with('success', 'Penilaian berhasil diupdate!');
    }

    public function leaderboard(Event $event)
    {
        $registrations = $event->registrations()
            ->where('status', 'verified')
            ->with(['user', 'juryScores'])
            ->get()
            ->map(function ($registration) {
                $registration->average_score = $registration->getAverageScore();
                return $registration;
            })
            ->sortByDesc('average_score')
            ->values();

        return view('jury.leaderboard', compact('event', 'registrations'));
    }

    public function allScores()
    {
        // Admin can view all scores from all events
        $scores = JuryScore::with(['registration.event', 'registration.user', 'jury'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_scores' => JuryScore::count(),
            'total_events_scored' => Event::whereHas('registrations.juryScores')->count(),
            'average_score' => round(JuryScore::avg('score'), 2),
            'total_juries' => JuryScore::distinct('jury_id')->count('jury_id'),
        ];

        return view('jury.all-scores', compact('scores', 'stats'));
    }

    public function recap(Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if ($user->isOrganizer() && $event->organizer_id !== $user->id && !$user->isAdmin() && !$user->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }

        // Get all verified registrations with their scores and form templates
        $registrations = $event->registrations()
            ->where('status', 'verified')
            ->with(['user', 'juryScores.jury', 'juryScores.juryFormTemplate', 'response'])
            ->get();

        // Eager load jury form templates for the event
        $event->load('juryFormTemplates');

        // Get all jury form templates for this event
        $juryFormTemplates = $event->juryFormTemplates()->get();
        $totalFormTemplates = $juryFormTemplates->count();

        // Process each registration
        $recapData = $registrations->map(function ($registration) use ($juryFormTemplates, $totalFormTemplates) {
            $scores = $registration->juryScores;
            
            if ($scores->isEmpty()) {
                return null;
            }

            // Group scores by form template
            $scoresByForm = $scores->groupBy('jury_form_template_id');
            
            // Calculate final score per form (nilai akhir per form)
            $formFinalScores = [];
            $formDetails = [];
            
            foreach ($scoresByForm as $formTemplateId => $formScores) {
                $formTemplate = $juryFormTemplates->firstWhere('id', $formTemplateId);
                
                if (!$formTemplate) {
                    continue;
                }
                
                $criteriaFields = $formTemplate->criteria_fields ?? [];
                $formFinalScore = 0;
                
                // Calculate total score for this form (sum of all criteria)
                if (!empty($criteriaFields)) {
                    foreach ($criteriaFields as $field) {
                        if ($field['type'] === 'score') {
                            $criteriaLabel = $field['label'];
                            
                            // Get all scores for this criteria from all juries who filled this form
                            $criteriaScores = [];
                            foreach ($formScores as $score) {
                                if ($score->criteria_responses && isset($score->criteria_responses[$criteriaLabel])) {
                                    $response = $score->criteria_responses[$criteriaLabel];
                                    if (is_array($response) && isset($response['score'])) {
                                        $criteriaScores[] = $response['score'];
                                    }
                                }
                            }
                            
                            // Average score for this criteria
                            if (!empty($criteriaScores)) {
                                $avgCriteriaScore = array_sum($criteriaScores) / count($criteriaScores);
                                $formFinalScore += $avgCriteriaScore;
                            }
                        }
                    }
                } else {
                    // If no criteria form, use average score
                    $formFinalScore = $formScores->avg('score') ?? 0;
                }
                
                $formFinalScores[] = $formFinalScore;
                $formDetails[] = [
                    'name' => $formTemplate->name ?? 'Form Juri',
                    'score' => round($formFinalScore, 2),
                ];
            }
            
            // Calculate average: sum of all form final scores / number of forms
            $averageScore = !empty($formFinalScores) && $totalFormTemplates > 0 
                ? array_sum($formFinalScores) / $totalFormTemplates 
                : 0;
            
            $data = [
                'registration' => $registration,
                'registration_number' => $registration->registration_number,
                'participant_name' => $registration->user->name,
                'address' => $this->getAddressFromResponse($registration),
                'form_details' => $formDetails, // Detail per form
                'total_score' => round(array_sum($formFinalScores), 2), // Total dari semua form
                'average_score' => round($averageScore, 8), // Rata-rata dari semua form
                'jury_count' => $scores->count(), // Jumlah juri yang sudah memberikan nilai
                'form_count' => count($formFinalScores), // Jumlah form yang sudah diisi
            ];

            return $data;
        })->filter()->values();

        // Sort by average score descending
        $recapData = $recapData->sortByDesc('average_score')->values();

        // Assign ranks (handle ties) - convert to array first
        $recapDataArray = $recapData->toArray();
        $rank = 1;
        $previousScore = null;
        foreach ($recapDataArray as $index => &$data) {
            if ($previousScore !== null && abs($data['average_score'] - $previousScore) > 0.00000001) {
                $rank = $index + 1;
            }
            $data['rank'] = $rank;
            $previousScore = $data['average_score'];
        }
        unset($data); // Unset reference
        
        // Convert back to collection
        $recapData = collect($recapDataArray);

        // Get all unique juries who have scored this event
        $allJuries = JuryScore::whereHas('registration', function($query) use ($event) {
                $query->where('event_id', $event->id);
            })
            ->with('jury')
            ->get()
            ->pluck('jury')
            ->unique('id')
            ->values();

        // Get total number of juries
        $totalJuries = $allJuries->count();

        return view('jury.recap', compact('event', 'recapData', 'juryFormTemplates', 'totalFormTemplates', 'allJuries', 'totalJuries'));
    }

    private function getAddressFromResponse($registration)
    {
        if ($registration->response && $registration->response->responses) {
            $responses = is_string($registration->response->responses) 
                ? json_decode($registration->response->responses, true) 
                : $registration->response->responses;
            
            // Try to find address field
            foreach ($responses as $key => $value) {
                if (stripos($key, 'alamat') !== false || stripos($key, 'address') !== false) {
                    return $value;
                }
            }
        }
        
        return '-';
    }
}
