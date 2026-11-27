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

        if ($user->canManageEvents()) {
            $query = Event::where('status', 'active')
                ->with(['organizer', 'category', 'registrations']);

            if (!$user->canManageAllEvents()) {
                $query->where('organizer_id', $user->id);
            }

            $events = $query->latest()->paginate(10);

            return view('jury.events', [
                'events' => $events,
                'assignedEventIds' => [],
                'pendingByEvent' => collect(),
            ]);
        }

        $assignedEventIds = $user->acceptedJuryAssignments()
            ->pluck('event_id')
            ->unique()
            ->toArray();

        $events = Event::where('status', 'active')
            ->whereIn('id', $assignedEventIds)
            ->with(['organizer', 'category', 'registrations'])
            ->latest()
            ->paginate(10);

        // Hanya event yang penilaiannya sudah terbuka yang punya tugas menilai.
        $openEventIds = $events->getCollection()
            ->filter(fn ($event) => $event->isScoringOpen())
            ->pluck('id');

        // Jumlah peserta terverifikasi yang belum dinilai juri ini, per event.
        $pendingByEvent = Registration::query()
            ->whereIn('event_id', $openEventIds)
            ->where('status', 'verified')
            ->whereDoesntHave('juryScores', fn ($query) => $query->where('jury_id', $user->id))
            ->selectRaw('event_id, count(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        return view('jury.events', compact('events', 'assignedEventIds', 'pendingByEvent'));
    }

    /**
     * Tampilkan isi form penilaian yang di-assign ke juri (read-only),
     * supaya juri tahu kriteria apa yang akan diisi sebelum menilai.
     */
    public function form(Event $event)
    {
        $user = auth()->user();

        if ($user->canManageEvents()) {
            if (!$user->canManageEvent($event)) {
                abort(403, 'Unauthorized action.');
            }

            $formTemplate = $event->juryFormTemplates()->orderBy('name')->first();
        } else {
            if (!$user->hasAcceptedAssignmentFor($event)) {
                abort(403, 'Anda belum menerima undangan juri untuk event ini.');
            }

            $formTemplate = $user->getAssignedFormTemplate($event);
        }

        if (!$formTemplate) {
            abort(404, 'Form penilaian belum diatur untuk event ini.');
        }

        return view('jury.form', compact('event', 'formTemplate'));
    }

    public function participants(Event $event)
    {
        $user = auth()->user();

        if ($user->canManageEvents()) {
            if (!$user->canManageEvent($event)) {
                abort(403, 'Unauthorized action.');
            }

            $assignedFormTemplate = null;
        } else {
            if (!$user->hasAcceptedAssignmentFor($event)) {
                abort(403, 'Anda belum menerima undangan juri untuk event ini.');
            }

            $assignedFormTemplate = $user->getAssignedFormTemplate($event);
        }

        $registrations = $event->registrations()
            ->where('status', 'verified')
            ->with(['user', 'juryScores.jury'])
            ->paginate(20);

        $verifiedTotal = $registrations->total();
        $scoringOpen = $event->isScoringOpen();

        $myScoredTotal = JuryScore::query()
            ->where('jury_id', $user->id)
            ->whereHas('registration', function ($query) use ($event) {
                $query->where('event_id', $event->id)->where('status', 'verified');
            })
            ->count();

        return view('jury.participants', compact(
            'event',
            'registrations',
            'assignedFormTemplate',
            'verifiedTotal',
            'myScoredTotal',
            'scoringOpen'
        ));
    }

    public function create(Registration $registration)
    {
        $user = auth()->user();
        $event = $registration->event;

        if ($user->canManageEvents()) {
            if (!$user->canManageEvent($event)) {
                abort(403, 'Unauthorized action.');
            }
        } elseif (!$user->hasAcceptedAssignmentFor($event)) {
            abort(403, 'Anda belum menerima undangan juri untuk event ini.');
        }

        if (!$event->isScoringOpen()) {
            return redirect()->route('jury.participants', $event)
                ->with('error', 'Penilaian peserta belum dibuka. Penilaian otomatis dibuka pada hari H atau saat panitia mengaktifkannya.');
        }

        if ($registration->status !== 'verified') {
            abort(403, 'Peserta belum terverifikasi.');
        }

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
        if ($user->hasAcceptedAssignmentFor($event)) {
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
        $event = $registration->event;

        if ($user->canManageEvents()) {
            if (!$user->canManageEvent($event)) {
                abort(403, 'Unauthorized action.');
            }
        } elseif (!$user->hasAcceptedAssignmentFor($event)) {
            abort(403, 'Anda belum menerima undangan juri untuk event ini.');
        }

        if (!$event->isScoringOpen()) {
            return back()->with('error', 'Penilaian peserta belum dibuka. Penilaian otomatis dibuka pada hari H atau saat panitia mengaktifkannya.');
        }

        if ($registration->status !== 'verified') {
            abort(403, 'Peserta belum terverifikasi.');
        }

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
        ], [
            'score.required' => 'Nilai wajib diisi.',
            'score.numeric' => 'Nilai harus berupa angka.',
            'score.min' => 'Nilai minimal :min.',
            'score.max' => 'Nilai maksimal :max.',
        ], [
            'score' => 'Nilai',
        ]);

        // Handle criteria responses if jury form exists
        $criteriaResponses = [];
        $juryFormTemplate = null;

        // Get assigned form template for this jury
        $assignedFormTemplate = $user->getAssignedFormTemplate($event);
        
        // Get form template - check if specific form template ID is provided, otherwise use assigned form
        $formTemplateId = $request->input('jury_form_template_id');
        if ($formTemplateId) {
            $juryFormTemplate = $event->juryFormTemplates()->find($formTemplateId);

            if (!$juryFormTemplate) {
                return back()->with('error', 'Form penilaian tidak valid untuk event ini.');
            }

            // Verify that this is the assigned form (security check)
            if ($assignedFormTemplate && $juryFormTemplate && $juryFormTemplate->id !== $assignedFormTemplate->id) {
                return back()->with('error', 'Anda tidak diizinkan menggunakan form ini.');
            }
        } else {
            // Use assigned form template, or fallback to first form (for backward compatibility)
            $juryFormTemplate = $assignedFormTemplate ?? $event->juryFormTemplate ?? $event->juryFormTemplates()->first();
        }

        if ($juryFormTemplate) {
            [$criteriaRules, $criteriaMessages, $criteriaAttributes] = $this->criteriaRules($juryFormTemplate);

            $request->validate($criteriaRules, $criteriaMessages, $criteriaAttributes);

            $totalScore = 0;
            foreach ($juryFormTemplate->criteria_fields as $index => $field) {
                $fieldName = "criteria_{$index}";

                if ($field['type'] === 'score') {
                    $criteriaScore = (float) $request->input($fieldName, 0);
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
                if ($totalScore > 100) {
                    return back()->withErrors(['score' => 'Total nilai melebihi 100.'])->withInput();
                }

                $validated['score'] = $totalScore;
            }
        }

        JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $user->id,
            'jury_form_template_id' => $juryFormTemplate ? $juryFormTemplate->id : null,
            'score' => $validated['score'],
            'public_feedback' => $validated['public_feedback'] ?? null,
            'private_evaluation' => $validated['private_evaluation'] ?? null,
            'criteria_responses' => !empty($criteriaResponses) ? $criteriaResponses : null,
        ]);

        // Alur untuk juri: setelah menyimpan, langsung antar ke peserta berikutnya
        // yang belum dinilai supaya tidak perlu mencari-cari lagi.
        $nextRegistration = Registration::query()
            ->where('event_id', $event->id)
            ->where('status', 'verified')
            ->whereKeyNot($registration->getKey())
            ->whereDoesntHave('juryScores', fn ($query) => $query->where('jury_id', $user->id))
            ->oldest('id')
            ->first();

        if ($nextRegistration) {
            return redirect()
                ->route('jury.score', $nextRegistration)
                ->with('success', "Nilai untuk {$registration->user->name} tersimpan. Lanjut menilai {$nextRegistration->user->name}.");
        }

        return redirect()->route('jury.participants', $registration->event)
            ->with('success', "Nilai untuk {$registration->user->name} tersimpan. Semua peserta di event ini sudah Anda nilai.");
    }

    public function edit(JuryScore $juryScore)
    {
        $user = auth()->user();
        $event = $juryScore->registration->event;

        if ($juryScore->jury_id !== $user->id || !$user->hasAcceptedAssignmentFor($event)) {
            abort(403, 'Hanya juri pemilik penilaian yang boleh mengubah skor.');
        }

        if (!$event->isScoringOpen()) {
            abort(403, 'Penilaian event ini sedang ditutup.');
        }

        $registration = $juryScore->registration;

        return view('jury.score-form', compact('registration', 'juryScore'));
    }

    public function update(Request $request, JuryScore $juryScore)
    {
        $user = auth()->user();
        $event = $juryScore->registration->event;

        if ($juryScore->jury_id !== $user->id || !$user->hasAcceptedAssignmentFor($event)) {
            abort(403, 'Hanya juri pemilik penilaian yang boleh mengubah skor.');
        }

        if (!$event->isScoringOpen()) {
            abort(403, 'Penilaian event ini sedang ditutup.');
        }

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'public_feedback' => 'nullable|string',
            'private_evaluation' => 'nullable|string',
        ], [
            'score.required' => 'Nilai wajib diisi.',
            'score.numeric' => 'Nilai harus berupa angka.',
            'score.min' => 'Nilai minimal :min.',
            'score.max' => 'Nilai maksimal :max.',
        ], [
            'score' => 'Nilai',
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

                if (!$juryFormTemplate) {
                    return back()->with('error', 'Form penilaian tidak valid untuk event ini.');
                }
            } else {
                $juryFormTemplate = $event->juryFormTemplate ?? $event->juryFormTemplates()->first();
            }
        }

        if ($juryFormTemplate) {
            [$criteriaRules, $criteriaMessages, $criteriaAttributes] = $this->criteriaRules($juryFormTemplate);

            $request->validate($criteriaRules, $criteriaMessages, $criteriaAttributes);

            $totalScore = 0;
            foreach ($juryFormTemplate->criteria_fields as $index => $field) {
                $fieldName = "criteria_{$index}";

                if ($field['type'] === 'score') {
                    $criteriaScore = (float) $request->input($fieldName, 0);
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
                if ($totalScore > 100) {
                    return back()->withErrors(['score' => 'Total nilai melebihi 100.'])->withInput();
                }

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
        $user = auth()->user();

        if ($user->canManageEvents()) {
            if (!$user->canManageEvent($event)) {
                abort(403, 'Unauthorized action.');
            }
        } elseif (!$user->hasAcceptedAssignmentFor($event)) {
            abort(403, 'Anda belum menerima undangan juri untuk event ini.');
        }

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
        if (!auth()->user()->canManageAllEvents()) {
            abort(403, 'Unauthorized action.');
        }

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
        if (!$user->canManageEvent($event)) {
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

            // Preserve legacy scores that were saved before form templates existed
            $legacyScores = $scores->filter(fn ($score) => empty($score->jury_form_template_id));
            $legacyScore = $legacyScores->isNotEmpty() ? (float) ($legacyScores->avg('score') ?? 0) : 0;
            
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

            if ($legacyScores->isNotEmpty()) {
                $formFinalScores[] = $legacyScore;
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
                'legacy_score' => round($legacyScore, 2), // Skor lama tanpa form template
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
        $hasLegacyScores = $recapData->contains(fn ($data) => ($data['legacy_score'] ?? 0) > 0);

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

        return view('jury.recap', compact('event', 'recapData', 'juryFormTemplates', 'totalFormTemplates', 'allJuries', 'totalJuries', 'hasLegacyScores'));
    }

    /**
     * Build validation rules, Indonesian messages, and human labels for the
     * criteria attached to the jury's assigned form.
     *
     * @return array{0: array<string, array<int, string>>, 1: array<string, string>, 2: array<string, string>}
     */
    private function criteriaRules($formTemplate): array
    {
        $rules = [];
        $attributes = [];

        foreach ($formTemplate->criteria_fields as $index => $field) {
            $fieldName = "criteria_{$index}";
            $presence = !empty($field['required']) ? 'required' : 'nullable';

            $rules[$fieldName] = $field['type'] === 'score'
                ? [$presence, 'numeric', 'min:0', 'max:' . ($field['max'] ?? 100)]
                : [$presence, 'string', 'max:2000'];

            $attributes[$fieldName] = $field['label'] ?? ('Kriteria ' . ($index + 1));
        }

        $messages = [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute tidak boleh kurang dari :min.',
            'max' => ':attribute tidak boleh lebih dari :max.',
            'string' => ':attribute harus berupa teks.',
        ];

        return [$rules, $messages, $attributes];
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
