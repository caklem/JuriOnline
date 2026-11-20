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
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $templates = JuryFormTemplate::getTemplates();
        $customTemplates = CustomJuryFormTemplate::query()
            ->when(!$user->canManageAllEvents(), fn ($query) => $query->where('created_by', $user->id))
            ->orderBy('created_at', 'desc')
            ->get();

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
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        // Tanpa ?form=ID berarti mode buat form baru.
        // Dengan ?form=ID berarti mengedit form tertentu pada event ini.
        $existingForm = null;
        $formId = request()->integer('form');

        if ($formId) {
            $existingForm = $event->juryFormTemplates()->find($formId);

            if (!$existingForm) {
                abort(404, 'Form penilaian tidak ditemukan untuk event ini.');
            }

            // Force decode JSON to ensure fresh data
            $existingForm->criteria_fields = json_decode(json_encode($existingForm->criteria_fields), true);
        }

        return view('jury-forms.custom-builder', compact('event', 'existingForm'));
    }

    public function store(Request $request, Event $event)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'criteria_fields' => 'required|json',
            'use_default_score' => 'nullable|boolean',
            'editing_template' => 'nullable|string',
            'template_id' => 'nullable|integer',
            'name' => 'required|string|max:255', // Nama form: Juri Vokal, Juri Adab, dll
            'form_template_id' => 'nullable|integer', // ID form yang sedang di-edit (jika edit)
        ]);

        $criteriaFields = json_decode($validated['criteria_fields'], true);
        $formName = trim($validated['name']);
        $formTemplateId = $validated['form_template_id'] ?? null;
        $useDefaultScore = $request->boolean('use_default_score');

        if ($formTemplateId) {
            // Edit form tertentu milik event ini
            $newForm = $event->juryFormTemplates()->find($formTemplateId);

            if (!$newForm) {
                return back()->withInput()
                    ->with('error', 'Form penilaian tidak ditemukan untuk event ini.');
            }

            $newForm->update([
                'name' => $formName,
                'criteria_fields' => $criteriaFields,
                'use_default_score' => $useDefaultScore,
            ]);

            $formMessage = "Form \"{$formName}\" berhasil diperbarui.";
        } else {
            // Buat form baru: satu event boleh punya banyak form juri
            $newForm = JuryFormTemplate::create([
                'event_id' => $event->id,
                'name' => $formName,
                'criteria_fields' => $criteriaFields,
                'use_default_score' => $useDefaultScore,
            ]);

            $formMessage = "Form \"{$formName}\" berhasil dibuat.";
        }

        // Auto-save as custom template HANYA jika BUKAN dari edit template
        $isEditingTemplate = $request->input('editing_template') === 'true';
        $templateId = $request->input('template_id');
        
        if ($isEditingTemplate && $templateId) {
            // Update existing template
            $template = CustomJuryFormTemplate::find($templateId);
            if ($template) {
                if (!$user->canManageAllEvents() && $template->created_by !== $user->id) {
                    abort(403, 'Unauthorized action.');
                }

                $template->update([
                    'criteria_fields' => $criteriaFields,
                    'use_default_score' => $useDefaultScore
                ]);
            }
        } elseif (!$isEditingTemplate) {
            // Create new template only if not editing
            $templateName = 'Template ' . $event->name . ' - ' . now()->format('d M Y H:i');
            CustomJuryFormTemplate::create([
                'name' => $templateName,
                'description' => 'Auto-saved dari form penilaian event ' . $event->name,
                'criteria_fields' => $criteriaFields,
                'use_default_score' => $useDefaultScore,
                'created_by' => auth()->id()
            ]);
        }

        // Clear any cached data
        cache()->forget('jury_form_' . $event->id);

        $successMessage = $isEditingTemplate && $templateId
            ? '✅ Form penilaian juri berhasil disimpan dan template berhasil diupdate!'
            : '✅ ' . $formMessage . (!$isEditingTemplate ? ' Otomatis tersimpan sebagai template.' : '');

        return redirect()
            ->route('events.jury-form.custom', ['event' => $event, 'form' => $newForm->id])
            ->with('success', $successMessage)
            ->with('show_success_modal', true);
    }

    public function useTemplate(Event $event, $template)
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        $templates = JuryFormTemplate::getTemplates();

        if (!isset($templates[$template])) {
            abort(404, 'Template tidak ditemukan');
        }

        // Tambahkan sebagai form baru; form yang sudah ada tidak ditimpa
        JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => $this->uniqueFormName($event, $templates[$template]['name']),
            'criteria_fields' => $templates[$template]['fields'],
            'use_default_score' => $templates[$template]['use_default_score']
        ]);

        return redirect()->route('events.jury-form.create', $event)
            ->with('template_success', "Form penilaian juri berhasil dibuat menggunakan template {$templates[$template]['name']}!");
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
        if (!$user->canManageEvent($event)) {
            abort(403, 'Unauthorized action.');
        }

        if (!$user->canManageAllEvents() && $customTemplate->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Tambahkan sebagai form baru; form yang sudah ada tidak ditimpa
        JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => $this->uniqueFormName($event, $customTemplate->name),
            'criteria_fields' => $customTemplate->criteria_fields,
            'use_default_score' => $customTemplate->use_default_score
        ]);

        return redirect()->route('events.jury-form.create', $event)
            ->with('template_success', "Form penilaian juri berhasil dibuat menggunakan template {$customTemplate->name}!");
    }

    /**
     * Pastikan nama form unik di dalam satu event agar beberapa form mudah dibedakan.
     */
    private function uniqueFormName(Event $event, string $baseName): string
    {
        $baseName = trim($baseName) !== '' ? trim($baseName) : 'Form Juri';
        $name = $baseName;
        $counter = 2;

        while ($event->juryFormTemplates()->where('name', $name)->exists()) {
            $name = "{$baseName} ({$counter})";
            $counter++;
        }

        return $name;
    }

    public function updateCustomTemplate(Request $request, CustomJuryFormTemplate $customTemplate)
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
