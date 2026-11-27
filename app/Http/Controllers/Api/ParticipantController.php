<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationFile;
use App\Models\RegistrationResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ParticipantController extends Controller
{
    /**
     * Daftar pendaftaran milik pengguna yang sedang masuk.
     */
    public function index(Request $request): JsonResponse
    {
        $registrations = Registration::query()
            ->where('user_id', $request->user()->id)
            ->with(['event', 'payment'])
            ->withAvg('juryScores as average_score', 'score')
            ->latest()
            ->get()
            ->map(fn (Registration $registration) => $this->listPayload($registration))
            ->values();

        return response()->json(['registrations' => $registrations]);
    }

    /**
     * Detail pendaftaran; hanya pemilik pendaftaran yang boleh melihat.
     */
    public function show(Request $request, Registration $registration): JsonResponse
    {
        $this->ensureOwner($request, $registration);

        $registration->load(['event.organizer', 'user', 'payment.verifier', 'response', 'files', 'juryScores.jury']);

        return response()->json(['registration' => $this->detailPayload($registration)]);
    }

    /**
     * Data untuk formulir unggah bukti pembayaran (mirror PaymentController::create).
     */
    public function payment(Request $request, Registration $registration): JsonResponse
    {
        $this->ensureOwner($request, $registration);

        $registration->loadMissing('event');

        if ($registration->payment()->exists()) {
            return response()->json([
                'message' => 'Pembayaran sudah diupload sebelumnya.',
            ], 409);
        }

        $price = (float) $registration->event->price;

        return response()->json([
            'registration' => [
                'id' => $registration->id,
                'registration_number' => $registration->registration_number,
                'status' => $registration->status,
                'event' => [
                    'id' => $registration->event->id,
                    'name' => $registration->event->name,
                    'price' => $price,
                    'price_label' => $this->rupiah($price),
                ],
                'amount' => $price,
                'amount_label' => $this->rupiah($price),
            ],
        ]);
    }

    /**
     * Simpan bukti pembayaran (mirror PaymentController::store).
     */
    public function storePayment(Request $request, Registration $registration): JsonResponse
    {
        $this->ensureOwner($request, $registration);

        $registration->loadMissing('event');

        if ($registration->payment()->exists()) {
            return response()->json([
                'message' => 'Pembayaran sudah diupload sebelumnya.',
            ], 409);
        }

        $request->validate([
            'proof_file' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
        ], [
            'proof_file.required' => 'Bukti transfer wajib diunggah.',
            'proof_file.file' => 'Bukti transfer tidak valid.',
            'proof_file.mimes' => 'Format file harus JPG, PNG, atau PDF.',
            'proof_file.max' => 'Ukuran file maksimal 2MB.',
            'proof_file.uploaded' => 'Gagal mengunggah bukti transfer. Ukuran file kemungkinan melebihi batas server.',
        ]);

        $proofPath = $request->file('proof_file')->store('payment-proofs', 'local');

        $payment = DB::transaction(function () use ($registration, $proofPath) {
            $locked = Registration::whereKey($registration->id)->lockForUpdate()->firstOrFail();

            if ($locked->payment()->exists()) {
                return null;
            }

            return Payment::create([
                'registration_id' => $registration->id,
                'amount' => $registration->event->price,
                'proof_file' => $proofPath,
                'status' => 'pending',
            ]);
        });

        if (!$payment instanceof Payment) {
            Storage::disk('local')->delete($proofPath);

            return response()->json([
                'message' => 'Pembayaran sudah diupload sebelumnya.',
            ], 409);
        }

        return response()->json([
            'message' => 'Bukti pembayaran berhasil diupload! Mohon tunggu verifikasi.',
            'payment' => $this->paymentPayload($payment->loadMissing('registration')),
        ], 201);
    }

    /**
     * Sajikan bukti transfer dari disk privat (mirror PaymentController::proof).
     */
    public function proof(Request $request, Registration $registration): Response
    {
        $registration->loadMissing('event', 'payment');

        $user = $request->user();
        $isOwner = $registration->user_id === $user->id;
        $canManage = $user->canVerifyPayments() && $user->canManageEvent($registration->event);

        abort_unless($isOwner || $canManage, 403, 'Unauthorized action.');

        $payment = $registration->payment;
        $disk = Storage::disk('local');

        abort_if(!$payment || !$payment->proof_file || !$disk->exists($payment->proof_file), 404);

        return $disk->response($payment->proof_file, basename($payment->proof_file));
    }

    /**
     * Data form pendaftaran event untuk peserta yang sedang masuk.
     */
    public function registrationForm(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        if (!$user->isParticipant()) {
            abort(403, 'Hanya peserta yang dapat mendaftar event.');
        }

        $event->load('eventForm');
        $existing = Registration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        $emailDomain = substr(strrchr($user->email, '@'), 1);
        $allowedDomains = $event->require_official_email && $event->allowed_email_domains
            ? array_map('trim', explode(',', $event->allowed_email_domains))
            : [];

        return response()->json([
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'price' => (float) $event->price,
                'quota' => $event->quota,
                'registered_count' => $event->registered_count,
                'event_date' => $event->event_date?->format('Y-m-d'),
                'registration_start' => $event->registration_start?->format('Y-m-d'),
                'registration_end' => $event->registration_end?->format('Y-m-d'),
                'registration_open' => $event->isRegistrationOpen(),
                'participation_mode' => $event->participation_mode ?? 'individual',
                'team_min_members' => (int) ($event->team_min_members ?? 2),
                'team_max_members' => (int) ($event->team_max_members ?? 5),
                'require_official_email' => (bool) $event->require_official_email,
                'allowed_email_domains' => $allowedDomains,
                'email_domain_allowed' => !$event->require_official_email
                    || !$event->allowed_email_domains
                    || in_array($emailDomain, $allowedDomains, true),
            ],
            'fields' => $event->eventForm?->form_fields ?? [],
            'registered' => (bool) $existing,
            'registration_id' => $existing?->id,
        ]);
    }

    /**
     * Daftar event sebagai peserta; mengikuti aturan RegistrationController@store.
     */
    public function register(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        if (!$user->isParticipant()) {
            abort(403, 'Hanya peserta yang dapat mendaftar event.');
        }

        if (!$event->isRegistrationOpen()) {
            return response()->json(['message' => 'Pendaftaran event ini sudah ditutup atau kuota penuh.'], 422);
        }

        if (Registration::where('event_id', $event->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Anda sudah terdaftar di event ini.'], 422);
        }

        if ($event->require_official_email && $event->allowed_email_domains) {
            $emailDomain = substr(strrchr($user->email, '@'), 1);
            $allowedDomains = array_map('trim', explode(',', $event->allowed_email_domains));

            if (!in_array($emailDomain, $allowedDomains, true)) {
                return response()->json([
                    'message' => 'Pendaftaran event ini hanya untuk email resmi dengan domain: '
                        . implode(', ', $allowedDomains) . '. Email Anda (' . $user->email . ') tidak diizinkan.',
                ], 422);
            }
        }

        $event->load('eventForm');

        $teamName = null;
        $teamMembers = null;

        if ($event->participation_mode === 'team') {
            $team = $request->validate([
                'team_name' => 'required|string|max:255',
                'team_members' => 'required|array|min:' . max(1, (int) $event->team_min_members) . '|max:' . max(1, (int) $event->team_max_members),
                'team_members.*.name' => 'required|string|max:255',
                'team_members.*.email' => 'nullable|email|max:255',
            ], [
                'team_name.required' => 'Nama tim wajib diisi.',
                'team_members.required' => 'Anggota tim wajib diisi.',
                'team_members.min' => 'Jumlah anggota tim minimal :min orang.',
                'team_members.max' => 'Jumlah anggota tim maksimal :max orang.',
                'team_members.*.name.required' => 'Nama setiap anggota tim wajib diisi.',
            ]);

            $teamName = $team['team_name'];
            $teamMembers = $team['team_members'];
        }

        $rules = [];
        $attributes = [];

        foreach ($event->eventForm?->form_fields ?? [] as $index => $field) {
            $fieldName = "field_{$index}";
            $fieldRules = [];
            $attributes[$fieldName] = $field['label'] ?? ('Kolom ' . ($index + 1));

            if (!empty($field['required'])) {
                $fieldRules[] = 'required';
            }

            if (($field['type'] ?? '') === 'email') {
                $fieldRules[] = 'email';
            } elseif (($field['type'] ?? '') === 'number') {
                $fieldRules[] = 'numeric';
            } elseif (($field['type'] ?? '') === 'url') {
                $fieldRules[] = 'url';
            } elseif (($field['type'] ?? '') === 'file') {
                $fieldRules[] = 'file';
                $fieldRules[] = 'mimes:pdf,jpg,jpeg,png,doc,docx';
                $fieldRules[] = 'max:5120';
            }

            if ($fieldRules !== []) {
                $rules[$fieldName] = implode('|', $fieldRules);
            }
        }

        if ($rules !== []) {
            $request->validate($rules, [
                'required' => ':attribute wajib diisi.',
                'email' => ':attribute harus berupa alamat email yang valid.',
                'numeric' => ':attribute harus berupa angka.',
                'url' => ':attribute harus berupa URL yang valid.',
                'file' => ':attribute harus berupa file yang valid.',
                'mimes' => ':attribute harus berformat PDF, JPG, PNG, DOC, atau DOCX.',
                'max' => ':attribute maksimal 5MB.',
            ], $attributes);
        }

        $formResponses = [];
        $fileUploads = [];

        foreach ($event->eventForm?->form_fields ?? [] as $index => $field) {
            $fieldName = "field_{$index}";

            if (($field['type'] ?? '') === 'file') {
                if ($request->hasFile($fieldName)) {
                    $fileUploads[$field['label']] = $request->file($fieldName);
                    $formResponses[$field['label']] = $request->file($fieldName)->getClientOriginalName();
                }

                continue;
            }

            if (array_key_exists($fieldName, $request->all())) {
                $formResponses[$field['label']] = $request->input($fieldName);
            }
        }

        try {
            $registration = DB::transaction(function () use ($event, $user, $formResponses, $teamName, $teamMembers) {
                $lockedEvent = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

                if ($lockedEvent->status !== 'active' || $lockedEvent->archived_at !== null || $lockedEvent->registered_count >= $lockedEvent->quota) {
                    throw new \RuntimeException('Pendaftaran event ini sudah ditutup atau kuota penuh.');
                }

                $registration = Registration::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'registration_number' => Registration::generateRegistrationNumber($event->id),
                    'team_name' => $teamName,
                    'team_members' => $teamMembers,
                    'status' => 'pending',
                    'registered_at' => now(),
                ]);

                if ($formResponses !== []) {
                    RegistrationResponse::create([
                        'registration_id' => $registration->id,
                        'responses' => $formResponses,
                    ]);
                }

                $lockedEvent->increment('registered_count');

                return $registration;
            });
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        foreach ($fileUploads as $label => $uploadedFile) {
            $path = $uploadedFile->store('registration-documents/' . $registration->id, 'local');

            RegistrationFile::create([
                'registration_id' => $registration->id,
                'field_label' => $label,
                'path' => $path,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getClientMimeType(),
                'size' => $uploadedFile->getSize(),
                'uploaded_by' => $user->id,
            ]);
        }

        return response()->json([
            'message' => 'Pendaftaran berhasil! Silakan upload bukti pembayaran.',
            'registration_id' => $registration->id,
        ], 201);
    }

    public function downloadFile(Request $request, Registration $registration, RegistrationFile $file): Response
    {
        abort_unless($file->registration_id === $registration->id, 404);

        $user = $request->user();
        $isOwner = $registration->user_id === $user->id;
        $isManager = $user->canManageEvents() && $user->canManageEvent($registration->event);

        abort_unless($isOwner || $isManager, 403);

        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    private function ensureOwner(Request $request, Registration $registration): void
    {
        abort_unless($registration->user_id === $request->user()->id, 403, 'Unauthorized action.');
    }

    /**
     * @return array<string, mixed>
     */
    private function listPayload(Registration $registration): array
    {
        $event = $registration->event;

        return [
            'id' => $registration->id,
            'registration_number' => $registration->registration_number,
            'status' => $registration->status,
            'registered_at' => $registration->registered_at?->toIso8601String(),
            'registered_at_label' => $registration->registered_at?->format('d M Y'),
            'created_at' => $registration->created_at?->toIso8601String(),
            'average_score' => (float) ($registration->average_score ?? 0),
            'event' => $event ? [
                'id' => $event->id,
                'name' => $event->name,
                'event_date' => $event->event_date?->toDateString(),
                'event_date_label' => ($event->event_start ?? $event->event_date)?->format('d M Y'),
                'location' => $event->location,
                'price' => (float) $event->price,
                'price_label' => $this->rupiah((float) $event->price),
            ] : null,
            'payment' => $registration->payment ? [
                'status' => $registration->payment->status,
                'has_proof' => filled($registration->payment->proof_file),
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detailPayload(Registration $registration): array
    {
        $event = $registration->event;
        $payload = $this->listPayload($registration);

        $payload['notes'] = $registration->notes;
        $payload['registered_at_time_label'] = $registration->registered_at?->format('d M Y H:i');
        $payload['average_score'] = (float) ($registration->juryScores->avg('score') ?? 0);
        $payload['event'] = $event ? [
            'id' => $event->id,
            'name' => $event->name,
            'organizer' => $event->organizer?->name,
            'event_date' => $event->event_date?->toDateString(),
            'event_date_label' => ($event->event_start ?? $event->event_date)?->format('d M Y'),
            'event_start_label' => $event->event_start?->format('d M Y'),
            'event_end_label' => $event->event_end?->format('d M Y'),
            'location' => $event->location,
            'price' => (float) $event->price,
            'price_label' => $this->rupiah((float) $event->price),
        ] : null;
        $payload['user'] = [
            'name' => $registration->user?->name,
            'email' => $registration->user?->email,
        ];
        $payload['team_name'] = $registration->team_name;
        $payload['team_members'] = $registration->team_members;
        $payload['payment'] = $registration->payment ? $this->paymentPayload($registration->payment) : null;
        $payload['response'] = $registration->response?->responses ?? [];
        $payload['files'] = $registration->files->map(fn (RegistrationFile $file) => [
            'id' => $file->id,
            'label' => $file->field_label,
            'name' => $file->original_name,
            'size' => $file->size,
            'url' => '/registrations/' . $registration->id . '/files/' . $file->id,
        ])->values();
        $payload['jury_scores'] = $registration->juryScores
            ->where('status', 'published')
            ->sortBy('created_at')
            ->map(fn ($score) => [
                'jury' => $score->jury?->name,
                'score' => (float) $score->score,
                'score_label' => number_format((float) $score->score, 2),
                'public_feedback' => $score->public_feedback,
                'created_at_label' => $score->created_at?->format('d M Y'),
            ])
            ->values();

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentPayload(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'amount' => (float) $payment->amount,
            'amount_label' => $this->rupiah((float) $payment->amount),
            'status' => $payment->status,
            'has_proof' => filled($payment->proof_file),
            'created_at' => $payment->created_at?->toIso8601String(),
            'created_at_label' => $payment->created_at?->format('d M Y H:i'),
            'verified_at' => $payment->verified_at?->toIso8601String(),
            'verified_at_label' => $payment->verified_at?->format('d M Y H:i'),
            'verifier' => $payment->verifier?->name,
            'verification_notes' => $payment->verification_notes,
        ];
    }

    private function rupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function results(Request $request, Registration $registration): JsonResponse
    {
        $this->ensureOwner($request, $registration);

        $registration->loadMissing('event');
        $event = $registration->event;

        $publishedScores = $registration->juryScores()
            ->where('status', 'published')
            ->with('jury:id,name')
            ->get();

        $publishedAverage = $publishedScores->avg('score');

        $ranking = Registration::query()
            ->where('event_id', $event->id)
            ->where('status', 'verified')
            ->get()
            ->map(function (Registration $row) {
                $row->setAttribute('published_average', (float) $row->juryScores()
                    ->where('status', 'published')
                    ->avg('score'));

                return $row;
            })
            ->sortByDesc('published_average')
            ->values();

        $rank = $ranking->search(fn (Registration $row) => $row->id === $registration->id) + 1;

        return response()->json([
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'evaluations_published_at' => $event->evaluations_published_at?->toIso8601String(),
            ],
            'published' => $publishedScores->isNotEmpty(),
            'average_score' => $publishedScores->isEmpty() ? null : round((float) $publishedAverage, 2),
            'jury_count' => $publishedScores->count(),
            'rank' => $publishedScores->isEmpty() || $rank < 1 ? null : $rank,
            'total_participants' => $ranking->count(),
            'evaluations' => $publishedScores->map(fn ($score) => [
                'id' => $score->id,
                'jury' => $score->jury?->name,
                'score' => (float) $score->score,
                'public_feedback' => $score->public_feedback,
                'published_at' => $score->published_at?->toIso8601String(),
            ])->values(),
        ]);
    }
}