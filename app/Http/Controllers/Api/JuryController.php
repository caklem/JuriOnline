<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventJuryFormAssignment;
use App\Models\JuryFormTemplate;
use App\Models\JuryScore;
use App\Models\ScoreRevisionRequest;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class JuryController extends Controller
{
    /**
     * Daftar undangan juri milik pengguna yang sedang login.
     */
    public function invitations(Request $request): JsonResponse
    {
        $user = $request->user();

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

        return response()->json([
            'pending' => $pending->map(fn (EventJuryFormAssignment $assignment) => [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'status_label' => $assignment->statusLabel(),
                'jury_role' => $assignment->jury_role,
                'form' => $assignment->juryFormTemplate?->name,
                'organizer' => $assignment->event?->organizer?->name,
                'created_at_label' => $assignment->created_at?->translatedFormat('d F Y, H:i'),
                'event' => $assignment->event ? [
                    'id' => $assignment->event->id,
                    'name' => $assignment->event->name,
                ] : null,
            ])->values(),
            'responded' => $responded->map(fn (EventJuryFormAssignment $assignment) => [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'status_label' => $assignment->statusLabel(),
                'jury_role' => $assignment->jury_role,
                'form' => $assignment->juryFormTemplate?->name,
                'responded_at_label' => $assignment->responded_at?->format('d M Y H:i'),
                'event' => $assignment->event ? [
                    'id' => $assignment->event->id,
                    'name' => $assignment->event->name,
                ] : null,
            ])->values(),
        ]);
    }

    public function acceptInvitation(Request $request, EventJuryFormAssignment $assignment): JsonResponse
    {
        return $this->respondToInvitation($request, $assignment, EventJuryFormAssignment::STATUS_ACCEPTED);
    }

    public function declineInvitation(Request $request, EventJuryFormAssignment $assignment): JsonResponse
    {
        return $this->respondToInvitation($request, $assignment, EventJuryFormAssignment::STATUS_DECLINED);
    }

    private function respondToInvitation(Request $request, EventJuryFormAssignment $assignment, string $status): JsonResponse
    {
        $user = $request->user();

        if ($assignment->jury_id !== $user->id) {
            abort(403, 'Undangan ini bukan milik Anda.');
        }

        if (!$assignment->isPending()) {
            return response()->json(['message' => 'Undangan ini sudah Anda jawab sebelumnya.'], 422);
        }

        $assignment->update([
            'status' => $status,
            'responded_at' => now(),
        ]);

        $assignment->load(['event.organizer', 'jury']);

        // Beri tahu panitia event bahwa undangan sudah dijawab.
        $assignment->event?->organizer?->notify(new \App\Notifications\JuryAssignmentResponded($assignment, $status));

        // Tandai notifikasi undangan terkait sebagai sudah dibaca.
        $user->unreadNotifications()
            ->where('data->assignment_id', $assignment->id)
            ->get()
            ->each->markAsRead();

        $message = $status === EventJuryFormAssignment::STATUS_ACCEPTED
            ? "Undangan diterima. Sekarang nilai peserta event \"{$assignment->event?->name}\" satu per satu."
            : "Undangan ditolak. Panitia event \"{$assignment->event?->name}\" akan melihat jawaban Anda.";

        return response()->json([
            'message' => $message,
            'status' => $status,
            'assignment_id' => $assignment->id,
            'event_id' => $assignment->event_id,
        ]);
    }

    /**
     * Event yang boleh dinilai (event dari assignment yang sudah diterima).
     */
    public function events(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->canManageEvents()) {
            $query = Event::where('status', 'active')
                ->with(['organizer', 'category', 'registrations']);

            if (!$user->canManageAllEvents()) {
                $query->where('organizer_id', $user->id);
            }

            $events = $query->latest()->paginate(10);
            $pendingByEvent = collect();
            $isManager = true;
        } else {
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
            $openEventIds = collect($events->items())
                ->filter(fn (Event $event) => $event->isScoringOpen())
                ->pluck('id');

            $pendingByEvent = Registration::query()
                ->whereIn('event_id', $openEventIds)
                ->where('status', 'verified')
                ->whereDoesntHave('juryScores', fn ($query) => $query->where('jury_id', $user->id))
                ->selectRaw('event_id, count(*) as total')
                ->groupBy('event_id')
                ->pluck('total', 'event_id');
            $isManager = false;
        }

        return response()->json([
            'events' => collect($events->items())->map(
                fn (Event $event) => $this->eventPayload(
                    $event,
                    $user,
                    (int) ($pendingByEvent[$event->id] ?? 0),
                    $isManager
                )
            )->values(),
            'meta' => $this->paginationMeta($events),
        ]);
    }

    /**
     * Peserta terverifikasi pada satu event beserta penilaian yang sudah masuk.
     */
    public function participants(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();
        $this->authorizeEventAccess($request, $event);

        $registrations = $event->registrations()
            ->where('status', 'verified')
            ->with(['user', 'juryScores.jury'])
            ->paginate(20);

        $verifiedTotal = $registrations->total();
        $scoringOpen = $event->isScoringOpen();
        $assignedFormTemplate = $user->canManageEvents() ? null : $user->getAssignedFormTemplate($event);

        $myScoredTotal = JuryScore::query()
            ->where('jury_id', $user->id)
            ->whereHas('registration', function ($query) use ($event) {
                $query->where('event_id', $event->id)->where('status', 'verified');
            })
            ->count();

        return response()->json([
            'event' => $this->eventSummary($event),
            'registrations' => collect($registrations->items())->map(
                fn (Registration $registration) => $this->registrationPayload($registration, $user)
            )->values(),
            'meta' => $this->paginationMeta($registrations),
            'scoring_open' => $scoringOpen,
            'scoring_mode' => $event->scoringMode(),
            'scoring_opens_at_label' => $event->scoringOpensAt()?->format('d M Y'),
            'verified_total' => $verifiedTotal,
            'my_scored_total' => $myScoredTotal,
            'progress_percent' => $verifiedTotal > 0 ? (int) round(($myScoredTotal / $verifiedTotal) * 100) : 0,
            'assigned_form' => $assignedFormTemplate ? $this->formPayload($assignedFormTemplate) : null,
        ]);
    }

    /**
     * Form penilaian untuk satu peserta (mode beri nilai maupun edit).
     */
    public function registrationForm(Request $request, Registration $registration): JsonResponse
    {
        $user = $request->user();
        $event = $registration->event;

        $this->authorizeEventAccess($request, $event);

        if ($registration->status !== 'verified') {
            abort(403, 'Peserta belum terverifikasi.');
        }

        $assignedFormTemplate = $user->getAssignedFormTemplate($event);

        if ($user->hasAcceptedAssignmentFor($event) && !$assignedFormTemplate) {
            abort(403, 'Form penilaian belum di-assign untuk Anda. Silakan hubungi admin.');
        }

        $formTemplate = $assignedFormTemplate
            ?? $event->juryFormTemplate
            ?? $event->juryFormTemplates()->first();

        $existingScore = JuryScore::where('registration_id', $registration->id)
            ->where('jury_id', $user->id)
            ->first();

        return response()->json([
            'event' => $this->eventSummary($event),
            'registration' => $this->registrationPayload($registration, $user),
            'form' => $formTemplate ? $this->formPayload($formTemplate) : null,
            'existing_score' => $existingScore ? $this->scorePayload($existingScore) : null,
            'average_score' => $registration->juryScores()->exists() ? (float) $registration->getAverageScore() : null,
            'scoring_open' => $event->isScoringOpen(),
            'scoring_mode' => $event->scoringMode(),
            'scoring_opens_at_label' => $event->scoringOpensAt()?->format('d M Y'),
        ]);
    }

    public function storeScore(Request $request, Registration $registration): JsonResponse
    {
        $user = $request->user();
        $event = $registration->event;

        $this->authorizeEventAccess($request, $event);

        if (!$event->isScoringOpen()) {
            return response()->json([
                'message' => 'Penilaian peserta belum dibuka. Penilaian otomatis dibuka pada hari H atau saat panitia mengaktifkannya.',
            ], 422);
        }

        if ($registration->status !== 'verified') {
            abort(403, 'Peserta belum terverifikasi.');
        }

        $existingScore = JuryScore::where('registration_id', $registration->id)
            ->where('jury_id', $user->id)
            ->first();

        if ($existingScore) {
            return response()->json(['message' => 'Anda sudah memberikan nilai untuk peserta ini.'], 422);
        }

        $validated = $this->validateBaseScore($request);

        $criteriaResponses = [];
        $juryFormTemplate = $this->resolveStoreFormTemplate($request, $event, $user);

        if ($juryFormTemplate) {
            [$criteriaRules, $criteriaMessages, $criteriaAttributes] = $this->criteriaRules($juryFormTemplate);
            $request->validate($criteriaRules, $criteriaMessages, $criteriaAttributes);

            $totalScore = 0;
            foreach ($juryFormTemplate->criteria_fields ?? [] as $index => $field) {
                $fieldName = "criteria_{$index}";

                if ($field['type'] === 'score') {
                    $criteriaScore = (float) $request->input($fieldName, 0);
                    $criteriaResponses[$field['label']] = [
                        'score' => $criteriaScore,
                        'max' => $field['max'],
                    ];
                    $totalScore += $criteriaScore;
                } else {
                    $criteriaResponses[$field['label']] = $request->input($fieldName);
                }
            }

            if ($juryFormTemplate->use_default_score && $totalScore > 0) {
                if ($totalScore > 100) {
                    throw ValidationException::withMessages(['score' => 'Total nilai melebihi 100.']);
                }

                $validated['score'] = $totalScore;
            }
        }

        $score = JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $user->id,
            'jury_form_template_id' => $juryFormTemplate?->id,
            'score' => $validated['score'],
            'public_feedback' => $validated['public_feedback'] ?? null,
            'private_evaluation' => $validated['private_evaluation'] ?? null,
            'criteria_responses' => !empty($criteriaResponses) ? $criteriaResponses : null,
            'form_snapshot' => $juryFormTemplate ? [
                'name' => $juryFormTemplate->name,
                'criteria_fields' => $juryFormTemplate->criteria_fields,
                'use_default_score' => (bool) $juryFormTemplate->use_default_score,
            ] : null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $nextRegistration = Registration::query()
            ->where('event_id', $event->id)
            ->where('status', 'verified')
            ->whereKeyNot($registration->getKey())
            ->whereDoesntHave('juryScores', fn ($query) => $query->where('jury_id', $user->id))
            ->oldest('id')
            ->first();

        $message = $nextRegistration
            ? "Nilai untuk {$registration->user->name} tersimpan. Lanjut menilai {$nextRegistration->user->name}."
            : "Nilai untuk {$registration->user->name} tersimpan. Semua peserta di event ini sudah Anda nilai.";

        return response()->json([
            'message' => $message,
            'score_id' => $score->id,
            'event_id' => $event->id,
            'next_registration_id' => $nextRegistration?->id,
            'next_registration_name' => $nextRegistration?->user?->name,
        ], 201);
    }

    public function showScore(Request $request, JuryScore $juryScore): JsonResponse
    {
        $user = $request->user();
        $event = $juryScore->registration->event;

        $this->authorizeScoreOwnership($request, $juryScore);

        if (!$event->isScoringOpen()) {
            abort(403, 'Penilaian event ini sedang ditutup.');
        }

        $registration = $juryScore->registration;
        $formTemplate = $juryScore->juryFormTemplate
            ?? $event->juryFormTemplate
            ?? $event->juryFormTemplates()->first();

        $formPayload = $juryScore->form_snapshot
            ? [
                'id' => $juryScore->jury_form_template_id,
                'name' => $juryScore->form_snapshot['name'] ?? 'Form Penilaian',
                'use_default_score' => (bool) ($juryScore->form_snapshot['use_default_score'] ?? true),
                'criteria_fields' => $juryScore->form_snapshot['criteria_fields'] ?? [],
            ]
            : ($formTemplate ? $this->formPayload($formTemplate) : null);

        return response()->json([
            'event' => $this->eventSummary($event),
            'registration' => $this->registrationPayload($registration, $user),
            'form' => $formPayload,
            'existing_score' => $this->scorePayload($juryScore),
            'average_score' => $registration->juryScores()->exists() ? (float) $registration->getAverageScore() : null,
            'scoring_open' => true,
            'scoring_mode' => $event->scoringMode(),
            'scoring_opens_at_label' => $event->scoringOpensAt()?->format('d M Y'),
        ]);
    }

    public function updateScore(Request $request, JuryScore $juryScore): JsonResponse
    {
        $user = $request->user();
        $event = $juryScore->registration->event;

        $this->authorizeScoreOwnership($request, $juryScore);

        if (!$event->isScoringOpen()) {
            abort(403, 'Penilaian event ini sedang ditutup.');
        }

        $validated = $this->validateBaseScore($request);

        $criteriaResponses = [];
        $juryFormTemplate = $juryScore->juryFormTemplate;

        if (!$juryFormTemplate) {
            $formTemplateId = $request->input('jury_form_template_id');
            if ($formTemplateId) {
                $juryFormTemplate = $event->juryFormTemplates()->find($formTemplateId);

                if (!$juryFormTemplate) {
                    return response()->json(['message' => 'Form penilaian tidak valid untuk event ini.'], 422);
                }
            } else {
                $juryFormTemplate = $event->juryFormTemplate ?? $event->juryFormTemplates()->first();
            }
        }

        if ($juryFormTemplate) {
            [$criteriaRules, $criteriaMessages, $criteriaAttributes] = $this->criteriaRules($juryFormTemplate);
            $request->validate($criteriaRules, $criteriaMessages, $criteriaAttributes);

            $totalScore = 0;
            foreach ($juryFormTemplate->criteria_fields ?? [] as $index => $field) {
                $fieldName = "criteria_{$index}";

                if ($field['type'] === 'score') {
                    $criteriaScore = (float) $request->input($fieldName, 0);
                    $criteriaResponses[$field['label']] = [
                        'score' => $criteriaScore,
                        'max' => $field['max'],
                    ];
                    $totalScore += $criteriaScore;
                } else {
                    $criteriaResponses[$field['label']] = $request->input($fieldName);
                }
            }

            if ($juryFormTemplate->use_default_score && $totalScore > 0) {
                if ($totalScore > 100) {
                    throw ValidationException::withMessages(['score' => 'Total nilai melebihi 100.']);
                }

                $validated['score'] = $totalScore;
            }
        }

        $validated['criteria_responses'] = !empty($criteriaResponses) ? $criteriaResponses : null;
        if ($juryFormTemplate) {
            $validated['jury_form_template_id'] = $juryFormTemplate->id;
        }

        if (!$juryScore->form_snapshot && $juryFormTemplate) {
            $validated['form_snapshot'] = [
                'name' => $juryFormTemplate->name,
                'criteria_fields' => $juryFormTemplate->criteria_fields,
                'use_default_score' => (bool) $juryFormTemplate->use_default_score,
            ];
        }

        $validated['status'] = 'submitted';
        $validated['submitted_at'] = now();
        $validated['published_at'] = null;
        $validated['reviewed_by'] = null;
        $validated['reviewed_at'] = null;

        $juryScore->update($validated);

        ScoreRevisionRequest::query()
            ->where('jury_score_id', $juryScore->id)
            ->where('jury_id', $user->id)
            ->where('status', ScoreRevisionRequest::STATUS_PENDING)
            ->update([
                'status' => ScoreRevisionRequest::STATUS_APPLIED,
                'resolved_by' => $user->id,
                'resolved_at' => now(),
            ]);

        return response()->json([
            'message' => 'Penilaian berhasil diupdate!',
            'event_id' => $event->id,
            'registration_id' => $juryScore->registration_id,
        ]);
    }

    public function leaderboard(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();
        $this->authorizeEventAccess($request, $event);

        $registrations = $event->registrations()
            ->where('status', 'verified')
            ->with(['user', 'juryScores.jury'])
            ->get()
            ->map(function (Registration $registration) {
                $registration->setAttribute('average_score', $registration->getAverageScore());
                return $registration;
            })
            ->sortByDesc('average_score')
            ->values();

        return response()->json([
            'event' => $this->eventSummary($event),
            'registrations' => $registrations->map(function (Registration $registration, int $index) {
                $scores = $registration->juryScores;

                return [
                    'registration_id' => $registration->id,
                    'rank' => $index + 1,
                    'user' => $registration->user ? [
                        'id' => $registration->user->id,
                        'name' => $registration->user->name,
                        'email' => $registration->user->email,
                    ] : null,
                    'average_score' => (float) ($registration->average_score ?? 0),
                    'jury_count' => $scores->count(),
                    'scores' => $scores->map(fn (JuryScore $score) => [
                        'id' => $score->id,
                        'jury_id' => $score->jury_id,
                        'jury_name' => $score->jury?->name,
                        'score' => (float) $score->score,
                        'public_feedback' => $score->public_feedback,
                    ])->values(),
                ];
            })->values(),
        ]);
    }

    /**
     * Pratinjau form penilaian yang di-assign ke juri.
     */
    public function form(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        if ($user->canManageEvents()) {
            if (!$user->canManageEvent($event)) {
                abort(403, 'Unauthorized action.');
            }

            $formTemplate = $event->juryFormTemplates()->orderBy('name')->first();
        } else {
            $this->authorizeEventAccess($request, $event);
            $formTemplate = $user->getAssignedFormTemplate($event);
        }

        if (!$formTemplate) {
            abort(404, 'Form penilaian belum diatur untuk event ini.');
        }

        return response()->json([
            'event' => $this->eventSummary($event),
            'form' => $this->formPayload($formTemplate),
        ]);
    }

    private function authorizeEventAccess(Request $request, Event $event): void
    {
        $user = $request->user();

        if ($user->canManageEvents()) {
            if (!$user->canManageEvent($event)) {
                abort(403, 'Unauthorized action.');
            }

            return;
        }

        if (!$user->hasAcceptedAssignmentFor($event)) {
            abort(403, 'Anda belum menerima undangan juri untuk event ini.');
        }
    }

    private function authorizeScoreOwnership(Request $request, JuryScore $juryScore): void
    {
        $user = $request->user();
        $event = $juryScore->registration->event;

        if ($juryScore->jury_id === $user->id) {
            if (!$user->hasAcceptedAssignmentFor($event)) {
                abort(403, 'Unauthorized action.');
            }

            return;
        }

        abort(403, 'Hanya juri pemilik penilaian yang boleh mengubah skor.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBaseScore(Request $request): array
    {
        return $request->validate([
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
    }

    private function resolveStoreFormTemplate(Request $request, Event $event, User $user): ?JuryFormTemplate
    {
        $assignedFormTemplate = $user->getAssignedFormTemplate($event);
        $formTemplateId = $request->input('jury_form_template_id');

        if ($formTemplateId) {
            $juryFormTemplate = $event->juryFormTemplates()->find($formTemplateId);

            if (!$juryFormTemplate) {
                abort(response()->json(['message' => 'Form penilaian tidak valid untuk event ini.'], 422));
            }

            if ($assignedFormTemplate && $juryFormTemplate->id !== $assignedFormTemplate->id) {
                abort(response()->json(['message' => 'Anda tidak diizinkan menggunakan form ini.'], 422));
            }

            return $juryFormTemplate;
        }

        return $assignedFormTemplate
            ?? $event->juryFormTemplate
            ?? $event->juryFormTemplates()->first();
    }

    /**
     * @return array{0: array<string, array<int, string>>, 1: array<string, string>, 2: array<string, string>}
     */
    private function criteriaRules(JuryFormTemplate $formTemplate): array
    {
        $rules = [];
        $attributes = [];

        foreach ($formTemplate->criteria_fields ?? [] as $index => $field) {
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

    /**
     * @return array<string, mixed>
     */
    private function eventPayload(Event $event, User $user, int $pendingCount, bool $isManager): array
    {
        $verifiedCount = $event->registrations->where('status', 'verified')->count();
        $assignedForm = $isManager ? null : $user->getAssignedFormTemplate($event);

        return array_merge($this->eventSummary($event), [
            'description_excerpt' => \Illuminate\Support\Str::limit((string) $event->description, 200),
            'event_date_label' => $this->eventDateLabel($event),
            'banner_url' => $event->banner ? url(Storage::url($event->banner)) : null,
            'participants_count' => $event->registrations->count(),
            'verified_count' => $verifiedCount,
            'pending_count' => $pendingCount,
            'assigned_form' => $assignedForm ? $this->formPayload($assignedForm) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventSummary(Event $event): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'status' => $event->status,
            'status_label' => $event->status === 'active' ? 'Aktif' : ucfirst((string) $event->status),
            'category' => $event->category?->name,
            'organizer' => $event->organizer?->name,
            'scoring_open' => $event->isScoringOpen(),
            'scoring_mode' => $event->scoringMode(),
            'scoring_label' => $event->scoringStatusLabel(),
            'scoring_opens_at_label' => $event->scoringOpensAt()?->format('d M Y'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function registrationPayload(Registration $registration, User $user): array
    {
        $scores = $registration->juryScores;

        return [
            'id' => $registration->id,
            'status' => $registration->status,
            'status_label' => $registration->status === 'verified' ? 'Terverifikasi' : ucfirst((string) $registration->status),
            'notes' => $registration->notes,
            'user' => $registration->user ? [
                'id' => $registration->user->id,
                'name' => $registration->user->name,
                'email' => $registration->user->email,
            ] : null,
            'average_score' => $scores->isNotEmpty() ? (float) $scores->avg('score') : null,
            'jury_count' => $scores->count(),
            'my_score' => ($mine = $scores->firstWhere('jury_id', $user->id)) ? [
                'id' => $mine->id,
                'score' => (float) $mine->score,
            ] : null,
            'scores' => $scores->map(fn (JuryScore $score) => [
                'id' => $score->id,
                'jury_id' => $score->jury_id,
                'jury_name' => $score->jury?->name,
                'score' => (float) $score->score,
            ])->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formPayload(JuryFormTemplate $form): array
    {
        return [
            'id' => $form->id,
            'name' => $form->name,
            'use_default_score' => (bool) $form->use_default_score,
            'criteria_fields' => $form->criteria_fields ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scorePayload(JuryScore $score): array
    {
        return [
            'id' => $score->id,
            'registration_id' => $score->registration_id,
            'jury_form_template_id' => $score->jury_form_template_id,
            'score' => (float) $score->score,
            'public_feedback' => $score->public_feedback,
            'private_evaluation' => $score->private_evaluation,
            'criteria_responses' => $score->criteria_responses,
            'created_at_label' => $score->created_at?->format('d M Y H:i'),
        ];
    }

    private function eventDateLabel(Event $event): string
    {
        if ($event->event_date) {
            return $event->event_date->format('d M Y');
        }

        if ($event->event_start && $event->event_end && $event->event_start->equalTo($event->event_end)) {
            return $event->event_start->format('d M Y');
        }

        if ($event->event_start) {
            return $event->event_start->format('d M Y')
                . ($event->event_end ? ' – ' . $event->event_end->format('d M Y') : '');
        }

        return 'Belum ditentukan';
    }

    /**
     * @return array{current_page: int, last_page: int, per_page: int, total: int}
     */
    private function paginationMeta(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    public function revisions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isJury()) {
            abort(403, 'Akses ditolak.');
        }

        $requests = ScoreRevisionRequest::query()
            ->with(['score.registration:id,registration_number,user_id,event_id', 'score.registration.user:id,name', 'event:id,name', 'requester:id,name'])
            ->where('jury_id', $user->id)
            ->where('status', ScoreRevisionRequest::STATUS_PENDING)
            ->latest()
            ->get()
            ->map(fn (ScoreRevisionRequest $revision) => [
                'id' => $revision->id,
                'reason' => $revision->reason,
                'created_at' => $revision->created_at?->toIso8601String(),
                'event' => ['id' => $revision->event?->id, 'name' => $revision->event?->name],
                'participant' => [
                    'name' => $revision->score?->registration?->user?->name,
                    'registration_number' => $revision->score?->registration?->registration_number,
                ],
                'score_id' => $revision->jury_score_id,
                'edit_url' => '/jury/scores/' . $revision->jury_score_id . '/edit',
            ]);

        return response()->json(['data' => $requests]);
    }

    public function rejectRevision(Request $request, ScoreRevisionRequest $revision): JsonResponse
    {
        $user = $request->user();

        if (!$user->isJury() || $revision->jury_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($revision->status !== ScoreRevisionRequest::STATUS_PENDING) {
            return response()->json(['message' => 'Permintaan revisi sudah diproses.'], 422);
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $revision->update([
            'status' => ScoreRevisionRequest::STATUS_REJECTED,
            'resolution_notes' => $validated['notes'],
            'resolved_by' => $user->id,
            'resolved_at' => now(),
        ]);

        return response()->json(['message' => 'Permintaan revisi ditolak dan panitia sudah diberi catatan.']);
    }
}