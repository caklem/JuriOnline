<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\JuryFormTemplate;
use App\Models\CustomJuryFormTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JuryFormTemplateController extends Controller
{
    public function create(Event $event)
    {
        // Check authorization - only admin and admin_owner
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }

        $templates = JuryFormTemplate::getTemplates();
        $customTemplates = CustomJuryFormTemplate::orderBy('created_at', 'desc')->get();

        // Force fresh data from database - bypass all caching
        \DB::connection()->getPdo()->exec('SET SESSION query_cache_type = OFF');
        $existingForm = JuryFormTemplate::where('event_id', $event->id)
            ->orderBy('id', 'desc')
            ->first();

        // If still using relationship, force refresh
        if (!$existingForm) {
            $event->load('juryFormTemplate');
            $existingForm = $event->juryFormTemplate;
        }

        if ($existingForm) {
            // Force decode JSON to ensure fresh data
            $existingForm->criteria_fields = json_decode(json_encode($existingForm->criteria_fields), true);
        }

        return view('jury-forms.create', compact('event', 'templates', 'customTemplates', 'existingForm'));
    }

    public function customBuilder(Event $event)
    {
        // Check authorization - only admin and admin_owner
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }

        // Force fresh data from database - bypass all caching
        DB::connection()->getPdo()->exec('SET SESSION query_cache_type = OFF');
        $existingForm = JuryFormTemplate::where('event_id', $event->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$existingForm) {
            $event->load('juryFormTemplate');
            $existingForm = $event->juryFormTemplate;
        }

        if ($existingForm) {
            // Force decode JSON to ensure fresh data
            $existingForm->criteria_fields = json_decode(json_encode($existingForm->criteria_fields), true);
        }

        return view('jury-forms.custom-builder', compact('event', 'existingForm'));
    }

    public function store(Request $request, Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'criteria_fields' => 'required|json',
            'use_default_score' => 'nullable|boolean',
            'editing_template' => 'nullable|string',
            'template_id' => 'nullable|integer',
            'name' => 'nullable|string|max:255', // Nama form: Juri Vokal, Juri Adab, dll
            'form_template_id' => 'nullable|integer', // ID form yang sedang di-edit (jika edit)
        ]);

        $criteriaFields = json_decode($validated['criteria_fields'], true);
        $formName = $request->input('name');
        $formTemplateId = $request->input('form_template_id');
        
        // Validate form name
        if (empty($formName) || trim($formName) === '') {
            $formName = 'Form Juri';
        }

        // If editing existing form template
        if ($formTemplateId) {
            $existingForm = JuryFormTemplate::where('event_id', $event->id)
                ->where('id', $formTemplateId)
                ->first();
            
            if ($existingForm) {
                $existingForm->update([
                    'name' => $formName,
                    'criteria_fields' => $criteriaFields,
                    'use_default_score' => $request->input('use_default_score', false) == '1' || $request->input('use_default_score', false) === true
                ]);
                $newForm = $existingForm;
            } else {
                // Create new if not found
                $newForm = JuryFormTemplate::create([
                    'event_id' => $event->id,
                    'name' => $formName,
                    'criteria_fields' => $criteriaFields,
                    'use_default_score' => $request->input('use_default_score', false) == '1' || $request->input('use_default_score', false) === true
                ]);
            }
        } else {
            // For backward compatibility: if no name provided and only one form exists, update it
            // Otherwise, create new form
            $existingForms = $event->juryFormTemplates()->count();
            
            if ($existingForms === 1 && !$formName) {
                // Update existing single form (backward compatibility)
                $existingForm = $event->juryFormTemplate;
                if ($existingForm) {
                    $existingForm->update([
                        'criteria_fields' => $criteriaFields,
                        'use_default_score' => $request->input('use_default_score', false) == '1' || $request->input('use_default_score', false) === true
                    ]);
                    $newForm = $existingForm;
                } else {
                    // Create new if not found
                    $newForm = JuryFormTemplate::create([
                        'event_id' => $event->id,
                        'name' => $formName,
                        'criteria_fields' => $criteriaFields,
                        'use_default_score' => $request->input('use_default_score', false) == '1' || $request->input('use_default_score', false) === true
                    ]);
                }
            } else {
                // Create new form (support multiple forms)
                $newForm = JuryFormTemplate::create([
                    'event_id' => $event->id,
                    'name' => $formName ?? 'Form Juri',
                    'criteria_fields' => $criteriaFields,
                    'use_default_score' => $request->input('use_default_score', false) == '1' || $request->input('use_default_score', false) === true
                ]);
            }
        }

        // Auto-save as custom template HANYA jika BUKAN dari edit template
        $isEditingTemplate = $request->input('editing_template') === 'true';
        $templateId = $request->input('template_id');
        
        if ($isEditingTemplate && $templateId) {
            // Update existing template
            $template = CustomJuryFormTemplate::find($templateId);
            if ($template) {
                $template->update([
                    'criteria_fields' => $criteriaFields,
                    'use_default_score' => $request->input('use_default_score', false) == '1' || $request->input('use_default_score', false) === true
                ]);
            }
        } elseif (!$isEditingTemplate) {
            // Create new template only if not editing
            $templateName = 'Template ' . $event->name . ' - ' . now()->format('d M Y H:i');
            CustomJuryFormTemplate::create([
                'name' => $templateName,
                'description' => 'Auto-saved dari form penilaian event ' . $event->name,
                'criteria_fields' => $criteriaFields,
                'use_default_score' => $request->input('use_default_score', false) == '1' || $request->input('use_default_score', false) === true,
                'created_by' => auth()->id()
            ]);
        }

        // Clear any cached data
        cache()->forget('jury_form_' . $event->id);

        return redirect()->route('events.jury-form.custom', $event)
            ->with('success', $isEditingTemplate && $templateId ? '✅ Form penilaian juri berhasil disimpan dan template berhasil diupdate!' : '✅ Form penilaian juri berhasil disimpan' . (!$isEditingTemplate ? ' dan otomatis tersimpan sebagai template!' : '!'))
            ->with('show_success_modal', true);
    }

    public function useTemplate(Event $event, $template)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }

        $templates = JuryFormTemplate::getTemplates();

        if (!isset($templates[$template])) {
            abort(404, 'Template tidak ditemukan');
        }

        // Delete existing form if any
        if ($event->juryFormTemplate) {
            $event->juryFormTemplate->delete();
        }

        JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => $templates[$template]['name'], // Gunakan nama template sebagai nama form
            'criteria_fields' => $templates[$template]['fields'],
            'use_default_score' => $templates[$template]['use_default_score']
        ]);

        return redirect()->route('events.jury-form.create', $event)
            ->with('template_success', "Form penilaian juri berhasil dibuat menggunakan template {$templates[$template]['name']}!");
    }

    public function saveAsTemplate(Request $request, Event $event)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'template_description' => 'nullable|string',
            'criteria_fields' => 'required|json',
            'use_default_score' => 'nullable|boolean'
        ]);

        $criteriaFields = json_decode($validated['criteria_fields'], true);

        CustomJuryFormTemplate::create([
            'name' => $validated['template_name'],
            'description' => $validated['template_description'],
            'criteria_fields' => $criteriaFields,
            'use_default_score' => $request->input('use_default_score', false) == '1' || $request->input('use_default_score', false) === true,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('events.jury-form.create', $event)
            ->with('success', 'Template berhasil disimpan!');
    }

    public function useCustomTemplate(Event $event, CustomJuryFormTemplate $customTemplate)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete existing form if any
        if ($event->juryFormTemplate) {
            $event->juryFormTemplate->delete();
        }

        JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => $customTemplate->name, // Gunakan nama custom template sebagai nama form
            'criteria_fields' => $customTemplate->criteria_fields,
            'use_default_score' => $customTemplate->use_default_score
        ]);

        return redirect()->route('events.jury-form.create', $event)
            ->with('template_success', "Form penilaian juri berhasil dibuat menggunakan template {$customTemplate->name}!");
    }

    public function updateCustomTemplate(Request $request, CustomJuryFormTemplate $customTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'criteria_fields' => 'required|json',
            'use_default_score' => 'nullable|boolean'
        ]);

        $customTemplate->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'criteria_fields' => json_decode($validated['criteria_fields'], true),
            'use_default_score' => $request->input('use_default_score', false) == '1' || $request->input('use_default_score', false) === true
        ]);

        return back()->with('success', 'Template berhasil diupdate!');
    }

    public function deleteCustomTemplate(CustomJuryFormTemplate $customTemplate)
    {
        $customTemplate->delete();
        return back()->with('success', 'Template berhasil dihapus!');
    }
}
