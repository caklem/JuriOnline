<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventForm;
use App\Models\CustomEventFormTemplate;
use Illuminate\Http\Request;

class EventFormController extends Controller
{
    public function customBuilder(Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $existingForm = $event->eventForm;

        return view('event-forms.custom-builder', compact('event', 'existingForm'));
    }

    public function create(Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $templates = EventForm::getTemplates();
        $customTemplates = CustomEventFormTemplate::query()
            ->when(!$user->canManageAllEvents(), fn ($query) => $query->where('created_by', $user->id))
            ->orderBy('created_at', 'desc')
            ->get();
        $existingForm = $event->eventForm;

        return view('event-forms.create', compact('event', 'templates', 'customTemplates', 'existingForm'));
    }

    public function store(Request $request, Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'form_fields' => 'required|json',
            'editing_template' => 'nullable|string',
            'template_id' => 'nullable|integer'
        ]);

        $formFields = json_decode($validated['form_fields'], true);

        // Delete existing form if any
        if ($event->eventForm) {
            $event->eventForm->delete();
        }

        EventForm::create([
            'event_id' => $event->id,
            'form_fields' => $formFields
        ]);

        // Auto-save as custom template HANYA jika BUKAN dari edit template
        $isEditingTemplate = $request->input('editing_template') === 'true';
        $templateId = $request->input('template_id');
        
        if ($isEditingTemplate && $templateId) {
            // Update existing template
            $template = CustomEventFormTemplate::find($templateId);
            if ($template) {
                if (!$user->canManageAllEvents() && $template->created_by !== $user->id) {
                    abort(403, 'Unauthorized action.');
                }

                $template->update([
                    'form_fields' => $formFields
                ]);
            }
        } elseif (!$isEditingTemplate) {
            // Create new template only if not editing
            $templateName = 'Template ' . $event->name . ' - ' . now()->format('d M Y H:i');
            CustomEventFormTemplate::create([
                'name' => $templateName,
                'description' => 'Auto-saved dari form pendaftaran event ' . $event->name,
                'form_fields' => $formFields,
                'created_by' => auth()->id()
            ]);
        }

        // Check if request is from custom builder
        $fromCustomBuilder = $request->header('Referer') && strpos($request->header('Referer'), '/form/custom') !== false;

        if ($fromCustomBuilder) {
            return redirect()->route('events.form.custom', $event)
                ->with('success', $isEditingTemplate && $templateId ? '✅ Form pendaftaran berhasil disimpan dan template berhasil diupdate!' : '✅ Form pendaftaran berhasil disimpan' . (!$isEditingTemplate ? ' dan otomatis tersimpan sebagai template!' : '!'))
                ->with('show_success_modal', true);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Form pendaftaran berhasil disimpan dan otomatis tersimpan sebagai template!');
    }

    public function useTemplate(Event $event, $template)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $templates = EventForm::getTemplates();

        if (!isset($templates[$template])) {
            abort(404, 'Template tidak ditemukan');
        }

        // Delete existing form if any
        if ($event->eventForm) {
            $event->eventForm->delete();
        }

        EventForm::create([
            'event_id' => $event->id,
            'form_fields' => $templates[$template]['fields']
        ]);

        return redirect()->route('events.form.create', $event)
            ->with('template_success', "Form pendaftaran berhasil dibuat menggunakan template {$templates[$template]['name']}!");
    }

    public function saveAsTemplate(Request $request, Event $event)
    {
        $user = auth()->user();

        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'template_description' => 'nullable|string',
            'form_fields' => 'required|json'
        ]);

        $formFields = json_decode($validated['form_fields'], true);

        CustomEventFormTemplate::create([
            'name' => $validated['template_name'],
            'description' => $validated['template_description'],
            'form_fields' => $formFields,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('events.form.create', $event)
            ->with('success', 'Template berhasil disimpan!');
    }

    public function useCustomTemplate(Event $event, CustomEventFormTemplate $customTemplate)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        if (!$user->canManageAllEvents() && $customTemplate->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete existing form if any
        if ($event->eventForm) {
            $event->eventForm->delete();
        }

        EventForm::create([
            'event_id' => $event->id,
            'form_fields' => $customTemplate->form_fields
        ]);

        return redirect()->route('events.form.create', $event)
            ->with('template_success', "Form pendaftaran berhasil dibuat menggunakan template {$customTemplate->name}!");
    }

    public function updateCustomTemplate(Request $request, CustomEventFormTemplate $customTemplate)
    {
        $user = auth()->user();

        if (!$user->canManageEvents()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$user->canManageAllEvents() && $customTemplate->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'form_fields' => 'required|json'
        ]);

        $customTemplate->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'form_fields' => json_decode($validated['form_fields'], true)
        ]);

        return back()->with('success', 'Template berhasil diupdate!');
    }

    public function deleteCustomTemplate(CustomEventFormTemplate $customTemplate)
    {
        $user = auth()->user();

        if (!$user->canManageEvents()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$user->canManageAllEvents() && $customTemplate->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $customTemplate->delete();
        return back()->with('success', 'Template berhasil dihapus!');
    }
}
