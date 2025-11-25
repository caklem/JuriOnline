<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Models\EventJuryFormAssignment;
use App\Models\JuryFormTemplate;
use Illuminate\Http\Request;

class EventJuryFormAssignmentController extends Controller
{
    /**
     * Show assignment page for an event
     */
    public function index(Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if ($user->isOrganizer() && $event->organizer_id !== $user->id && !$user->isAdmin() && !$user->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }

        // Get all juries
        $juries = User::whereHas('role', function($query) {
            $query->where('name', 'jury');
        })->orderBy('name')->get();

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
     * Store or update assignment
     */
    public function store(Request $request, Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if ($user->isOrganizer() && $event->organizer_id !== $user->id && !$user->isAdmin() && !$user->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'jury_ids' => 'required|array',
            'jury_ids.*' => 'required|exists:users,id',
            'jury_form_template_id' => 'required|exists:jury_form_templates,id',
        ]);

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

            // Create or update assignment
            EventJuryFormAssignment::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'jury_id' => $juryId,
                ],
                [
                    'jury_form_template_id' => $validated['jury_form_template_id'],
                ]
            );
            $assignedCount++;
        }

        if ($assignedCount > 0) {
            $message = $assignedCount . ' juri berhasil di-assign ke form "' . ($formTemplate->name ?? 'Form Juri') . '"!';
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', $errors);
            }
            return back()->with('success', $message);
        } else {
            return back()->with('error', 'Tidak ada juri yang berhasil di-assign. ' . implode(' ', $errors));
        }
    }

    /**
     * Delete assignment
     */
    public function destroy(Event $event, EventJuryFormAssignment $assignment)
    {
        // Check authorization
        $user = auth()->user();
        if ($user->isOrganizer() && $event->organizer_id !== $user->id && !$user->isAdmin() && !$user->isAdminOwner()) {
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
        if ($user->isOrganizer() && $event->organizer_id !== $user->id && !$user->isAdmin() && !$user->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'form_name' => 'required|string|max:255',
            'template_key' => 'required|string',
            'jury_ids' => 'required|array',
            'jury_ids.*' => 'required|exists:users,id',
        ]);

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

            EventJuryFormAssignment::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'jury_id' => $juryId,
                ],
                [
                    'jury_form_template_id' => $newForm->id,
                ]
            );
            $assignedCount++;
        }

        if ($assignedCount > 0) {
            $message = "Form '{$validated['form_name']}' berhasil dibuat dan {$assignedCount} juri berhasil di-assign!";
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', $errors);
            }
            return back()->with('success', $message);
        } else {
            return back()->with('error', 'Form berhasil dibuat, tapi tidak ada juri yang berhasil di-assign. ' . implode(' ', $errors));
        }
    }
}
