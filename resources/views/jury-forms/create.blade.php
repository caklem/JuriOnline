@extends('layouts.app')

@section('title', 'Kelola Form Penilaian Juri')

@section('content')

<!-- Success Modal -->
@if(session('template_success'))
<div id="successModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 p-4" style="animation: fadeIn 0.3s;">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl" style="animation: slideUp 0.3s;">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 ring-1 ring-indigo-200" style="animation: checkPulse 0.6s;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-8 w-8 text-indigo-600" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
            </svg>
        </div>
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Berhasil!</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ session('template_success') }}</p>
        <div class="mt-6 inline-flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-medium text-slate-500 ring-1 ring-slate-200">
            <span class="spinner" style="display: inline-block; width: 12px; height: 12px; border: 2px solid #cbd5e1; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite;"></span>
            Menutup otomatis...
        </div>
    </div>
</div>

<style>
    @keyframes slideUp {
        from {
            transform: translateY(50px) scale(0.9);
            opacity: 0;
        }
        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.95);
        }
    }
    
    @keyframes checkPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
    setTimeout(function() {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.style.animation = 'fadeOut 0.3s';
            setTimeout(function() {
                modal.remove();
            }, 300);
        }
    }, 2000);
</script>
@endif

<div class="page-header">
    <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-indigo-600">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0 6-6m-6 6 6 6" />
        </svg>
        Kembali ke Detail Event
    </a>

    <div class="mt-4">
        <h1>Kelola Form Penilaian Juri</h1>
        <p>{{ $event->name }}</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success mt-5">
        {{ session('success') }}
    </div>
    @endif

    @php
        $existingFormsCount = $event->juryFormTemplates()->count();
    @endphp

    @if($existingFormsCount > 0)
    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <strong class="text-sm font-semibold text-emerald-800">Form yang sudah dibuat ({{ $existingFormsCount }}):</strong>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach($event->juryFormTemplates as $form)
            <a href="{{ route('events.jury-form.custom', ['event' => $event, 'form' => $form->id]) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700"
               title="Edit form ini">
                {{ $form->name ?? 'Form Juri' }}
                <span class="text-xs font-medium text-indigo-600">Edit</span>
            </a>
            @endforeach
        </div>
        <p class="mt-3 text-sm text-emerald-800">
            <strong class="font-semibold">Tip:</strong> Anda bisa membuat form tambahan untuk juri yang berbeda. Contoh: Form Juri Vokal, Form Juri Adab, Form Juri Terbang, dll.
        </p>
    </div>
    @endif

    <!-- Custom Form Builder Button -->
    <div class="mt-5">
        <a href="{{ route('events.jury-form.custom', $event) }}" class="btn-premium btn-premium-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
            </svg>
            <strong>{{ $existingFormsCount > 0 ? 'Buat Form Penilaian Baru' : 'Buat Form Penilaian Custom' }}</strong>
        </a>
        <p class="mt-2 text-sm italic text-slate-500">
            {{ $existingFormsCount > 0 ? 'Tambahkan form penilaian baru untuk juri yang berbeda' : 'Buat kriteria penilaian sesuai kebutuhan event Anda dengan form builder yang mudah' }}
        </p>
    </div>

    @if($existingForm)
    <div class="alert alert-success mt-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-medium">Event ini sudah memiliki form penilaian custom untuk juri</p>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('events.jury-form.custom', ['event' => $event, 'form' => $existingForm->id]) }}" class="btn btn-warning">
                    Edit Form
                </a>
                <button type="button" class="btn btn-primary" onclick="showPreview()">
                    Preview Form
                </button>
            </div>
        </div>
    </div>


    @else
    <div class="alert alert-warning mt-5">
        Event ini menggunakan form penilaian standar (Nilai 0-100 + Feedback)
    </div>
    @endif
</div>

<!-- Template Form -->
<div class="card">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Pilih Template Form Penilaian</h2>
            <p class="mt-1 text-sm text-slate-500">Gunakan template yang sudah tersedia untuk berbagai jenis kompetisi</p>
        </div>
    </div>

    @php
    // Check if using custom form (not matching any template, including custom templates)
    $isCustomForm = false;
    if ($existingForm && $existingForm->criteria_fields) {
        $isCustomForm = true;
        
        // Check against standard templates
        foreach($templates as $key => $template) {
            if (json_encode($template['fields']) === json_encode($existingForm->criteria_fields)) {
                $isCustomForm = false;
                break;
            }
        }
        
        // Also check against custom templates
        if ($isCustomForm) {
            foreach($customTemplates as $customTemplate) {
                if (json_encode($customTemplate->criteria_fields) === json_encode($existingForm->criteria_fields)) {
                    $isCustomForm = false;
                    break;
                }
            }
        }
    }
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <!-- Custom Form Card -->
        <div class="relative flex flex-col rounded-2xl border p-6 transition {{ $isCustomForm ? 'border-indigo-300 bg-indigo-50/60 shadow-sm' : 'border-slate-200 bg-white hover:border-indigo-200 hover:shadow-sm' }}">
            @if($isCustomForm)
            <span class="absolute -top-3 right-4 inline-flex items-center gap-1 rounded-full bg-indigo-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-white shadow-sm">
                Sedang digunakan
            </span>
            @endif
            <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5 text-indigo-500" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                </svg>
                Form Penilaian Custom
            </h3>
            <div class="mt-3 text-sm text-slate-600">
                <strong class="font-semibold text-slate-900">Fitur:</strong>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Buat kriteria sesuai kebutuhan</li>
                    <li>Tambah/hapus kriteria sesuka hati</li>
                    <li>Atur nilai maksimal per kriteria</li>
                    <li>Edit dengan Google Form style</li>
                </ul>
                @if($isCustomForm && $existingForm)
                @php
                $totalMaxCustom = array_sum(array_column($existingForm->criteria_fields, 'max'));
                @endphp
                <div class="mt-3 space-y-0.5 rounded-lg bg-white/80 px-3 py-2 ring-1 ring-indigo-200">
                    <div><strong class="font-semibold text-slate-900">Total Kriteria:</strong> {{ count($existingForm->criteria_fields) }} kriteria</div>
                    <div><strong class="font-semibold text-slate-900">Total Maksimal:</strong> {{ $totalMaxCustom }} poin</div>
                </div>
                @endif
            </div>
            <div class="mt-5 flex flex-col gap-2">
                @if($isCustomForm)
                <button type="button" class="btn btn-primary w-full" disabled>
                    Custom Form Aktif
                </button>
                <button type="button" class="btn btn-outline-secondary w-full" onclick="showTemplateSelection()">
                    Gunakan Template
                </button>
                @else
                <a href="{{ route('events.jury-form.custom', $event) }}" class="btn btn-primary w-full">
                    Buat Form Custom
                </a>
                @endif
            </div>
        </div>

        @foreach($templates as $key => $template)
        @php
        // Check if this template is currently being used
        $isActive = false;
        if ($existingForm && $existingForm->criteria_fields) {
            // Compare entire field structure (not just labels)
            $isActive = json_encode($template['fields']) === json_encode($existingForm->criteria_fields);
        }
        $totalMax = 0;
        @endphp
        <div class="relative flex flex-col rounded-2xl border p-6 transition {{ $isActive ? 'border-emerald-300 bg-emerald-50/60 shadow-sm' : 'border-slate-200 bg-white hover:border-indigo-200 hover:shadow-sm' }}">
            @if($isActive)
            <span class="absolute -top-3 right-4 inline-flex items-center gap-1 rounded-full bg-emerald-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-white shadow-sm">
                Sedang digunakan
            </span>
            @endif
            <h3 class="text-base font-semibold text-slate-900">{{ $template['name'] }}</h3>
            <div class="mt-3 text-sm text-slate-600">
                <strong class="font-semibold text-slate-900">Kriteria Penilaian:</strong>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($template['fields'] as $field)
                    @php $totalMax += $field['max']; @endphp
                    <li>{{ $field['label'] }} <span class="text-slate-400">({{ $field['max'] }} poin)</span> @if($field['required'])<span style="color: #e11d48;">*</span>@endif</li>
                    @endforeach
                </ul>
                <div class="mt-3 rounded-lg bg-white/80 px-3 py-2 ring-1 ring-slate-200">
                    <strong class="font-semibold text-slate-900">Total Maksimal:</strong> {{ $totalMax }} poin
                </div>
            </div>
            <div class="mt-5 flex gap-2">
                <form method="POST" action="{{ route('events.jury-form.template', [$event, $key]) }}" class="flex-1" id="templateForm{{ $key }}">
                    @csrf
                    <button type="button" class="btn btn-primary w-full"
                        {{ $isActive ? 'disabled' : '' }}
                        onclick="{{ $isActive ? '' : 'showConfirmModal(\'Gunakan Template?\', \'Template ini akan ditambahkan sebagai form baru. Form yang ada tidak diubah.\', () => document.getElementById(\'templateForm' . $key . '\').submit())' }}">
                        {{ $isActive ? 'Template Aktif' : 'Gunakan Template' }}
                    </button>
                </form>
                <button type="button" class="btn btn-info w-[112px] shrink-0" onclick="editJuryTemplate({{ json_encode($template['fields']) }}, '{{ $template['name'] }}')" title="Edit kriteria template">
                    Edit Kriteria
                </button>
            </div>
        </div>
        @endforeach

        @foreach($customTemplates as $customTemplate)
        @php
        // Check if this custom template is currently being used
        $isActiveCustom = false;
        if ($existingForm && $existingForm->criteria_fields) {
            // Compare entire field structure (not just labels)
            $isActiveCustom = json_encode($customTemplate->criteria_fields) === json_encode($existingForm->criteria_fields);
        }
        $totalMaxCustom = array_sum(array_column($customTemplate->criteria_fields, 'max'));
        @endphp
        <div class="relative flex flex-col rounded-2xl border p-6 transition {{ $isActiveCustom ? 'border-emerald-300 bg-emerald-50/60 shadow-sm' : 'border-slate-200 bg-white hover:border-indigo-200 hover:shadow-sm' }}">
            @if($isActiveCustom)
            <span class="absolute -top-3 right-4 inline-flex items-center gap-1 rounded-full bg-emerald-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-white shadow-sm">
                Sedang digunakan
            </span>
            @endif
            <h3 class="flex flex-wrap items-center gap-2 text-base font-semibold text-slate-900">
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-700 ring-1 ring-amber-200">Custom</span>
                {{ $customTemplate->name }}
            </h3>
            @if($customTemplate->description)
            <p class="mt-1 text-sm text-slate-500">{{ $customTemplate->description }}</p>
            @endif
            <div class="mt-3 text-sm text-slate-600">
                <strong class="font-semibold text-slate-900">Kriteria:</strong>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($customTemplate->criteria_fields as $field)
                    <li>{{ $field['label'] }} <span class="text-slate-400">({{ $field['type'] }}{{ $field['type'] === 'score' ? ', max: ' . $field['max'] : '' }})</span></li>
                    @endforeach
                </ul>
                <div class="mt-3 rounded-lg bg-white/80 px-3 py-2 ring-1 ring-slate-200">
                    <strong class="font-semibold text-slate-900">Total Maksimal:</strong> {{ $totalMaxCustom }} poin
                </div>
            </div>
            <div class="mt-3 text-xs text-slate-500">
                Dibuat oleh: <strong class="font-semibold text-slate-700">{{ $customTemplate->creator->name }}</strong>
                <span class="px-1 text-slate-300">|</span> {{ $customTemplate->created_at->diffForHumans() }}
            </div>
            <div class="mt-5 flex gap-2">
                <form method="POST" action="{{ route('events.jury-form.custom-template', ['event' => $event->id, 'customTemplate' => $customTemplate->id]) }}" class="flex-1" id="customTemplateForm{{ $customTemplate->id }}">
                    @csrf
                    <button type="button" class="btn w-full {{ $isActiveCustom ? 'btn-secondary' : 'btn-success' }}"
                        {{ $isActiveCustom ? 'disabled' : '' }}
                        onclick="{{ $isActiveCustom ? '' : 'showConfirmModal(\'Gunakan Template?\', \'Template ini akan ditambahkan sebagai form baru. Form yang ada tidak diubah.\', () => document.getElementById(\'customTemplateForm' . $customTemplate->id . '\').submit())' }}">
                        {{ $isActiveCustom ? 'Template Aktif' : 'Gunakan Template' }}
                    </button>
                </form>
                <button type="button" class="btn btn-info w-[112px] shrink-0" onclick="editJuryTemplate({{ json_encode($customTemplate->criteria_fields) }}, '{{ addslashes($customTemplate->name) }}', {{ $customTemplate->id }})" title="Edit kriteria template">
                    Edit Kriteria
                </button>
            </div>
            <!-- Tombol Edit & Hapus di bagian bawah -->
            <div class="mt-4 flex gap-2 border-t border-slate-200 pt-4">
                <button type="button" class="btn btn-sm btn-warning flex-1" onclick="editCustomJuryTemplate({{ $customTemplate->id }}, '{{ addslashes($customTemplate->name) }}', '{{ addslashes($customTemplate->description ?? '') }}', {{ json_encode($customTemplate->criteria_fields) }}, {{ $customTemplate->use_default_score ? 'true' : 'false' }})" title="Edit nama, deskripsi, dan kriteria template">
                    Edit Data Template
                </button>
                <form method="POST" action="{{ route('custom-jury-templates.delete', $customTemplate->id) }}" class="flex-1" id="deleteTemplateForm{{ $customTemplate->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-danger w-full" title="Hapus Template" onclick="showConfirmModal('Hapus Template?', 'Template akan dihapus permanen.', () => document.getElementById('deleteTemplateForm{{ $customTemplate->id }}').submit())">
                        Hapus Template
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    // Edit Jury Template - redirect to custom builder with template data
    function editJuryTemplate(criteria, templateName, templateId = null) {
        localStorage.setItem('jury_template_preview_criteria', JSON.stringify(criteria));
        localStorage.setItem('editing_template', 'true'); // Flag untuk tidak auto-save sebagai template baru
        if (templateName) {
            localStorage.setItem('jury_template_preview_name', templateName);
        }
        if (templateId) {
            localStorage.setItem('editing_template_id', templateId); // Simpan ID template untuk update
        }
        window.location.href = "{{ route('events.jury-form.custom', $event) }}";
    }

    // Preview Jury Template Function
    function previewJuryTemplate(fields, templateName) {
        if (!fields || fields.length === 0) {
            alert('Template ini tidak memiliki kriteria untuk dipratinjau.');
            return;
        }

        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; overflow-y: auto; padding: 2rem;';
        
        let fieldsHTML = '';
        let totalMax = 0;
        
        fields.forEach((field, index) => {
            let inputHTML = '';
            
            if (field.type === 'score') {
                totalMax += parseInt(field.max || 0);
                inputHTML = `
                    <input type="number" class="form-control" 
                        min="0" max="${field.max}" 
                        placeholder="0 - ${field.max}"
                        ${field.required ? 'required' : ''}>
                    <small style="color: #64748b; display: block; margin-top: 0.25rem;">Maksimal: ${field.max} poin</small>
                `;
            } else if (field.type === 'textarea') {
                inputHTML = `<textarea class="form-control" rows="4" placeholder="Masukkan ${field.label.toLowerCase()}" ${field.required ? 'required' : ''}></textarea>`;
            } else if (field.type === 'select') {
                const options = field.options || [];
                inputHTML = `<select class="form-control" ${field.required ? 'required' : ''}>
                    <option value="">Pilih ${field.label}</option>
                    ${options.map(opt => `<option value="${opt}">${opt}</option>`).join('')}
                </select>`;
            } else {
                inputHTML = `<input type="${field.type}" class="form-control" placeholder="Masukkan ${field.label.toLowerCase()}" ${field.required ? 'required' : ''}>`;
            }
            
            fieldsHTML += `
                <div class="form-group" style="margin-bottom: 1.5rem; padding: 1rem; background: ${field.type === 'score' ? '#eef2ff' : '#f8fafc'}; border-radius: 10px; border-left: 4px solid ${field.type === 'score' ? '#4f46e5' : '#cbd5e1'};">
                    <label style="font-weight: 600; margin-bottom: 0.5rem; display: block; color: #0f172a;">
                        ${index + 1}. ${field.label}
                        ${field.required ? '<span style="color: #e11d48;">*</span>' : ''}
                        ${field.type === 'score' ? `<span style="float: right; background: #4f46e5; color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">${field.max} poin</span>` : ''}
                    </label>
                    ${inputHTML}
                </div>
            `;
        });

        modal.innerHTML = `
            <div style="background: #ffffff; padding: 2rem; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 20px 45px rgba(15,23,42,0.2); max-width: 700px; width: 90%; max-height: 80vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                    <h3 style="margin: 0; font-size: 1.125rem; font-weight: 600; color: #0f172a;">Preview: ${templateName}</h3>
                    <button type="button" onclick="this.closest('div[style*=fixed]').remove()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #64748b; cursor: pointer;">&times;</button>
                </div>
                <div style="background: #eef2ff; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; border-left: 4px solid #4f46e5;">
                    <p style="margin: 0; font-size: 0.875rem; color: #475569;">
                        <strong style="color: #3730a3;">Ini adalah preview template form penilaian juri.</strong><br>
                        Kriteria di bawah ini akan digunakan juri untuk menilai peserta jika template ini diterapkan.
                    </p>
                    ${totalMax > 0 ? `<div style="margin-top: 0.75rem; padding: 0.5rem; background: rgba(79,70,229,0.1); border-radius: 6px; text-align: center; color: #3730a3;">
                        <strong>Total Maksimal Nilai: ${totalMax} poin</strong>
                    </div>` : ''}
                </div>
                <form onsubmit="event.preventDefault(); alert('Ini hanya preview, penilaian tidak akan tersimpan.');">
                    ${fieldsHTML}
                    <div style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 10px; border-left: 4px solid #4f46e5;">
                        <strong style="display: block; margin-bottom: 0.5rem; color: #0f172a;">Total Nilai</strong>
                        <input type="text" class="form-control" value="0 / ${totalMax}" readonly style="font-weight: 600; font-size: 1.1rem; text-align: center;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem; width: 100%;">Simpan Penilaian (Preview)</button>
                </form>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    // Show success modal if redirected after save
    @if(session('show_success_modal'))
    window.addEventListener('DOMContentLoaded', function() {
        showSuccessModal();
    });
    @endif

    // Success Modal Function
    function showSuccessModal() {
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center; animation: fadeIn 0.3s;';
        modal.innerHTML = `
            <div style="background: #ffffff; border-radius: 16px; padding: 2.5rem; max-width: 450px; width: 90%; text-align: center; border: 1px solid #e2e8f0; box-shadow: 0 20px 45px rgba(15,23,42,0.25); animation: slideUp 0.3s;" onclick="event.stopPropagation()">
                <div style="width: 64px; height: 64px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width: 30px; height: 30px; color: #059669;" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                    </svg>
                </div>
                <h2 style="margin: 0 0 0.75rem 0; color: #0f172a; font-size: 1.5rem; font-weight: 600;">Berhasil Disimpan!</h2>
                <p style="margin: 0 0 2rem 0; color: #64748b; font-size: 0.9375rem; line-height: 1.6;">
                    Form penilaian juri telah berhasil disimpan.<br>
                    Perubahan sudah diterapkan ke event ini.
                </p>
                <button onclick="this.closest('div[style*=fixed]').remove()" class="btn-premium btn-premium-success" style="cursor: pointer;">
                    OK, Mengerti
                </button>
            </div>
        `;
        modal.onclick = function() {
            this.remove();
        };
        document.body.appendChild(modal);

        // Auto close after 5 seconds
        setTimeout(() => {
            if (modal.parentNode) {
                modal.remove();
                location.reload(); // Reload to refresh data
            }
        }, 5000);
    }

    // Preview Form Function
    function showPreview() {
        console.log('=== PREVIEW FUNCTION DEBUG ===');
        console.log('1. showPreview() called at: ' + new Date().toISOString());
        @if($existingForm)
        console.log('2. Existing form found, showing preview...');
        const fields = @json($existingForm->criteria_fields);
        console.log('3. Fields from database (timestamp: ' + Date.now() + '):', fields);
        console.log('4. Type of fields:', typeof fields);
        console.log('5. Is Array?', Array.isArray(fields));
        console.log('6. Fields length:', fields ? fields.length : 'null/undefined');

        // Log each field detail
        if (fields && Array.isArray(fields)) {
            fields.forEach((field, index) => {
                console.log(`   Field ${index + 1}:`, {
                    label: field.label,
                    type: field.type,
                    max: field.max,
                    maxType: typeof field.max,
                    required: field.required
                });
            });
        }

        const useDefaultScore = {{ $existingForm->use_default_score ? 'true' : 'false' }};
        console.log('7. useDefaultScore:', useDefaultScore);

        let totalMax = 0;
        console.log('8. Starting to build preview HTML...');
        let previewHTML = `
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 2rem;" onclick="this.remove()">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 2rem; max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 45px rgba(15,23,42,0.25);" onclick="event.stopPropagation()">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
                        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #0f172a;">Preview Form Penilaian Juri</h2>
                        <button onclick="this.closest('div[style*=fixed]').remove()" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.875rem; font-weight: 600;">Tutup</button>
                    </div>
                    
                    <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border-left: 4px solid #4f46e5;">
                        <h3 style="margin-top: 0; font-size: 1rem; font-weight: 600; color: #0f172a;">Kriteria Penilaian</h3>
                        <p style="color: #64748b; margin-bottom: 0;">Ini adalah preview form yang akan dilihat oleh juri</p>
        `;

        fields.forEach((field, index) => {
            console.log(`9. Processing field ${index + 1}:`, field);
            if (field.type === 'score') {
                console.log(`   -> Score field detected, max value: ${field.max}`);
                totalMax += field.max;
                console.log(`   -> Total max updated to: ${totalMax}`);
                previewHTML += `
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">
                            ${field.label}
                            ${field.required ? '<span style="color: #e11d48;">*</span>' : ''}
                            <span style="color: #4f46e5; font-weight: 600;">(Max: ${field.max} poin)</span>
                        </label>
                        ${field.description ? `<p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                        <input 
                            type="number" 
                            style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; background: #f8fafc;"
                            placeholder="0 - ${field.max}"
                            min="0"
                            max="${field.max}"
                            disabled
                        >
                    </div>
                `;
            } else if (field.type === 'textarea') {
                previewHTML += `
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">
                            ${field.label}
                            ${field.required ? '<span style="color: #e11d48;">*</span>' : ''}
                        </label>
                        ${field.description ? `<p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                        <textarea 
                            style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; background: #f8fafc;"
                            rows="3"
                            disabled
                        ></textarea>
                    </div>
                `;
            } else {
                previewHTML += `
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">
                            ${field.label}
                            ${field.required ? '<span style="color: #e11d48;">*</span>' : ''}
                        </label>
                        ${field.description ? `<p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                        <input 
                            type="text" 
                            style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; background: #f8fafc;"
                            disabled
                        >
                    </div>
                `;
            }
        });

        if (totalMax > 0) {
            console.log('10. Total max calculated:', totalMax);
            previewHTML += `
                <div style="background: #ecfdf5; padding: 1rem; border-radius: 10px; margin-top: 1rem; border: 1px solid #a7f3d0;">
                    <strong style="color: #047857; font-size: 1.125rem;">Total Maksimal Poin: ${totalMax}</strong>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #64748b;">
                        ${useDefaultScore ? 'Nilai akhir akan dihitung otomatis dari total kriteria penilaian' : 'Juri akan memasukkan nilai total manual'}
                    </p>
                </div>
            `;
        }

        previewHTML += `
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">Feedback Publik</label>
                        <textarea 
                            style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; background: #f8fafc;"
                            rows="4"
                            placeholder="Feedback yang dapat dilihat oleh peserta..."
                            disabled
                        ></textarea>
                        <small style="color: #64748b;">Feedback ini akan terlihat oleh peserta dan publik</small>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">Evaluasi Private</label>
                        <textarea 
                            style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; background: #f8fafc;"
                            rows="4"
                            placeholder="Evaluasi internal untuk admin..."
                            disabled
                        ></textarea>
                        <small style="color: #64748b;">Evaluasi ini hanya terlihat oleh admin dan juri</small>
                    </div>
                    
                    <div style="text-align: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                        <button onclick="this.closest('div[style*=fixed]').remove()" class="btn btn-primary" style="font-size: 1rem; padding: 0.75rem 2rem;">
                            Tutup Preview
                        </button>
                    </div>
                </div>
            </div>
        `;

        console.log('11. Preview HTML generated, inserting into DOM...');
        document.body.insertAdjacentHTML('beforeend', previewHTML);
        console.log('12. Preview modal inserted successfully');
        console.log('=== END PREVIEW FUNCTION DEBUG ===');
        @else
        alert('Tidak ada form penilaian untuk di-preview.\n\nSilakan buat form penilaian terlebih dahulu.');
        @endif
    }

    function editExistingJuryForm() {
        const existingFields = @json($existingForm ? $existingForm->criteria_fields : []);
        const useDefaultScore = @json($existingForm ? $existingForm->use_default_score : false);

        if (!existingFields || existingFields.length === 0) {
            alert('Tidak ada form penilaian yang bisa diedit.');
            return;
        }

        // Clear current fields
        const container = document.getElementById('criteriaContainer');
        if (!container) {
            alert('Form builder tidak ditemukan.');
            return;
        }
        container.innerHTML = '';
        criteriaIndex = 0;

        // Set auto-calculate checkbox
        const autoCalcCheckbox = document.getElementById('use_default_score');
        if (autoCalcCheckbox) {
            autoCalcCheckbox.checked = useDefaultScore;
        }

        // Load existing criteria into builder with Google Form style
        existingFields.forEach((field, index) => {
            const criteriaItem = document.createElement('div');
            criteriaItem.className = 'criteria-item';
            criteriaItem.setAttribute('data-index', index);
            criteriaItem.style.cssText = 'border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1rem; border-radius: 8px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: box-shadow 0.3s;';

            // Add hover effect
            criteriaItem.onmouseenter = function() {
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
            };
            criteriaItem.onmouseleave = function() {
                this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
            };

            criteriaItem.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem; margin-bottom: 1rem;">
                <div style="flex: 1;">
                    <input type="text" class="form-control criteria-label" value="${field.label || ''}" 
                           placeholder="Kriteria penilaian tanpa judul" 
                           style="font-size: 1rem; font-weight: 500; border: none; border-bottom: 1px solid #e2e8f0; border-radius: 0; padding: 0.5rem 0; background: transparent;"
                           onfocus="this.style.borderBottom='2px solid #4f46e5'" onblur="this.style.borderBottom='1px solid #e2e8f0'">
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="duplicateCriteria(this)" title="Duplikat" style="padding: 0.25rem 0.5rem;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H6zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1H2z"/>
                        </svg>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-criteria" title="Hapus" style="padding: 0.25rem 0.5rem;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                            <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <select class="form-control criteria-type" style="max-width: 180px; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <option value="score" ${field.type === 'score' ? 'selected' : ''}>Nilai Poin</option>
                    <option value="text" ${field.type === 'text' ? 'selected' : ''}>Text</option>
                    <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>Paragraf</option>
                </select>
                
                <div class="form-group max-score-field" style="display: ${field.type === 'score' ? 'block' : 'none'}; margin: 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label style="margin: 0; font-size: 0.875rem; color: #64748b; white-space: nowrap;">Max:</label>
                        <input type="number" class="form-control criteria-max" value="${field.max || 25}" min="1" max="100" 
                               style="width: 80px; border: 1px solid #e2e8f0; border-radius: 4px; padding: 0.375rem 0.5rem;">
                        <span style="font-size: 0.875rem; color: #64748b;">poin</span>
                    </div>
                </div>
                
                <div style="flex: 1;"></div>
                
                <label style="display: flex; align-items: center; gap: 0.5rem; margin: 0; cursor: pointer;">
                    <span style="font-size: 0.875rem; color: #64748b;">Wajib</span>
                    <input type="checkbox" class="criteria-required" ${field.required ? 'checked' : ''} 
                           style="width: 18px; height: 18px; cursor: pointer;">
                </label>
            </div>
        `;

            container.appendChild(criteriaItem);

            // Add event listener for type select
            const typeSelect = criteriaItem.querySelector('.criteria-type');
            const maxScoreField = criteriaItem.querySelector('.max-score-field');

            typeSelect.addEventListener('change', function() {
                if (this.value === 'score') {
                    maxScoreField.style.display = 'block';
                } else {
                    maxScoreField.style.display = 'none';
                }
            });

            criteriaIndex++;
        });

        // Scroll to form builder with smooth animation
        const formBuilder = document.getElementById('formBuilder');
        formBuilder.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // Highlight form builder
        formBuilder.style.backgroundColor = '#eef2ff';
        setTimeout(() => {
            formBuilder.style.transition = 'background-color 1s';
            formBuilder.style.backgroundColor = 'white';
        }, 1000);
    }

    // Function to duplicate criteria
    function duplicateCriteria(button) {
        const criteriaItem = button.closest('.criteria-item');
        const clone = criteriaItem.cloneNode(true);

        // Update data-index
        clone.setAttribute('data-index', criteriaIndex);
        criteriaIndex++;

        // Insert after current criteria
        criteriaItem.parentNode.insertBefore(clone, criteriaItem.nextSibling);

        // Reattach event listeners for the cloned criteria
        const typeSelect = clone.querySelector('.criteria-type');
        const maxScoreField = clone.querySelector('.max-score-field');

        typeSelect.addEventListener('change', function() {
            if (this.value === 'score') {
                maxScoreField.style.display = 'block';
            } else {
                maxScoreField.style.display = 'none';
            }
        });

        // Add hover effect to clone
        clone.onmouseenter = function() {
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
        };
        clone.onmouseleave = function() {
            this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        };
    }

    // Function to scroll to custom builder
    function scrollToCustomBuilder() {
        // Reset/clear the criteria builder
        const criteriaContainer = document.getElementById('criteriaContainer');
        if (criteriaContainer) {
            criteriaContainer.innerHTML = ''; // Clear all criteria
            criteriaIndex = 0; // Reset criteria index
        }

        const formBuilder = document.getElementById('formBuilder');
        formBuilder.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // Highlight form builder with blue background
        formBuilder.style.backgroundColor = '#eef2ff';
        setTimeout(() => {
            formBuilder.style.transition = 'background-color 1s';
            formBuilder.style.backgroundColor = 'white';
        }, 1000);
    }

    // Function to scroll to template selection
    function showTemplateSelection() {
        const templateSection = document.querySelector('h2');
        if (templateSection) {
            templateSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Highlight template cards area
            const cardGrid = templateSection.parentElement.querySelector('div[style*="grid"]');
            if (cardGrid) {
                cardGrid.style.backgroundColor = '#eef2ff';
                setTimeout(() => {
                    cardGrid.style.transition = 'background-color 1s';
                    cardGrid.style.backgroundColor = 'transparent';
                }, 1000);
            }
        }
    }

    // Function to show save jury template modal
    function showSaveJuryTemplateModal() {
        // Collect criteria data first
        const criteria = [];
        const criteriaItems = document.querySelectorAll('.criteria-item');

        criteriaItems.forEach(function(item) {
            const label = item.querySelector('.criteria-label').value;
            const type = item.querySelector('.criteria-type').value;
            const max = item.querySelector('.criteria-max').value;
            const required = item.querySelector('.criteria-required').checked;

            if (label.trim() === '') {
                return;
            }

            const criterion = {
                label: label,
                type: type,
                required: required
            };

            if (type === 'score') {
                criterion.max = parseInt(max) || 25;
            }

            criteria.push(criterion);
        });

        if (criteria.length === 0) {
            alert('Buat kriteria penilaian terlebih dahulu!');
            return;
        }

        const criteriaFields = JSON.stringify(criteria);
        const useDefaultScore = document.getElementById('use_default_score').checked;

        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        modal.innerHTML = `
        <div style="background: #ffffff; padding: 2rem; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 20px 45px rgba(15,23,42,0.2); max-width: 500px; width: 90%;">
            <h3 style="margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600; color: #0f172a;">Simpan Sebagai Template</h3>
            <form method="POST" action="{{ route('events.jury-form.save-template', $event->id) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Template *</label>
                    <input type="text" name="template_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="template_description" class="form-control" rows="3"></textarea>
                </div>
                <input type="hidden" name="criteria_fields" value='${criteriaFields}'>
                <input type="hidden" name="use_default_score" value="${useDefaultScore ? '1' : '0'}">
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Simpan Template</button>
                    <button type="button" class="btn btn-secondary" onclick="this.closest('div[style*=fixed]').remove()">Batal</button>
                </div>
            </form>
        </div>
    `;
        document.body.appendChild(modal);
    }

    // Function to edit custom jury template
    function editCustomJuryTemplate(id, name, description, fields, useDefaultScore) {
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        modal.innerHTML = `
        <div style="background: #ffffff; padding: 2rem; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 20px 45px rgba(15,23,42,0.2); max-width: 500px; width: 90%;">
            <h3 style="margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600; color: #0f172a;">Edit Template</h3>
            <form method="POST" action="/custom-jury-templates/${id}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Nama Template *</label>
                    <input type="text" name="name" class="form-control" value="${name}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">${description}</textarea>
                </div>
                <input type="hidden" name="criteria_fields" value='${JSON.stringify(fields)}'>
                <input type="hidden" name="use_default_score" value="${useDefaultScore ? '1' : '0'}">
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-warning">Update Template</button>
                    <button type="button" class="btn btn-secondary" onclick="this.closest('div[style*=fixed]').remove()">Batal</button>
                </div>
            </form>
        </div>
    `;
        document.body.appendChild(modal);
    }

    // Confirmation Modal Function
    function showConfirmModal(title, message, onConfirm) {
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 9999; animation: fadeIn 0.2s;';
        
        modal.innerHTML = `
            <div style="background: #ffffff; padding: 2rem; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 20px 45px rgba(15,23,42,0.25); max-width: 450px; width: 90%; animation: slideUp 0.3s;" onclick="event.stopPropagation()">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div style="width: 64px; height: 64px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 28px; height: 28px; color: #4f46e5;" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                        </svg>
                    </div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #0f172a; font-size: 1.25rem; font-weight: 600;">${title}</h3>
                    <p style="margin: 0; color: #64748b; font-size: 0.9375rem; line-height: 1.6;">${message}</p>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <button onclick="this.closest('div[style*=fixed]').remove()" class="btn btn-secondary" style="flex: 1;">
                        Batal
                    </button>
                    <button id="confirmBtn" class="btn-premium btn-premium-primary" style="flex: 1;">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Handle confirm
        modal.querySelector('#confirmBtn').addEventListener('click', () => {
            modal.remove();
            if (onConfirm) onConfirm();
        });
        
        // Close on outside click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    // Function to edit jury template form (load template criteria to builder)
    function editJuryTemplateForm(criteria) {
        // Clear existing criteria
        const criteriaContainer = document.getElementById('criteriaContainer');
        if (criteriaContainer) {
            criteriaContainer.innerHTML = '';
        }

        // Reset criteria index
        criteriaIndex = 0;

        // Load template criteria into the form builder
        criteria.forEach((field, index) => {
            const criteriaItem = document.createElement('div');
            criteriaItem.className = 'criteria-item';
            criteriaItem.setAttribute('data-index', index);
            criteriaItem.setAttribute('draggable', 'true');
            criteriaItem.style.cssText = 'border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 8px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.3s; cursor: move;';

            criteriaItem.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                    <span class="drag-handle" style="font-size: 1.2rem; color: #64748b; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                    <input type="text" class="form-control criteria-label" value="${field.label}" style="border: none; border-bottom: 1px solid #e2e8f0; font-size: 1.1rem; font-weight: 500; padding: 0.5rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text;" placeholder="Nama Kriteria" required>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="duplicateCriteria(this)" title="Duplikat Kriteria" aria-label="Duplikat Kriteria" style="padding: 0.35rem 0.5rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2M6 8h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" /></svg>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.criteria-item').remove()" title="Hapus Kriteria" aria-label="Hapus Kriteria" style="padding: 0.35rem 0.5rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" /></svg>
                    </button>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <select class="form-control criteria-type" style="background: #f1f5f9; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; color: #0f172a; cursor: pointer;">
                        <option value="score" ${field.type === 'score' ? 'selected' : ''}>Nilai Poin</option>
                        <option value="text" ${field.type === 'text' ? 'selected' : ''}>Teks Panjang</option>
                    </select>
                </div>
                <div class="max-score-field" style="display: ${field.type === 'score' ? 'flex' : 'none'}; align-items: center; gap: 0.5rem;">
                    <label style="margin: 0; font-size: 0.9rem; color: #64748b;">Max:</label>
                    <input type="number" class="form-control criteria-max" value="${field.max || 30}" min="1" max="100" style="border: 1px solid #e2e8f0; width: 80px; padding: 0.5rem; border-radius: 4px; text-align: center; font-size: 0.9rem;">
                    <span style="font-size: 0.9rem; color: #64748b;">poin</span>
                </div>
                <div style="margin-left: auto; display: flex; align-items: center;">
                    <label style="margin: 0; font-size: 0.9rem; color: #64748b; margin-right: 0.5rem;">Wajib</label>
                    <input type="checkbox" class="criteria-required" ${field.required ? 'checked' : ''} style="width: 20px; height: 20px; cursor: pointer; accent-color: #4f46e5;">
                </div>
            </div>
        `;

            criteriaContainer.appendChild(criteriaItem);

            // Add event listener for type change
            const typeSelect = criteriaItem.querySelector('.criteria-type');
            const maxScoreField = criteriaItem.querySelector('.max-score-field');

            typeSelect.addEventListener('change', function() {
                if (this.value === 'score') {
                    maxScoreField.style.display = 'flex';
                } else {
                    maxScoreField.style.display = 'none';
                }
            });

            // Add hover effect
            criteriaItem.onmouseenter = function() {
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                this.style.transform = 'translateY(-2px)';
            };
            criteriaItem.onmouseleave = function() {
                this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
                this.style.transform = 'translateY(0)';
            };

            // Enable drag and drop
            enableDragAndDrop(criteriaItem);

            criteriaIndex++;
        }); // Scroll to form builder
        const formBuilder = document.getElementById('formBuilder');
        if (formBuilder) {
            formBuilder.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Highlight form builder
            formBuilder.style.backgroundColor = '#eef2ff';
            setTimeout(() => {
                formBuilder.style.transition = 'background-color 1s';
                formBuilder.style.backgroundColor = 'white';
            }, 1000);
        }
    }

    // Add hover effects and drag & drop to existing criteria items on page load
    document.addEventListener('DOMContentLoaded', function() {
        const existingCriteria = document.querySelectorAll('.criteria-item');
        existingCriteria.forEach(item => {
            item.onmouseenter = function() {
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                this.style.transform = 'translateY(-2px)';
            };
            item.onmouseleave = function() {
                this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
                this.style.transform = 'translateY(0)';
            };

            // Enable drag and drop for existing items
            enableDragAndDrop(item);
        });
    });
</script>

<style>
    /* Modal Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Responsive Styles for Jury Forms */
    @media (max-width: 768px) {
        .card {
            padding: 1rem !important;
        }

        .criteria-item {
            flex-direction: column !important;
        }

        .criteria-item>div {
            width: 100% !important;
            margin-bottom: 0.5rem;
        }

        .criteria-item input[type="text"],
        .criteria-item select {
            width: 100% !important;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }

    @media (max-width: 480px) {
        h1 {
            font-size: 1.5rem !important;
        }

        h2 {
            font-size: 1.1rem !important;
        }

        .card {
            padding: 0.75rem !important;
        }

        .alert {
            padding: 0.75rem !important;
            font-size: 0.9rem !important;
        }

        .btn {
            padding: 0.4rem 0.8rem !important;
            font-size: 0.85rem !important;
        }
    }
</style>
@endpush
@endsection
