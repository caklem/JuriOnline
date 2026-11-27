<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventForm;
use App\Models\CustomEventFormTemplate;
use App\Models\EventJuryFormAssignment;
use App\Models\JuryFormTemplate;
use App\Models\JuryScore;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Role;
use App\Models\ScoreRevisionRequest;
use App\Models\User;
use App\Notifications\JuryAssignmentInvited;
use App\Notifications\ScoreRevisionRequested;
use App\Services\AuditLogger;
use App\Services\EventQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Daftar event yang boleh dikelola pengguna (semua event atau miliknya).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->guardAdmin($request);

        $query = Event::query()
            ->with(['organizer:id,name', 'category:id,name'])
            ->withCount('registrations');

        if (!$user->canManageAllEvents()) {
            $query->where('organizer_id', $user->id);
        }

        $search = trim((string) $request->string('q'));

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'archived') {
                $query->whereNotNull('archived_at');
            } else {
                $query->where('status', $request->input('status'))->whereNull('archived_at');
            }
        }

        $events = $query->latest()->paginate(15)->withQueryString();

        return response()->json([
            'can_manage_all' => $user->canManageAllEvents(),
            'title' => $user->canManageAllEvents() ? 'Semua Event' : 'Event Saya',
            'description' => $user->canManageAllEvents()
                ? 'Kelola seluruh event dan penyelenggara dari sini.'
                : 'Kelola event yang Anda selenggarakan.',
            'data' => collect($events->items())
                ->map(fn (Event $event) => $this->serializeEvent($event))
                ->values(),
            'filters' => [
                'q' => $search,
                'status' => $request->input('status', ''),
            ],
            'meta' => $this->meta($events),
        ]);
    }

    /**
     * Detail event beserta ringkasan peserta, pembayaran, dan form.
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $event->load(['organizer:id,name,email', 'category:id,name', 'eventForm:id,event_id']);
        $event->loadCount('registrations');

        $registrationsByStatus = $event->registrations()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        $paymentsPending = Payment::query()
            ->where('status', 'pending')
            ->whereHas('registration', fn ($query) => $query->where('event_id', $event->id))
            ->count();

        $juryCount = $event->juryFormAssignments()->count();

        return response()->json([
            'event' => $this->serializeEvent($event),
            'stats' => [
                'registrations' => (int) $event->registrations_count,
                'verified' => (int) ($registrationsByStatus['verified'] ?? 0),
                'pending' => (int) ($registrationsByStatus['pending'] ?? 0),
                'rejected' => (int) ($registrationsByStatus['rejected'] ?? 0),
                'payments_pending' => $paymentsPending,
                'juries' => $juryCount,
            ],
            'has_event_form' => $event->eventForm !== null,
            'jury_form_templates' => $event->juryFormTemplates()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (JuryFormTemplate $template) => [
                    'id' => $template->id,
                    'name' => $template->name,
                ])->values(),
        ]);
    }

    /**
     * Daftar kategori untuk form event.
     */
    /**
     * Ringkasan dashboard admin/event admin.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->canManageEvents()) {
            abort(403, 'Akses ditolak.');
        }

        $eventsQuery = Event::query();

        if (!$user->canManageAllEvents()) {
            $eventsQuery->where('organizer_id', $user->id);
        }

        $eventIds = (clone $eventsQuery)->pluck('id');

        $pendingPayments = Payment::where('status', 'pending')
            ->whereHas('registration', fn ($query) => $query->whereIn('event_id', $eventIds))
            ->count();

        $recentEvents = (clone $eventsQuery)
            ->with('organizer:id,name')
            ->withCount('registrations')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'name' => $event->name,
                'status' => $event->status,
                'status_label' => $this->eventStatusLabel($event->status),
                'registered_count' => $event->registered_count,
                'quota' => $event->quota,
                'registrations_count' => $event->registrations_count,
                'organizer' => $event->organizer?->name,
                'event_date' => $event->event_date?->format('Y-m-d'),
            ]);

        return response()->json([
            'can_manage_all' => $user->canManageAllEvents(),
            'stats' => [
                ['label' => 'Total Event', 'value' => $eventIds->count()],
                ['label' => 'Event Aktif', 'value' => (clone $eventsQuery)->where('status', 'active')->count()],
                ['label' => 'Pembayaran Pending', 'value' => $pendingPayments],
                [
                    'label' => 'Total Pendaftaran',
                    'value' => Registration::whereIn('event_id', $eventIds)->count(),
                ],
            ],
            'recent_events' => $recentEvents,
            'event_quota' => app(EventQuotaService::class)->quotaFor($user),
            'organizer_profile_complete' => (bool) $user->organizerProfile,
        ]);
    }

    /**
     * Detail pendaftaran untuk admin/panitia event.
     */
    public function registrationDetail(Request $request, Registration $registration): JsonResponse
    {
        $event = $registration->event;
        $this->guardEvent($request, $event);

        $registration->load(['user', 'payment.verifier', 'response', 'files', 'juryScores.jury']);

        return response()->json([
            'registration' => [
                'id' => $registration->id,
                'registration_number' => $registration->registration_number,
                'team_name' => $registration->team_name,
                'team_members' => $registration->team_members,
                'status' => $registration->status,
                'status_label' => $this->registrationStatusLabel($registration->status),
                'notes' => $registration->notes,
                'registered_at' => $registration->registered_at?->toIso8601String(),
                'user' => [
                    'name' => $registration->user?->name,
                    'email' => $registration->user?->email,
                ],
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'event_date' => $event->event_date?->format('Y-m-d'),
                ],
                'payment' => $registration->payment ? [
                    'id' => $registration->payment->id,
                    'status' => $registration->payment->status,
                    'status_label' => $this->paymentStatusLabel($registration->payment->status),
                    'amount' => (float) $registration->payment->amount,
                    'verification_notes' => $registration->payment->verification_notes,
                    'verified_at' => $registration->payment->verified_at?->toIso8601String(),
                    'verifier' => $registration->payment->verifier?->name,
                ] : null,
                'responses' => $registration->response?->responses ?? [],
                'files' => $registration->files->map(fn (\App\Models\RegistrationFile $file) => [
                    'id' => $file->id,
                    'label' => $file->field_label,
                    'name' => $file->original_name,
                    'size' => $file->size,
                    'url' => '/registrations/' . $registration->id . '/files/' . $file->id,
                ])->values(),
                'scores' => $registration->juryScores->map(function (JuryScore $score) {
                    $pendingRevision = ScoreRevisionRequest::query()
                        ->where('jury_score_id', $score->id)
                        ->where('status', ScoreRevisionRequest::STATUS_PENDING)
                        ->latest()
                        ->first();

                    return [
                        'id' => $score->id,
                        'jury' => $score->jury?->name,
                        'score' => (float) $score->score,
                        'public_feedback' => $score->public_feedback,
                        'pending_revision' => $pendingRevision ? [
                            'id' => $pendingRevision->id,
                            'reason' => $pendingRevision->reason,
                            'created_at' => $pendingRevision->created_at?->toIso8601String(),
                        ] : null,
                    ];
                })->values(),
            ],
        ]);
    }

    public function categories(Request $request): JsonResponse
    {
        $this->guardAdmin($request);

        return response()->json([
            'data' => Category::orderBy('name')->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])->values(),
        ]);
    }

    /**
     * Buat event baru (selalu berstatus draft, penyelenggara = pembuat).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $this->guardAdmin($request);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:1',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'event_date' => 'required|date|after_or_equal:event_start|before_or_equal:event_end',
            'event_start' => 'required|date',
            'event_end' => 'required|date|after_or_equal:event_start',
            'location' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|alpha_dash|unique:events,slug',
            'format' => 'nullable|in:offline,online,hybrid',
            'participation_mode' => 'nullable|in:individual,team',
            'team_min_members' => 'nullable|integer|min:1|max:20',
            'team_max_members' => 'nullable|integer|min:1|max:20|gte:team_min_members',
            'timezone' => 'nullable|timezone',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:40',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'juknis' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240',
            'remove_juknis' => 'nullable|boolean',
        ], $this->eventValidationMessages());

        $validated['status'] = 'draft';
        $validated['category_id'] = $validated['category_id'] ?? null;
        $validated['location'] = $validated['location'] ?? null;
        $validated['format'] = $validated['format'] ?? 'offline';
        $validated['participation_mode'] = $validated['participation_mode'] ?? 'individual';
        $validated['team_min_members'] = $validated['team_min_members'] ?? 2;
        $validated['team_max_members'] = $validated['team_max_members'] ?? 5;
        $validated['timezone'] = $validated['timezone'] ?? 'Asia/Jakarta';
        if (empty($validated['slug'])) {
            unset($validated['slug']);
        }

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('banners', 'public');
        }

        if ($request->hasFile('juknis')) {
            $file = $request->file('juknis');
            $validated['juknis_file'] = $file->store('juknis', 'public');
            $validated['juknis_name'] = $file->getClientOriginalName();
        }

        unset($validated['remove_juknis']);

        try {
            $event = app(EventQuotaService::class)->create($user, $validated);
        } catch (ValidationException $exception) {
            if (isset($validated['banner'])) {
                Storage::disk('public')->delete($validated['banner']);
            }
            if (isset($validated['juknis_file'])) {
                Storage::disk('public')->delete($validated['juknis_file']);
            }
            throw $exception;
        }
        $event->load(['organizer:id,name', 'category:id,name']);

        return response()->json([
            'message' => 'Event berhasil dibuat!',
            'event' => $this->serializeEvent($event),
        ], 201);
    }

    /**
     * Perbarui event. Menerima multipart (POST) maupun JSON (PUT).
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:' . $event->registered_count,
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'event_date' => 'required|date|after_or_equal:event_start|before_or_equal:event_end',
            'event_start' => 'required|date',
            'event_end' => 'required|date|after_or_equal:event_start',
            'location' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|alpha_dash|unique:events,slug,' . $event->id,
            'format' => 'nullable|in:offline,online,hybrid',
            'participation_mode' => 'nullable|in:individual,team',
            'team_min_members' => 'nullable|integer|min:1|max:20',
            'team_max_members' => 'nullable|integer|min:1|max:20|gte:team_min_members',
            'timezone' => 'nullable|timezone',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:40',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'juknis' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240',
            'remove_juknis' => 'nullable|boolean',
            'status' => 'required|in:draft,active,closed,completed,archived',
        ], $this->eventValidationMessages($event));

        $validated['category_id'] = $validated['category_id'] ?? null;
        $validated['location'] = $validated['location'] ?? null;
        if (empty($validated['slug'])) {
            unset($validated['slug']);
        }
        $validated['format'] = $validated['format'] ?? 'offline';
        $validated['participation_mode'] = $validated['participation_mode'] ?? $event->participation_mode;
        $validated['team_min_members'] = $validated['team_min_members'] ?? $event->team_min_members;
        $validated['team_max_members'] = $validated['team_max_members'] ?? $event->team_max_members;
        $validated['timezone'] = $validated['timezone'] ?? 'Asia/Jakarta';

        if (($validated['status'] ?? null) === 'archived') {
            $validated['archived_at'] = $event->archived_at ?? now();
            unset($validated['status']);
        } else {
            $validated['archived_at'] = null;
        }

        if ($request->hasFile('banner')) {
            if ($event->banner) {
                Storage::disk('public')->delete($event->banner);
            }
            $validated['banner'] = $request->file('banner')->store('banners', 'public');
        }

        if ($request->hasFile('juknis')) {
            if ($event->juknis_file) {
                Storage::disk('public')->delete($event->juknis_file);
            }
            $file = $request->file('juknis');
            $validated['juknis_file'] = $file->store('juknis', 'public');
            $validated['juknis_name'] = $file->getClientOriginalName();
        } elseif ($request->boolean('remove_juknis') && $event->juknis_file) {
            Storage::disk('public')->delete($event->juknis_file);
            $validated['juknis_file'] = null;
            $validated['juknis_name'] = null;
        }

        unset($validated['remove_juknis']);

        $event->update($validated);
        $event->load(['organizer:id,name', 'category:id,name']);
        $event->loadCount('registrations');

        return response()->json([
            'message' => 'Event berhasil diupdate!',
            'event' => $this->serializeEvent($event),
        ]);
    }

    /**
     * Ubah status event (Aktifkan / Tutup / Selesaikan).
     */
    public function updateStatus(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $validated = $request->validate([
            'status' => 'required|in:draft,active,closed,completed,archived',
        ], [
            'status.required' => 'Status event wajib dipilih.',
            'status.in' => 'Status event tidak valid.',
        ]);

        if ($validated['status'] === 'archived') {
            $event->update(['archived_at' => $event->archived_at ?? now()]);
        } else {
            $event->update([
                'status' => $validated['status'],
                'archived_at' => null,
            ]);
        }

        $statusText = [
            'draft' => 'Draft',
            'active' => 'Aktif',
            'closed' => 'Ditutup',
            'completed' => 'Selesai',
            'archived' => 'Diarsipkan',
        ];

        return response()->json([
            'message' => "Status event berhasil diubah menjadi {$statusText[$validated['status']]}!",
            'event' => $this->serializeEvent($event->fresh(['organizer:id,name', 'category:id,name'])),
        ]);
    }

    /**
     * Buka / tutup penilaian peserta, atau kembalikan ke mode otomatis (hari H).
     */
    public function updateScoring(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $validated = $request->validate([
            'mode' => 'required|in:open,closed,auto',
        ], [
            'mode.required' => 'Mode penilaian wajib dipilih.',
            'mode.in' => 'Mode penilaian tidak dikenali.',
        ]);

        $event->update([
            'scoring_enabled' => match ($validated['mode']) {
                'open' => true,
                'closed' => false,
                default => null,
            },
        ]);

        $message = match ($validated['mode']) {
            'open' => 'Penilaian peserta dibuka. Juri sekarang bisa menilai peserta.',
            'closed' => 'Penilaian peserta ditutup. Juri tidak bisa menilai sampai dibuka lagi.',
            default => 'Penilaian kembali ke mode otomatis: terbuka sejak tanggal hari H.',
        };

        return response()->json([
            'message' => $message,
            'event' => $this->serializeEvent($event->fresh(['organizer:id,name', 'category:id,name'])),
        ]);
    }

    /**
     * Daftar peserta event beserta status pendaftaran dan pembayaran.
     */
    public function participants(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $registrations = $event->registrations()
            ->with(['user:id,name,email', 'payment'])
            ->withCount('juryScores')
            ->withAvg('juryScores', 'score')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $registrationsByStatus = $event->registrations()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        return response()->json([
            'event' => ['id' => $event->id, 'name' => $event->name],
            'stats' => [
                'total' => $event->registrations()->count(),
                'verified' => (int) ($registrationsByStatus['verified'] ?? 0),
                'pending' => (int) ($registrationsByStatus['pending'] ?? 0),
                'rejected' => (int) ($registrationsByStatus['rejected'] ?? 0),
            ],
            'data' => collect($registrations->items())->map(function (Registration $registration) {
                return [
                    'id' => $registration->id,
                    'registration_number' => $registration->registration_number,
                    'status' => $registration->status,
                    'status_label' => $this->registrationStatusLabel($registration->status),
                    'notes' => $registration->notes,
                    'registered_at' => $registration->registered_at?->toIso8601String() ?? $registration->created_at?->toIso8601String(),
                    'user' => [
                        'id' => $registration->user?->id,
                        'name' => $registration->user?->name,
                        'email' => $registration->user?->email,
                    ],
                    'payment' => $registration->payment ? [
                        'id' => $registration->payment->id,
                        'status' => $registration->payment->status,
                        'status_label' => $this->paymentStatusLabel($registration->payment->status),
                    ] : null,
                    'average_score' => round((float) ($registration->jury_scores_avg_score ?? 0), 2),
                    'scores_count' => (int) $registration->jury_scores_count,
                ];
            })->values(),
            'meta' => $this->meta($registrations),
        ]);
    }

    /**
     * Ubah status pendaftaran peserta.
     */
    public function updateRegistrationStatus(Request $request, Registration $registration): JsonResponse
    {
        $registration->load('event');

        if (!$registration->event || !$request->user()->canManageEvent($registration->event)) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,verified,rejected,cancelled',
            'notes' => 'nullable|string',
        ], [
            'status.required' => 'Status pendaftaran wajib dipilih.',
            'status.in' => 'Status pendaftaran tidak valid.',
        ]);

        $registration->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $registration->notes,
        ]);

        return response()->json([
            'message' => 'Status pendaftaran berhasil diupdate!',
            'registration' => [
                'id' => $registration->id,
                'status' => $registration->status,
                'status_label' => $this->registrationStatusLabel($registration->status),
            ],
        ]);
    }

    /**
     * Pembayaran yang menunggu verifikasi (mengikuti cakupan event admin).
     */
    public function pendingPayments(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->canVerifyPayments()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'nullable|in:pending,verified,rejected',
            'event' => 'nullable|integer|exists:events,id',
        ], [
            'status.in' => 'Status pembayaran tidak valid.',
            'event.exists' => 'Event tidak ditemukan.',
        ]);

        $status = $validated['status'] ?? 'pending';
        $eventId = $validated['event'] ?? null;

        $query = Payment::query()
            ->where('status', $status)
            ->with(['registration:id,event_id,user_id,registration_number', 'registration.event:id,name', 'registration.user:id,name']);

        if ($eventId) {
            $query->whereHas('registration', fn ($query) => $query->where('event_id', $eventId));
        }

        if (!$user->canManageAllEvents()) {
            $query->whereHas('registration.event', function ($query) use ($user) {
                $query->where('organizer_id', $user->id);
            });
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        return response()->json([
            'data' => collect($payments->items())
                ->map(fn (Payment $payment) => $this->serializePayment($payment))
                ->values(),
            'pending_total' => $payments->total(),
            'meta' => $this->meta($payments),
        ]);
    }

    /**
     * Riwayat seluruh pembayaran (hanya admin dengan akses semua event).
     */
    public function allPayments(Request $request): JsonResponse
    {
        if (!$request->user()->canManageAllEvents()) {
            abort(403, 'Akses ditolak.');
        }

        $payments = Payment::query()
            ->with([
                'registration:id,event_id,user_id,registration_number',
                'registration.event:id,name',
                'registration.user:id,name',
                'verifier:id,name',
            ])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'data' => collect($payments->items())
                ->map(fn (Payment $payment) => $this->serializePayment($payment))
                ->values(),
            'meta' => $this->meta($payments),
        ]);
    }

    /**
     * Verifikasi / tolak pembayaran peserta.
     */
    public function verifyPayment(Request $request, Payment $payment): JsonResponse
    {
        $user = $request->user();

        if (!$user->canVerifyPayments()) {
            abort(403, 'Akses ditolak.');
        }

        $payment->load('registration.event');

        if (!$payment->registration || !$payment->registration->event || !$user->canManageEvent($payment->registration->event)) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'verification_notes' => 'nullable|string',
        ], [
            'status.required' => 'Status verifikasi wajib dipilih.',
            'status.in' => 'Status verifikasi tidak valid.',
        ]);

        $payment->update([
            'status' => $validated['status'],
            'verified_by' => $user->id,
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
        ]);

        if ($validated['status'] === 'verified') {
            $payment->registration->update(['status' => 'verified']);
        } else {
            $payment->registration->update(['status' => 'rejected']);
        }

        return response()->json([
            'message' => 'Pembayaran berhasil diverifikasi!',
            'payment' => $this->serializePayment($payment->fresh([
                'registration.event:id,name',
                'registration.user:id,name',
                'verifier:id,name',
            ])),
        ]);
    }

    /**
     * Sajikan bukti pembayaran dari disk privat untuk pengelola event.
     */
    public function proof(Request $request, Payment $payment)
    {
        $user = $request->user();
        $payment->load('registration.event');

        $canManage = $user->canVerifyPayments()
            && $payment->registration
            && $payment->registration->event
            && $user->canManageEvent($payment->registration->event);

        if (!$canManage) {
            abort(403, 'Akses ditolak.');
        }

        $disk = Storage::disk('local');

        if (!$payment->proof_file || !$disk->exists($payment->proof_file)) {
            abort(404);
        }

        return $disk->response($payment->proof_file);
    }

    /**
     * Rekapitulasi nilai event: nilai akhir per form, total, rata-rata, dan rank.
     */
    public function recap(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $registrations = $event->registrations()
            ->where('status', 'verified')
            ->with(['user:id,name', 'juryScores.jury:id,name', 'juryScores.juryFormTemplate', 'response'])
            ->get();

        $juryFormTemplates = $event->juryFormTemplates()->orderBy('name')->get();
        $totalFormTemplates = $juryFormTemplates->count();

        $recapData = $registrations->map(function (Registration $registration) use ($juryFormTemplates, $totalFormTemplates) {
            $scores = $registration->juryScores;

            if ($scores->isEmpty()) {
                return null;
            }

            $scoresByForm = $scores->groupBy('jury_form_template_id');
            $legacyScores = $scores->filter(fn (JuryScore $score) => empty($score->jury_form_template_id));
            $legacyScore = $legacyScores->isNotEmpty() ? (float) ($legacyScores->avg('score') ?? 0) : 0;

            $formFinalScores = [];
            $formDetails = [];

            foreach ($scoresByForm as $formTemplateId => $formScores) {
                $formTemplate = $juryFormTemplates->firstWhere('id', $formTemplateId);

                if (!$formTemplate) {
                    continue;
                }

                $criteriaFields = $formTemplate->criteria_fields ?? [];
                $formFinalScore = 0;

                if (!empty($criteriaFields)) {
                    foreach ($criteriaFields as $field) {
                        if (($field['type'] ?? null) !== 'score') {
                            continue;
                        }

                        $criteriaLabel = $field['label'];
                        $criteriaScores = [];

                        foreach ($formScores as $score) {
                            $responses = $score->criteria_responses;

                            if (is_array($responses) && isset($responses[$criteriaLabel])) {
                                $response = $responses[$criteriaLabel];

                                if (is_array($response) && isset($response['score'])) {
                                    $criteriaScores[] = $response['score'];
                                }
                            }
                        }

                        if (!empty($criteriaScores)) {
                            $formFinalScore += array_sum($criteriaScores) / count($criteriaScores);
                        }
                    }
                } else {
                    $formFinalScore = (float) ($formScores->avg('score') ?? 0);
                }

                $formFinalScores[] = $formFinalScore;
                $formDetails[] = [
                    'id' => $formTemplate->id,
                    'name' => $formTemplate->name ?? 'Form Juri',
                    'score' => round($formFinalScore, 2),
                ];
            }

            if ($legacyScores->isNotEmpty()) {
                $formFinalScores[] = $legacyScore;
            }

            $averageScore = !empty($formFinalScores) && $totalFormTemplates > 0
                ? array_sum($formFinalScores) / $totalFormTemplates
                : 0;

            return [
                'registration_number' => $registration->registration_number,
                'participant_name' => $registration->user?->name,
                'address' => $this->addressFromResponse($registration),
                'form_details' => $formDetails,
                'total_score' => round(array_sum($formFinalScores), 2),
                'average_score' => round($averageScore, 8),
                'legacy_score' => round($legacyScore, 2),
                'jury_count' => $scores->count(),
                'form_count' => count($formFinalScores),
            ];
        })->filter()->values()->sortByDesc('average_score')->values();

        $rows = [];
        $rank = 1;
        $previousScore = null;

        foreach ($recapData as $index => $data) {
            if ($previousScore !== null && abs($data['average_score'] - $previousScore) > 0.00000001) {
                $rank = $index + 1;
            }

            $data['rank'] = $rank;
            $previousScore = $data['average_score'];
            $rows[] = $data;
        }

        $juryIds = JuryScore::query()
            ->whereHas('registration', fn ($query) => $query->where('event_id', $event->id))
            ->distinct()
            ->pluck('jury_id');

        return response()->json([
            'event' => ['id' => $event->id, 'name' => $event->name],
            'jury_form_templates' => $juryFormTemplates->map(fn (JuryFormTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name ?? 'Form Juri',
            ])->values(),
            'total_form_templates' => $totalFormTemplates,
            'total_juries' => $juryIds->count(),
            'has_legacy_scores' => collect($rows)->contains(fn (array $row) => ($row['legacy_score'] ?? 0) > 0),
            'rows' => $rows,
        ]);
    }

    /**
     * Assignment form juri untuk event: daftar juri yang bisa dipilih, form, dan assignment.
     */
    public function juryAssignments(Request $request, Event $event): JsonResponse
    {
        $user = $this->guardEvent($request, $event);

        $juryQuery = User::query()
            ->whereHas('role', fn ($query) => $query->where('name', Role::JURY));

        $juries = $juryQuery->orderBy('name')->get(['id', 'name', 'email', 'is_active']);

        $formTemplates = $event->juryFormTemplates()->orderBy('name')->get();

        $assignments = EventJuryFormAssignment::query()
            ->where('event_id', $event->id)
            ->with(['jury:id,name,email,is_active', 'juryFormTemplate:id,name'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'event' => ['id' => $event->id, 'name' => $event->name],
            'juries' => $juries->map(fn (User $jury) => [
                'id' => $jury->id,
                'name' => $jury->name,
                'email' => $jury->email,
                'is_active' => $jury->isActive(),
            ])->values(),
            'form_templates' => $formTemplates->map(function (JuryFormTemplate $template) {
                $fields = $template->criteria_fields ?? [];

                return [
                    'id' => $template->id,
                    'name' => $template->name ?? 'Form Juri',
                    'criteria_count' => count(array_filter($fields, fn ($field) => ($field['type'] ?? null) === 'score')),
                    'criteria_fields' => $fields,
                ];
            })->values(),
            'form_template_presets' => collect(JuryFormTemplate::getTemplates())
                ->map(fn (array $template, string $key) => ['key' => $key, 'name' => $template['name']])
                ->values(),
            'assignments' => $assignments->map(fn (EventJuryFormAssignment $assignment) => [
                'id' => $assignment->id,
                'jury' => [
                    'id' => $assignment->jury?->id,
                    'name' => $assignment->jury?->name,
                    'email' => $assignment->jury?->email,
                    'is_active' => $assignment->jury?->isActive() ?? false,
                ],
                'jury_role' => $assignment->jury_role,
                'status' => $assignment->status,
                'status_label' => $assignment->statusLabel(),
                'form_template_id' => $assignment->jury_form_template_id,
                'form_template_name' => $assignment->juryFormTemplate?->name,
                'created_at' => $assignment->created_at?->toIso8601String(),
                'responded_at' => $assignment->responded_at?->toIso8601String(),
                'can_reset_password' => $user->canManageAllEvents() || $assignment->jury?->created_by === $user->id,
            ])->values(),
        ]);
    }

    /**
     * Assign satu atau beberapa juri ke satu form penilaian event.
     */
    public function assignJury(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $validated = $request->validate([
            'jury_ids' => 'required|array',
            'jury_ids.*' => 'required|exists:users,id',
            'jury_form_template_id' => 'required|exists:jury_form_templates,id',
            'jury_role' => 'nullable|string|max:100',
        ], [
            'jury_ids.required' => 'Pilih minimal 1 juri untuk di-assign.',
            'jury_ids.*.exists' => 'Juri yang dipilih tidak ditemukan.',
            'jury_form_template_id.required' => 'Form penilaian wajib dipilih.',
            'jury_form_template_id.exists' => 'Form penilaian tidak ditemukan.',
            'jury_role.max' => 'Posisi juri maksimal 100 karakter.',
        ]);

        $juryRole = isset($validated['jury_role']) ? trim($validated['jury_role']) : null;
        $juryRole = $juryRole === '' ? null : $juryRole;

        $formTemplate = JuryFormTemplate::findOrFail($validated['jury_form_template_id']);

        if ($formTemplate->event_id !== $event->id) {
            throw ValidationException::withMessages([
                'jury_form_template_id' => 'Form template tidak valid untuk event ini.',
            ]);
        }

        $assignedCount = 0;
        $errors = [];

        foreach ($validated['jury_ids'] as $juryId) {
            $jury = User::findOrFail($juryId);

            if (!$jury->isJury()) {
                $errors[] = "User {$jury->name} bukan juri.";
                continue;
            }

            $this->assignJuryToForm($event, $jury, $formTemplate, $juryRole);
            $assignedCount++;
        }

        if ($assignedCount === 0) {
            throw ValidationException::withMessages([
                'jury_ids' => 'Tidak ada juri yang berhasil di-assign. ' . implode(' ', $errors),
            ]);
        }

        $message = $assignedCount . ' juri di-assign ke form "' . ($formTemplate->name ?? 'Form Juri') . '". Undangan sudah dikirim dan menunggu persetujuan juri.';

        if (!empty($errors)) {
            $message .= ' ' . implode(' ', $errors);
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Hapus assignment juri dari event.
     */
    public function destroyAssignment(Request $request, Event $event, EventJuryFormAssignment $assignment): JsonResponse
    {
        $this->guardEvent($request, $event);

        if ($assignment->event_id !== $event->id) {
            abort(403, 'Akses ditolak.');
        }

        $assignment->delete();

        return response()->json(['message' => 'Assignment berhasil dihapus!']);
    }

    /**
     * Buat atau segarkan assignment, lalu kirim undangan ke juri.
     * Assignment yang pernah ditolak dibuka kembali agar juri bisa menjawab ulang.
     */
    public function registrationForm(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);
        $event->load('eventForm');

        return response()->json([
            'fields' => $event->eventForm?->form_fields ?? [],
            'presets' => collect(EventForm::getTemplates())
                ->map(fn (array $template, string $key) => [
                    'key' => $key,
                    'name' => $template['name'],
                    'fields' => $template['fields'],
                ])
                ->values(),
        ]);
    }

    public function saveRegistrationForm(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $data = $request->validate([
            'fields' => ['present', 'array'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.type' => ['required', 'string', 'in:text,email,number,url,textarea,select,date'],
            'fields.*.required' => ['boolean'],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.options.*' => ['string', 'max:255'],
        ], [
            'fields.*.label.required' => 'Label field wajib diisi.',
            'fields.*.type.in' => 'Tipe field tidak dikenali.',
        ]);

        $fields = array_values(array_map(fn (array $field) => [
            'label' => trim($field['label']),
            'type' => $field['type'],
            'required' => (bool) ($field['required'] ?? false),
            'options' => array_values($field['options'] ?? []),
        ], $data['fields']));

        EventForm::updateOrCreate(['event_id' => $event->id], ['form_fields' => $fields]);

        return response()->json(['message' => 'Form pendaftaran berhasil disimpan.']);
    }

    public function juryForms(Request $request, Event $event): JsonResponse
    {
        $user = $this->guardEvent($request, $event);

        $forms = $event->juryFormTemplates()
            ->orderBy('name')
            ->get()
            ->map(fn (JuryFormTemplate $form) => [
                'id' => $form->id,
                'name' => $form->name,
                'criteria_fields' => $form->criteria_fields ?? [],
                'use_default_score' => (bool) $form->use_default_score,
            ])
            ->values();

        $customTemplates = \App\Models\CustomJuryFormTemplate::query()
            ->when(!$user->canManageAllEvents(), fn ($query) => $query->where('created_by', $user->id))
            ->orderBy('name')
            ->get()
            ->map(fn (\App\Models\CustomJuryFormTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'criteria_fields' => $template->criteria_fields ?? [],
                'use_default_score' => (bool) $template->use_default_score,
            ])
            ->values();

        $builtInTemplates = collect(JuryFormTemplate::getTemplates())
            ->map(fn (array $template, string $key) => [
                'key' => $key,
                'name' => $template['name'],
                'description' => 'Template bawaan JURI.ON',
                'criteria_fields' => $template['fields'] ?? [],
                'use_default_score' => (bool) ($template['use_default_score'] ?? true),
            ])
            ->values();

        return response()->json([
            'forms' => $forms,
            'custom_templates' => $customTemplates,
            'built_in_templates' => $builtInTemplates,
        ]);
    }

    public function saveJuryFormAsTemplate(Request $request, Event $event): JsonResponse
    {
        $user = $this->guardEvent($request, $event);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'use_default_score' => ['boolean'],
            'criteria_fields' => ['required', 'array', 'min:1'],
            'criteria_fields.*.label' => ['required', 'string', 'max:255'],
            'criteria_fields.*.type' => ['required', 'string', 'in:score,text,textarea,select'],
            'criteria_fields.*.required' => ['boolean'],
            'criteria_fields.*.max' => ['nullable', 'integer', 'min:1', 'max:100'],
            'criteria_fields.*.description' => ['nullable', 'string', 'max:500'],
            'criteria_fields.*.options' => ['nullable', 'array'],
        ]);

        $criteria = array_values(array_map(fn (array $field) => [
            'label' => trim($field['label']),
            'type' => $field['type'],
            'required' => (bool) ($field['required'] ?? false),
            'max' => (int) ($field['max'] ?? 100),
            'description' => trim((string) ($field['description'] ?? '')),
            'options' => array_values($field['options'] ?? []),
        ], $data['criteria_fields']));

        $template = \App\Models\CustomJuryFormTemplate::create([
            'name' => trim($data['name']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'criteria_fields' => $criteria,
            'use_default_score' => (bool) ($data['use_default_score'] ?? true),
            'created_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Form penilaian disimpan sebagai template custom.',
            'template_id' => $template->id,
        ], 201);
    }

    public function saveJuryForm(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'form_template_id' => ['nullable', 'integer'],
            'use_default_score' => ['boolean'],
            'criteria_fields' => ['required', 'array', 'min:1'],
            'criteria_fields.*.label' => ['required', 'string', 'max:255'],
            'criteria_fields.*.type' => ['required', 'string', 'in:score,text,textarea,select'],
            'criteria_fields.*.required' => ['boolean'],
            'criteria_fields.*.max' => ['nullable', 'integer', 'min:1', 'max:100'],
            'criteria_fields.*.description' => ['nullable', 'string', 'max:500'],
            'criteria_fields.*.options' => ['nullable', 'array'],
        ], [
            'name.required' => 'Nama form wajib diisi.',
            'criteria_fields.required' => 'Minimal satu kriteria penilaian.',
            'criteria_fields.*.label.required' => 'Label kriteria wajib diisi.',
            'criteria_fields.*.type.in' => 'Tipe kriteria tidak dikenali.',
        ]);

        $criteria = array_values(array_map(fn (array $field) => [
            'label' => trim($field['label']),
            'type' => $field['type'],
            'required' => (bool) ($field['required'] ?? false),
            'max' => (int) ($field['max'] ?? 100),
            'description' => trim((string) ($field['description'] ?? '')),
            'options' => array_values($field['options'] ?? []),
        ], $data['criteria_fields']));

        $templateId = $data['form_template_id'] ?? null;

        if ($templateId) {
            $form = $event->juryFormTemplates()->find($templateId);

            if (!$form) {
                return response()->json(['message' => 'Form penilaian tidak ditemukan untuk event ini.'], 422);
            }

            $form->update([
                'name' => $data['name'],
                'criteria_fields' => $criteria,
                'use_default_score' => (bool) ($data['use_default_score'] ?? true),
            ]);
        } else {
            $form = JuryFormTemplate::create([
                'event_id' => $event->id,
                'name' => $data['name'],
                'criteria_fields' => $criteria,
                'use_default_score' => (bool) ($data['use_default_score'] ?? true),
            ]);
        }

        return response()->json([
            'message' => 'Form penilaian berhasil disimpan.',
            'form_id' => $form->id,
        ]);
    }

    /**
     * Buat form penilaian baru dari template lalu assign beberapa juri sekaligus.
     */
    public function quickCreateJuryForm(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $data = $request->validate([
            'form_name' => ['required', 'string', 'max:255'],
            'template_key' => ['required', 'string'],
            'jury_ids' => ['required', 'array', 'min:1'],
            'jury_ids.*' => ['integer', 'exists:users,id'],
            'jury_role' => ['nullable', 'string', 'max:100'],
        ], [
            'form_name.required' => 'Nama form wajib diisi.',
            'template_key.required' => 'Template wajib dipilih.',
            'jury_ids.required' => 'Pilih minimal 1 juri.',
        ]);

        $templates = JuryFormTemplate::getTemplates();

        if (!isset($templates[$data['template_key']])) {
            return response()->json(['message' => 'Template tidak valid.'], 422);
        }

        $template = $templates[$data['template_key']];
        $juryRole = trim((string) ($data['jury_role'] ?? ''));
        $juryRole = $juryRole === '' ? null : $juryRole;

        $form = JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => $data['form_name'],
            'criteria_fields' => $template['fields'],
            'use_default_score' => $template['use_default_score'] ?? true,
        ]);

        $assigned = 0;
        foreach ($data['jury_ids'] as $juryId) {
            $jury = User::find($juryId);

            if (!$jury || !$jury->isJury()) {
                continue;
            }

            $this->assignJuryToForm($event, $jury, $form, $juryRole);
            $assigned++;
        }

        return response()->json([
            'message' => "Form \"{$form->name}\" berhasil dibuat dan {$assigned} juri di-assign. Undangan sudah dikirim dan menunggu persetujuan juri.",
            'form_id' => $form->id,
            'assigned' => $assigned,
        ], 201);
    }

    public function createJuryAccount(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'jury_form_template_id' => ['required', 'integer'],
            'jury_role' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
        ], [
            'name' => 'nama',
            'email' => 'email',
            'jury_form_template_id' => 'form penilaian',
        ]);

        $juryRole = trim((string) ($data['jury_role'] ?? ''));
        $juryRole = $juryRole === '' ? null : $juryRole;

        $formTemplate = $event->juryFormTemplates()->find($data['jury_form_template_id']);

        if (!$formTemplate) {
            return response()->json(['message' => 'Form penilaian tidak valid untuk event ini.'], 422);
        }

        $email = Str::lower(trim($data['email']));
        $existing = User::where('email', $email)->first();

        if ($existing) {
            if (!$existing->isJury()) {
                return response()->json([
                    'message' => "Email {$email} sudah terdaftar sebagai {$existing->roleLabel()}. Gunakan email lain atau ubah role melalui Admin Owner.",
                ], 422);
            }

            if (!$existing->isActive()) {
                return response()->json([
                    'message' => "Akun juri {$existing->name} sedang nonaktif. Aktifkan akun terlebih dahulu melalui Admin Owner.",
                ], 422);
            }

            return response()->json([
                'message' => "Email {$email} sudah terdaftar sebagai juri. Gunakan tab \"Tambah Akun Juri yang Sudah Ada\" untuk meng-assign akun tersebut.",
            ], 422);
        }

        $role = Role::where('name', Role::JURY)->firstOrFail();
        $customPassword = trim((string) ($data['password'] ?? ''));
        $code = $customPassword !== '' ? $customPassword : Str::lower(Str::random(8));

        $jury = User::create([
            'name' => trim($data['name']),
            'email' => $email,
            'password' => $code,
            'role_id' => $role->id,
            'is_active' => true,
            'event_scope' => 'all',
            'created_by' => $request->user()->id,
        ]);

        $this->assignJuryToForm($event, $jury, $formTemplate, $juryRole);

        return response()->json([
            'message' => "Akun juri {$jury->name} berhasil dibuat dan di-assign ke form \"{$formTemplate->name}\". Undangan sudah dikirim dan menunggu persetujuan juri.",
            'credentials' => ['name' => $jury->name, 'email' => $jury->email, 'code' => $code],
        ], 201);
    }

    public function resetJuryPassword(Request $request, Event $event, EventJuryFormAssignment $assignment): JsonResponse
    {
        $admin = $this->guardEvent($request, $event);

        if ($assignment->event_id !== $event->id) {
            return response()->json(['message' => 'Assignment bukan milik event ini.'], 403);
        }

        $jury = $assignment->jury;

        if (!$jury) {
            return response()->json(['message' => 'Akun juri untuk assignment ini tidak ditemukan.'], 404);
        }

        if (!$admin->canManageAllEvents() && $jury->created_by !== $admin->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $data = $request->validate([
            'password' => ['nullable', 'string', 'min:8'],
        ], [
            'password.min' => 'Password baru minimal :min karakter.',
        ]);

        $custom = trim((string) ($data['password'] ?? ''));
        $newPassword = $custom !== '' ? $custom : Str::lower(Str::random(8));

        $jury->update(['password' => $newPassword]);

        return response()->json([
            'message' => 'Password juri berhasil diperbarui.',
            'credentials' => ['name' => $jury->name, 'email' => $jury->email, 'code' => $newPassword, 'custom' => $custom !== ''],
        ]);
    }

    private function assignJuryToForm(Event $event, User $jury, JuryFormTemplate $formTemplate, ?string $juryRole): EventJuryFormAssignment
    {
        $assignment = EventJuryFormAssignment::firstOrNew([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
        ]);

        $isNew = !$assignment->exists;
        $wasDeclined = $assignment->exists && $assignment->isDeclined();

        $assignment->jury_form_template_id = $formTemplate->id;
        $assignment->jury_role = $juryRole;

        if ($isNew || $wasDeclined) {
            $assignment->status = EventJuryFormAssignment::STATUS_PENDING;
            $assignment->responded_at = null;
        }

        $assignment->save();

        if ($isNew || $wasDeclined) {
            $jury->notify(new JuryAssignmentInvited($assignment->fresh(['event.organizer', 'juryFormTemplate'])));
        }

        return $assignment;
    }

    private function guardAdmin(Request $request): User
    {
        $user = $request->user();

        if (!$user->canManageEvents()) {
            abort(403, 'Akses ditolak.');
        }

        return $user;
    }

    private function guardEvent(Request $request, Event $event): User
    {
        $user = $this->guardAdmin($request);

        if (!$user->canManageEvent($event)) {
            abort(403, 'Akses ditolak.');
        }

        return $user;
    }

    /**
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, mixed>  $paginator
     * @return array{current_page: int, last_page: int, per_page: int, total: int}
     */
    private function meta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    public function duplicate(Request $request, Event $event): JsonResponse
    {
        $user = $this->guardEvent($request, $event);

        $copy = app(EventQuotaService::class)->create($user, [
            'category_id' => $event->category_id,
            'name' => $event->name . ' (Salinan)',
            'description' => $event->description,
            'format' => $event->format ?? 'offline',
            'participation_mode' => $event->participation_mode ?? 'individual',
            'team_min_members' => (int) ($event->team_min_members ?? 2),
            'team_max_members' => (int) ($event->team_max_members ?? 5),
            'timezone' => $event->timezone ?? 'Asia/Jakarta',
            'contact_name' => $event->contact_name,
            'contact_email' => $event->contact_email,
            'contact_phone' => $event->contact_phone,
            'price' => $event->price,
            'quota' => $event->quota,
            'registered_count' => 0,
            'registration_start' => $event->registration_start,
            'registration_end' => $event->registration_end,
            'event_date' => $event->event_date,
            'event_start' => $event->event_start,
            'event_end' => $event->event_end,
            'location' => $event->location,
            'status' => 'draft',
            'scoring_enabled' => null,
        ]);

        if ($event->eventForm) {
            EventForm::create([
                'event_id' => $copy->id,
                'description' => $event->eventForm->description,
                'form_fields' => $event->eventForm->form_fields,
            ]);
        }

        foreach ($event->juryFormTemplates as $template) {
            JuryFormTemplate::create([
                'event_id' => $copy->id,
                'name' => $template->name,
                'description' => $template->description,
                'criteria_fields' => $template->criteria_fields,
                'use_default_score' => $template->use_default_score,
            ]);
        }

        $copy->load(['organizer:id,name', 'category:id,name']);

        return response()->json([
            'message' => 'Event berhasil diduplikasi sebagai draft.',
            'event' => $this->serializeEvent($copy),
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeEvent(Event $event): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'slug' => $event->slug,
            'public_url' => '/events/' . $event->slug,
            'description' => $event->description,
            'format' => $event->format ?? 'offline',
            'participation_mode' => $event->participation_mode ?? 'individual',
            'team_min_members' => $event->team_min_members ?? 2,
            'team_max_members' => $event->team_max_members ?? 5,
            'timezone' => $event->timezone ?? 'Asia/Jakarta',
            'contact_name' => $event->contact_name,
            'contact_email' => $event->contact_email,
            'contact_phone' => $event->contact_phone,
            'archived_at' => $event->archived_at?->toIso8601String(),
            'status' => $event->archived_at ? 'archived' : $event->status,
            'status_label' => $event->archived_at ? 'Diarsipkan' : $this->eventStatusLabel($event->status),
            'price' => (float) $event->price,
            'quota' => (int) $event->quota,
            'registered_count' => (int) $event->registered_count,
            'available_slots' => $event->getAvailableSlots(),
            'is_full' => $event->getAvailableSlots() === 0,
            'location' => $event->location,
            'banner' => $event->banner,
            'banner_url' => $event->banner ? asset('storage/' . ltrim($event->banner, '/')) : null,
            'juknis_file' => $event->juknis_file,
            'juknis_name' => $event->juknis_name,
            'juknis_url' => $event->juknis_file ? Storage::url($event->juknis_file) : null,
            'registration_start' => $event->registration_start?->format('Y-m-d'),
            'registration_end' => $event->registration_end?->format('Y-m-d'),
            'event_date' => $event->event_date?->format('Y-m-d'),
            'event_start' => $event->event_start?->format('Y-m-d'),
            'event_end' => $event->event_end?->format('Y-m-d'),
            'scoring_mode' => $event->scoringMode(),
            'scoring_open' => $event->isScoringOpen(),
            'scoring_status_label' => $event->scoringStatusLabel(),
            'scoring_opens_at' => $event->scoringOpensAt()?->format('Y-m-d'),
            'organizer' => $event->organizer ? [
                'id' => $event->organizer->id,
                'name' => $event->organizer->name,
            ] : null,
            'category' => $event->category ? [
                'id' => $event->category->id,
                'name' => $event->category->name,
            ] : null,
            'registrations_count' => (int) ($event->registrations_count ?? $event->registrations()->count()),
            'registration_open' => $event->isRegistrationOpen(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'amount' => (float) $payment->amount,
            'status' => $payment->status,
            'status_label' => $this->paymentStatusLabel($payment->status),
            'verification_notes' => $payment->verification_notes,
            'verified_at' => $payment->verified_at?->toIso8601String(),
            'created_at' => $payment->created_at?->toIso8601String(),
            'has_proof' => (bool) $payment->proof_file,
            'verifier' => $payment->verifier ? [
                'id' => $payment->verifier->id,
                'name' => $payment->verifier->name,
            ] : null,
            'registration' => $payment->registration ? [
                'id' => $payment->registration->id,
                'registration_number' => $payment->registration->registration_number,
                'user' => $payment->registration->user ? [
                    'id' => $payment->registration->user->id,
                    'name' => $payment->registration->user->name,
                ] : null,
                'event' => $payment->registration->event ? [
                    'id' => $payment->registration->event->id,
                    'name' => $payment->registration->event->name,
                ] : null,
            ] : null,
        ];
    }

    private function eventStatusLabel(?string $status): string
    {
        return [
            'draft' => 'Draft',
            'active' => 'Aktif',
            'closed' => 'Ditutup',
            'completed' => 'Selesai',
            'archived' => 'Diarsipkan',
        ][$status] ?? ucfirst((string) $status);
    }

    private function registrationStatusLabel(?string $status): string
    {
        return [
            'pending' => 'Pending',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ][$status] ?? ucfirst((string) $status);
    }

    private function paymentStatusLabel(?string $status): string
    {
        return [
            'pending' => 'Pending',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
        ][$status] ?? ucfirst((string) $status);
    }

    private function addressFromResponse(Registration $registration): string
    {
        $responses = $registration->response?->responses;

        if (is_string($responses)) {
            $responses = json_decode($responses, true);
        }

        if (!is_array($responses)) {
            return '-';
        }

        foreach ($responses as $key => $value) {
            if (is_string($value)
                && (stripos((string) $key, 'alamat') !== false || stripos((string) $key, 'address') !== false)) {
                return $value;
            }
        }

        return '-';
    }

    /**
     * Pesan validasi event (Indonesia).
     *
     * @return array<string, string>
     */
    private function eventValidationMessages(?Event $event = null): array
    {
        $messages = [
            'category_id.exists' => 'Kategori tidak valid.',
            'name.required' => 'Nama event wajib diisi.',
            'name.max' => 'Nama event maksimal 255 karakter.',
            'description.required' => 'Deskripsi wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh kurang dari 0.',
            'quota.required' => 'Kuota peserta wajib diisi.',
            'quota.integer' => 'Kuota peserta harus berupa angka bulat.',
            'quota.min' => 'Kuota peserta minimal 1.',
            'registration_start.required' => 'Tanggal mulai pendaftaran wajib diisi.',
            'registration_start.date' => 'Tanggal mulai pendaftaran harus berupa tanggal yang valid.',
            'registration_end.required' => 'Tanggal akhir pendaftaran wajib diisi.',
            'registration_end.date' => 'Tanggal akhir pendaftaran harus berupa tanggal yang valid.',
            'registration_end.after' => 'Tanggal akhir pendaftaran harus setelah tanggal mulai pendaftaran.',
            'event_start.required' => 'Tanggal mulai event wajib diisi.',
            'event_start.date' => 'Tanggal mulai event harus berupa tanggal yang valid.',
            'event_end.required' => 'Tanggal akhir event wajib diisi.',
            'event_end.date' => 'Tanggal akhir event harus berupa tanggal yang valid.',
            'event_end.after_or_equal' => 'Tanggal akhir event tidak boleh sebelum tanggal mulai event.',
            'event_date.required' => 'Tanggal hari H wajib diisi.',
            'event_date.date' => 'Tanggal hari H harus berupa tanggal yang valid.',
            'event_date.after_or_equal' => 'Tanggal hari H tidak boleh sebelum tanggal mulai event.',
            'event_date.before_or_equal' => 'Tanggal hari H tidak boleh setelah tanggal akhir event.',
            'location.max' => 'Lokasi maksimal 255 karakter.',
            'banner.image' => 'Banner harus berupa gambar.',
            'banner.mimes' => 'Banner harus berformat JPG atau PNG.',
            'banner.max' => 'Ukuran banner maksimal 2 MB.',
            'juknis.file' => 'Juknis harus berupa berkas.',
            'juknis.mimes' => 'Juknis harus berformat PDF, DOC, DOCX, XLS, XLSX, PPT, atau PPTX.',
            'juknis.max' => 'Ukuran juknis maksimal 10 MB.',
            'status.required' => 'Status event wajib dipilih.',
            'status.in' => 'Status event tidak valid.',
        ];

        if ($event) {
            $messages['quota.min'] = "Kuota peserta minimal {$event->registered_count} (jumlah peserta terdaftar).";
        }

        return $messages;
    }

    public function requestScoreRevision(Request $request, JuryScore $juryScore): JsonResponse
    {
        $user = $this->guardAdmin($request);
        $event = $juryScore->registration->event;
        $this->guardEvent($request, $event);

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        if (!$juryScore->jury_id) {
            return response()->json(['message' => 'Skor ini tidak memiliki juri.'], 422);
        }

        DB::transaction(function () use ($juryScore, $event, $user, $validated, $request) {
            ScoreRevisionRequest::query()
                ->where('jury_score_id', $juryScore->id)
                ->where('status', ScoreRevisionRequest::STATUS_PENDING)
                ->update([
                    'status' => ScoreRevisionRequest::STATUS_CANCELLED,
                    'resolved_by' => $user->id,
                    'resolved_at' => now(),
                ]);

            $juryScore->update([
                'status' => 'submitted',
                'published_at' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);

            $revision = ScoreRevisionRequest::create([
                'jury_score_id' => $juryScore->id,
                'event_id' => $event->id,
                'requested_by' => $user->id,
                'jury_id' => $juryScore->jury_id,
                'reason' => $validated['reason'],
                'status' => ScoreRevisionRequest::STATUS_PENDING,
            ]);

            $juryScore->jury?->notify(new ScoreRevisionRequested($revision->load('score.registration.user', 'event')));

            app(AuditLogger::class)->log('score.revision_requested', $revision, [
                'jury_score_id' => $juryScore->id,
                'jury_id' => $juryScore->jury_id,
                'reason' => $validated['reason'],
            ], $request);
        });

        return response()->json([
            'message' => 'Permintaan revisi dikirim ke juri. Juri harus meninjau dan memperbarui penilaian.',
        ], 201);
    }

    public function scoreRevisions(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $revisions = ScoreRevisionRequest::query()
            ->with(['jury:id,name', 'score.registration.user:id,name', 'requester:id,name'])
            ->where('event_id', $event->id)
            ->latest()
            ->get()
            ->map(fn (ScoreRevisionRequest $revision) => [
                'id' => $revision->id,
                'status' => $revision->status,
                'reason' => $revision->reason,
                'created_at' => $revision->created_at?->toIso8601String(),
                'resolved_at' => $revision->resolved_at?->toIso8601String(),
                'jury' => $revision->jury?->name,
                'participant' => $revision->score?->registration?->user?->name,
                'requested_by' => $revision->requester?->name,
                'resolution_notes' => $revision->resolution_notes,
            ]);

        return response()->json(['data' => $revisions]);
    }

    public function evaluations(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $scores = JuryScore::query()
            ->with(['registration.user:id,name,email', 'jury:id,name', 'reviewer:id,name', 'juryFormTemplate:id,name'])
            ->whereHas('registration', fn ($query) => $query->where('event_id', $event->id))
            ->latest()
            ->get()
            ->map(function (JuryScore $score) {
                $pendingRevision = ScoreRevisionRequest::query()
                    ->where('jury_score_id', $score->id)
                    ->where('status', ScoreRevisionRequest::STATUS_PENDING)
                    ->latest()
                    ->first();

                return [
                    'id' => $score->id,
                    'score' => (float) $score->score,
                    'status' => $score->status,
                    'submitted_at' => $score->submitted_at?->toIso8601String(),
                    'published_at' => $score->published_at?->toIso8601String(),
                    'public_feedback' => $score->public_feedback,
                    'private_evaluation' => $score->private_evaluation,
                    'review_notes' => $score->review_notes,
                    'reviewer' => $score->reviewer?->name,
                    'jury' => $score->jury?->name,
                    'form' => $score->juryFormTemplate?->name,
                    'participant' => [
                        'id' => $score->registration?->id,
                        'name' => $score->registration?->user?->name,
                        'email' => $score->registration?->user?->email,
                    ],
                    'pending_revision' => $pendingRevision ? [
                        'id' => $pendingRevision->id,
                        'reason' => $pendingRevision->reason,
                    ] : null,
                ];
            });

        return response()->json([
            'event' => ['id' => $event->id, 'name' => $event->name, 'evaluations_published_at' => $event->evaluations_published_at?->toIso8601String()],
            'data' => $scores,
            'summary' => [
                'total' => $scores->count(),
                'submitted' => $scores->where('status', 'submitted')->count(),
                'reviewed' => $scores->where('status', 'reviewed')->count(),
                'published' => $scores->where('status', 'published')->count(),
                'needs_revision' => $scores->where('status', 'needs_revision')->count(),
            ],
        ]);
    }

    public function reviewEvaluation(Request $request, JuryScore $juryScore): JsonResponse
    {
        $user = $this->guardAdmin($request);
        $event = $juryScore->registration->event;
        $this->guardEvent($request, $event);

        $validated = $request->validate([
            'status' => 'required|in:reviewed,needs_revision',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($juryScore->status === 'published') {
            return response()->json(['message' => 'Evaluasi yang sudah terbit tidak dapat diubah status reviewnya. Minta revisi juri terlebih dahulu.'], 422);
        }

        if (ScoreRevisionRequest::query()
            ->where('jury_score_id', $juryScore->id)
            ->where('status', ScoreRevisionRequest::STATUS_PENDING)
            ->exists()) {
            return response()->json(['message' => 'Selesaikan permintaan revisi juri sebelum mereview evaluasi ini.'], 422);
        }

        $juryScore->update([
            'status' => $validated['status'],
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['notes'] ?? null,
        ]);

        app(AuditLogger::class)->log('evaluation.reviewed', $juryScore, [
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ], $request);

        return response()->json(['message' => 'Status review evaluasi diperbarui.']);
    }

    public function publishEvaluation(Request $request, JuryScore $juryScore): JsonResponse
    {
        $this->guardAdmin($request);
        $event = $juryScore->registration->event;
        $this->guardEvent($request, $event);

        if ($juryScore->status !== 'reviewed') {
            return response()->json(['message' => 'Hanya evaluasi berstatus reviewed yang bisa diterbitkan.'], 422);
        }

        if (ScoreRevisionRequest::query()
            ->where('jury_score_id', $juryScore->id)
            ->where('status', ScoreRevisionRequest::STATUS_PENDING)
            ->exists()) {
            return response()->json(['message' => 'Evaluasi masih memiliki permintaan revisi yang belum selesai.'], 422);
        }

        $juryScore->update(['status' => 'published', 'published_at' => now()]);
        $event->update(['evaluations_published_at' => $event->evaluations_published_at ?? now()]);

        app(AuditLogger::class)->log('evaluation.published', $juryScore, ['event_id' => $event->id], $request);

        return response()->json(['message' => 'Evaluasi berhasil diterbitkan ke peserta.']);
    }

    public function publishAllEvaluations(Request $request, Event $event): JsonResponse
    {
        $this->guardEvent($request, $event);

        $scores = JuryScore::query()
            ->whereHas('registration', fn ($query) => $query->where('event_id', $event->id))
            ->get();

        $blocking = $scores->filter(fn (JuryScore $score) => !in_array($score->status, ['reviewed', 'published'], true));

        if ($blocking->isNotEmpty()) {
            return response()->json([
                'message' => 'Masih ada ' . $blocking->count() . ' evaluasi yang belum direview. Review semua evaluasi sebelum menerbitkan.',
            ], 422);
        }

        $pendingRevisions = ScoreRevisionRequest::query()
            ->where('event_id', $event->id)
            ->where('status', ScoreRevisionRequest::STATUS_PENDING)
            ->count();

        if ($pendingRevisions > 0) {
            return response()->json([
                'message' => "Masih ada {$pendingRevisions} permintaan revisi yang belum diselesaikan juri.",
            ], 422);
        }

        JuryScore::query()
            ->whereIn('id', $scores->pluck('id'))
            ->where('status', 'reviewed')
            ->update(['status' => 'published', 'published_at' => now()]);

        $event->update(['evaluations_published_at' => now()]);

        app(AuditLogger::class)->log('evaluation.published_all', $event, ['count' => $scores->count()], $request);

        return response()->json(['message' => 'Semua evaluasi yang sudah direview berhasil diterbitkan.']);
    }

    public function exportParticipants(Request $request, Event $event)
    {
        $this->guardEvent($request, $event);

        $registrations = $event->registrations()
            ->with(['user:id,name,email', 'payment:id,registration_id,status', 'juryScores:id,registration_id,score'])
            ->orderBy('id')
            ->get();

        return response()->streamDownload(function () use ($registrations) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'No. Pendaftaran', 'Nama', 'Email', 'Status', 'Tim', 'Anggota Tim',
                'Pembayaran', 'Rata-rata Nilai', 'Jumlah Juri', 'Tanggal Daftar',
            ]);

            foreach ($registrations as $registration) {
                fputcsv($out, [
                    $registration->registration_number,
                    $registration->user?->name,
                    $registration->user?->email,
                    $registration->status,
                    $registration->team_name,
                    collect($registration->team_members ?? [])->pluck('name')->implode('; '),
                    $registration->payment?->status ?? 'belum upload',
                    $registration->juryScores->isEmpty() ? '' : number_format((float) $registration->juryScores->avg('score'), 2),
                    $registration->juryScores->count(),
                    $registration->registered_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($out);
        }, 'peserta-' . $event->slug . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}