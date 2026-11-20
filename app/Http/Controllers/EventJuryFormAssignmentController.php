<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Models\EventJuryFormAssignment;
use App\Models\JuryFormTemplate;
use App\Models\Role;
use App\Notifications\JuryAssignmentInvited;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class EventJuryFormAssignmentController extends Controller
{
    /**
     * Show assignment page for an event
     */
    public function index(Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        // Juries visible to this admin: every jury for a global admin, otherwise
        // only accounts they provisioned or that already work on their events.
        $juryQuery = User::whereHas('role', function ($query) {
            $query->where('name', Role::JURY);
        });

        if (!$user->canManageAllEvents()) {
            $juryQuery->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('juryFormAssignments.event', function ($eventQuery) use ($user) {
                        $eventQuery->where('organizer_id', $user->id);
                    });
            });
        }

        $juries = $juryQuery->orderBy('name')->get();

        // Get all form templates for this event
        $formTemplates = $event->juryFormTemplates()
            ->orderBy('name', 'asc')
            ->get();

        // Get existing assignments
        $assignments = EventJuryFormAssignment::where('event_id', $event->id)
            ->with(['jury', 'juryFormTemplate'])
            ->get()
            ->keyBy('jury_id');

        return view('events.jury-assignments', compact('event', 'juries', 'formTemplates', 'assignments'));
    }

    /**
     * Create or refresh an assignment and invite the jury to approve it.
     * A declined assignment is re-opened so the jury can reconsider.
     */
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

    /**
     * Store or update assignment
     */
    public function store(Request $request, Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'jury_ids' => 'required|array',
            'jury_ids.*' => 'required|exists:users,id',
            'jury_form_template_id' => 'required|exists:jury_form_templates,id',
            'jury_role' => 'nullable|string|max:100',
        ]);

        $juryRole = isset($validated['jury_role']) ? trim($validated['jury_role']) : null;
        $juryRole = $juryRole === '' ? null : $juryRole;

        // Verify that the form template belongs to this event
        $formTemplate = JuryFormTemplate::findOrFail($validated['jury_form_template_id']);
        if ($formTemplate->event_id !== $event->id) {
            return back()->with('error', 'Form template tidak valid untuk event ini.');
        }

        $assignedCount = 0;
        $errors = [];

        // Process each jury
        foreach ($validated['jury_ids'] as $juryId) {
            // Verify that the user is a jury
            $jury = User::findOrFail($juryId);
            if (!$jury->isJury()) {
                $errors[] = "User {$jury->name} bukan juri.";
                continue;
            }

            $this->assignJuryToForm($event, $jury, $formTemplate, $juryRole);
            $assignedCount++;
        }

        if ($assignedCount > 0) {
            $message = $assignedCount . ' juri di-assign ke form "' . ($formTemplate->name ?? 'Form Juri') . '". Undangan sudah dikirim dan menunggu persetujuan juri.';
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', $errors);
            }
            return back()->with('success', $message);
        } else {
            return back()->with('error', 'Tidak ada juri yang berhasil di-assign. ' . implode(' ', $errors));
        }
    }

    /**
     * Create a jury account and assign it to a form in one step.
     * The generated access code is shown once to the event admin.
     */
    public function storeAccount(Request $request, Event $event)
    {
        $admin = auth()->user();
        if (!$admin->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'jury_form_template_id' => ['required', 'integer', 'exists:jury_form_templates,id'],
            'jury_role' => ['nullable', 'string', 'max:100'],
        ], [], [
            'name' => 'nama',
            'email' => 'email',
            'jury_form_template_id' => 'form penilaian',
        ]);

        $juryRole = isset($validated['jury_role']) ? trim($validated['jury_role']) : null;
        $juryRole = $juryRole === '' ? null : $juryRole;

        $formTemplate = JuryFormTemplate::where('event_id', $event->id)
            ->find($validated['jury_form_template_id']);

        if (!$formTemplate) {
            return back()->withInput()
                ->with('error', 'Form penilaian tidak valid untuk event ini.');
        }

        $email = Str::lower(trim($validated['email']));
        $existing = User::where('email', $email)->first();

        if ($existing) {
            if (!$existing->isJury()) {
                return back()->withInput()->with(
                    'error',
                    "Email {$email} sudah terdaftar sebagai {$existing->roleLabel()}. Gunakan email lain atau ubah role melalui Admin Owner."
                );
            }

            if (!$existing->isActive()) {
                return back()->withInput()->with(
                    'error',
                    "Akun juri {$existing->name} sedang nonaktif. Aktifkan akun terlebih dahulu melalui Admin Owner."
                );
            }

            $this->assignJuryToForm($event, $existing, $formTemplate, $juryRole);

            return back()->with(
                'success',
                "Akun juri {$existing->name} sudah tersedia dan di-assign ke form \"{$formTemplate->name}\". Undangan sudah dikirim dan menunggu persetujuan juri."
            );
        }

        $juryRoleRecord = Role::where('name', Role::JURY)->firstOrFail();
        $accessCode = $this->generateAccessCode();

        $jury = User::create([
            'name' => trim($validated['name']),
            'email' => $email,
            'password' => $accessCode,
            'role_id' => $juryRoleRecord->id,
            'is_active' => true,
            'event_scope' => 'all',
            'created_by' => $admin->id,
        ]);

        $this->assignJuryToForm($event, $jury, $formTemplate, $juryRole);

        return back()
            ->with('success', "Akun juri {$jury->name} berhasil dibuat dan di-assign ke form \"{$formTemplate->name}\". Undangan sudah dikirim dan menunggu persetujuan juri.")
            ->with('jury_credentials', [
                'name' => $jury->name,
                'email' => $jury->email,
                'code' => $accessCode,
                'form' => $formTemplate->name,
            ]);
    }

    /**
     * A jury account may be attached/re-credentialed by an event admin only when
     * that admin provisioned it. Global admins (admin_owner / scope=all) may act
     * on any jury account.
     */
    private function canManageJuryAccount(User $jury): bool
    {
        $actor = auth()->user();

        return $actor->canManageAllEvents() || $jury->created_by === $actor->id;
    }

    /**
     * Generate a short, copy-friendly temporary password.
     * Ambiguous characters (i, l, o, 0, 1) are excluded.
     */
    private function generateAccessCode(): string
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789';
        $code = '';

        for ($i = 0; $i < 8; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }

    /**
     * Set a new password for an assigned jury and show it once.
     * Admin may type a memorable password; leaving it empty generates an access code.
     * Stored passwords are hashed, so the previous one can never be displayed.
     */
    public function resetJuryPassword(Request $request, Event $event, EventJuryFormAssignment $assignment)
    {
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        if ($assignment->event_id !== $event->id) {
            abort(403, 'Unauthorized action.');
        }

        $jury = $assignment->jury;
        if (!$jury) {
            return back()->with('error', 'Akun juri untuk assignment ini tidak ditemukan.');
        }

        if (!$this->canManageJuryAccount($jury)) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'password' => ['nullable', 'string', Password::defaults()],
        ], [
            'password.min' => 'Password baru minimal :min karakter.',
            'password.string' => 'Password baru harus berupa teks.',
        ], [
            'password' => 'password baru',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('jury_password_error_assignment', $assignment->id);
        }

        $customPassword = trim((string) $request->input('password', ''));
        $isCustom = $customPassword !== '';
        $newPassword = $isCustom ? $customPassword : $this->generateAccessCode();

        $jury->update(['password' => $newPassword]);

        // Password melekat pada akun, bukan pada assignment: juri yang bertugas di
        // beberapa event memakai password yang sama di semua event tersebut.
        $otherEvents = EventJuryFormAssignment::where('jury_id', $jury->id)
            ->where('event_id', '!=', $event->id)
            ->with('event:id,name')
            ->get()
            ->pluck('event.name')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return back()->with('jury_password_reset', [
            'name' => $jury->name,
            'email' => $jury->email,
            'code' => $newPassword,
            'custom' => $isCustom,
            'role' => $assignment->jury_role,
            'form' => $assignment->juryFormTemplate->name ?? null,
            'other_events' => $otherEvents,
        ]);
    }

    /**
     * Delete assignment
     */
    public function destroy(Event $event, EventJuryFormAssignment $assignment)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        // Verify assignment belongs to this event
        if ($assignment->event_id !== $event->id) {
            abort(403, 'Unauthorized action.');
        }

        $assignment->delete();

        return back()->with('success', 'Assignment berhasil dihapus!');
    }

    /**
     * Quick create form and assign juries in one step
     */
    public function quickCreate(Request $request, Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'form_name' => 'required|string|max:255',
            'template_key' => 'required|string',
            'jury_ids' => 'required|array',
            'jury_ids.*' => 'required|exists:users,id',
            'jury_role' => 'nullable|string|max:100',
        ]);

        $juryRole = isset($validated['jury_role']) ? trim($validated['jury_role']) : null;
        $juryRole = $juryRole === '' ? null : $juryRole;

        // Get template
        $templates = JuryFormTemplate::getTemplates();
        if (!isset($templates[$validated['template_key']])) {
            return back()->with('error', 'Template tidak valid.');
        }

        $template = $templates[$validated['template_key']];

        // Create new form template
        $newForm = JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => $validated['form_name'],
            'criteria_fields' => $template['fields'],
            'use_default_score' => $template['use_default_score'] ?? true,
        ]);

        // Assign juries to the new form
        $assignedCount = 0;
        $errors = [];

        foreach ($validated['jury_ids'] as $juryId) {
            $jury = User::findOrFail($juryId);
            if (!$jury->isJury()) {
                $errors[] = "User {$jury->name} bukan juri.";
                continue;
            }

            $this->assignJuryToForm($event, $jury, $newForm, $juryRole);
            $assignedCount++;
        }

        if ($assignedCount > 0) {
            $message = "Form '{$validated['form_name']}' berhasil dibuat dan {$assignedCount} juri di-assign. Undangan sudah dikirim dan menunggu persetujuan juri.";
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', $errors);
            }
            return back()->with('success', $message);
        } else {
            return back()->with('error', 'Form berhasil dibuat, tapi tidak ada juri yang berhasil di-assign. ' . implode(' ', $errors));
        }
    }
}
