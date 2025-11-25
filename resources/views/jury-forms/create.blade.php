@extends('layouts.app')

@section('title', 'Kelola Form Penilaian Juri')

@section('content')

<!-- Success Modal -->
@if(session('template_success'))
<div id="successModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; animation: fadeIn 0.3s;">
    <div style="background: white; padding: 3rem; border-radius: 20px; text-align: center; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: slideUp 0.3s;">
        <div style="width: 80px; height: 80px; background: #2563eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <span style="font-size: 3rem; animation: checkPulse 0.6s;">✓</span>
        </div>
        <h2 style="font-size: 1.75rem; font-weight: bold; color: #2c3e50; margin-bottom: 1rem;">Berhasil!</h2>
        <p style="color: #666; font-size: 1.1rem; margin-bottom: 2rem;">{{ session('template_success') }}</p>
        <div style="display: inline-block; padding: 0.5rem 1rem; background: #ede7f6; border-radius: 8px; color: #667eea; font-size: 0.9rem;">
            <div class="spinner" style="display: inline-block; width: 12px; height: 12px; border: 2px solid #667eea; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; margin-right: 0.5rem;"></div>
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

<div class="card">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('events.show', $event) }}" style="color: #3498db; text-decoration: none;">
            ← Kembali ke Detail Event
        </a>
    </div>

    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">Kelola Form Penilaian Juri</h1>
    <h2 style="font-size: 1.25rem; color: #666; margin-bottom: 1.5rem;">{{ $event->name }}</h2>

    @if(session('success'))
    <div class="alert alert-success" style="background: #d4edda; border-left: 4px solid #28a745; padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px;">
        {{ session('success') }}
    </div>
    @endif

    @php
        $existingFormsCount = $event->juryFormTemplates()->count();
    @endphp

    @if($existingFormsCount > 0)
    <div style="background: #e8f5e9; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #27ae60;">
        <strong style="color: #27ae60;">✅ Form yang sudah dibuat ({{ $existingFormsCount }}):</strong>
        <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @foreach($event->juryFormTemplates as $form)
            <span style="background: white; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; color: #2c3e50; border: 1px solid #27ae60;">
                {{ $form->name ?? 'Form Juri' }}
            </span>
            @endforeach
        </div>
        <p style="margin: 0.75rem 0 0 0; color: #2c3e50; font-size: 0.9rem;">
            💡 <strong>Tip:</strong> Anda bisa membuat form tambahan untuk juri yang berbeda. Contoh: Form Juri Vokal, Form Juri Adab, Form Juri Terbang, dll.
        </p>
    </div>
    @endif

    <!-- Custom Form Builder Button -->
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('events.jury-form.custom', $event) }}" class="btn-premium btn-premium-primary" style="padding: 0.75rem 1.5rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; border-radius: 8px;">
            <span style="font-size: 1.5rem;">🎨</span>
            <span><strong>{{ $existingFormsCount > 0 ? 'Buat Form Penilaian Baru' : 'Buat Form Penilaian Custom' }}</strong></span>
        </a>
        <p style="color: #666; margin-top: 0.5rem; font-size: 0.9rem;">
            💡 <em>{{ $existingFormsCount > 0 ? 'Tambahkan form penilaian baru untuk juri yang berbeda' : 'Buat kriteria penilaian sesuai kebutuhan event Anda dengan form builder yang mudah' }}</em>
        </p>
    </div>

    @if($existingForm)
    <div class="alert alert-success">
        ✅ Event ini sudah memiliki form penilaian custom untuk juri
        <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
            <a href="{{ route('events.jury-form.custom', $event) }}" class="btn btn-warning" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center;">
                ✏️ Edit Form
            </a>
            <button type="button" class="btn btn-primary" onclick="showPreview()" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                👁️ Preview Form
            </button>
        </div>
    </div>


    @else
    <div class="alert alert-warning">
        ⚠️ Event ini menggunakan form penilaian standar (Nilai 0-100 + Feedback)
    </div>
    @endif
</div>

<!-- Template Form -->
<div class="card">
    <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">📋 Pilih Template Form Penilaian</h2>
    <p style="color: #666; margin-bottom: 1.5rem;">Gunakan template yang sudah tersedia untuk berbagai jenis kompetisi</p>

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

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
        <!-- Custom Form Card -->
        <div style="border: 2px solid {{ $isCustomForm ? '#2196f3' : '#ddd' }}; border-radius: 8px; padding: 1.5rem; background: {{ $isCustomForm ? '#e3f2fd' : '#f8f9fa' }}; position: relative;">
            @if($isCustomForm)
            <div style="position: absolute; top: -12px; right: 10px; background: #2196f3; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: bold;">
                ✓ SEDANG DIGUNAKAN
            </div>
            @endif
            <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.75rem;">🎨 Form Penilaian Custom</h3>
            <div style="margin-bottom: 1rem;">
                <strong>Fitur:</strong>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem; font-size: 0.875rem;">
                    <li>Buat kriteria sesuai kebutuhan</li>
                    <li>Tambah/hapus kriteria sesuka hati</li>
                    <li>Atur nilai maksimal per kriteria</li>
                    <li>Edit dengan Google Form style</li>
                </ul>
                @if($isCustomForm && $existingForm)
                @php
                $totalMaxCustom = array_sum(array_column($existingForm->criteria_fields, 'max'));
                @endphp
                <div style="margin-top: 0.5rem; padding: 0.5rem; background: #bbdefb; border-radius: 5px; font-size: 0.875rem;">
                    <strong>Total Kriteria:</strong> {{ count($existingForm->criteria_fields) }} kriteria<br>
                    <strong>Total Maksimal:</strong> {{ $totalMaxCustom }} poin
                </div>
                @endif
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @if($isCustomForm)
                <button type="button" class="btn btn-primary" style="width: 100%;" disabled>
                    ✓ Custom Form Aktif
                </button>
                <button type="button" class="btn btn-outline-secondary" style="width: 100%;" onclick="showTemplateSelection()">
                    📋 Gunakan Template
                </button>
                @else
                <a href="{{ route('events.jury-form.custom', $event) }}" class="btn btn-primary" style="width: 100%; text-decoration: none; display: inline-block; text-align: center;">
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
        <div style="border: 2px solid {{ $isActive ? '#28a745' : '#ddd' }}; border-radius: 8px; padding: 1.5rem; background: {{ $isActive ? '#d4edda' : '#f8f9fa' }}; position: relative;">
            @if($isActive)
            <div style="position: absolute; top: -12px; right: 10px; background: #28a745; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: bold;">
                ✓ SEDANG DIGUNAKAN
            </div>
            @endif
            <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.75rem;">{{ $template['name'] }}</h3>
            <div style="margin-bottom: 1rem;">
                <strong>Kriteria Penilaian:</strong>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem; font-size: 0.875rem;">
                    @foreach($template['fields'] as $field)
                    @php $totalMax += $field['max']; @endphp
                    <li>{{ $field['label'] }} ({{ $field['max'] }} poin) @if($field['required'])<span style="color: red;">*</span>@endif</li>
                    @endforeach
                </ul>
                <div style="margin-top: 0.5rem; padding: 0.5rem; background: #d1ecf1; border-radius: 5px; font-size: 0.875rem;">
                    <strong>Total Maksimal:</strong> {{ $totalMax }} poin
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <form method="POST" action="{{ route('events.jury-form.template', [$event, $key]) }}" style="flex: 1;" id="templateForm{{ $key }}">
                    @csrf
                    <button type="button" class="btn btn-primary" style="width: 100%;"
                        {{ $isActive ? 'disabled' : '' }}
                        onclick="{{ $isActive ? '' : 'showConfirmModal(\'Gunakan Template?\', \'Form penilaian yang ada akan diganti dengan template ini.\', () => document.getElementById(\'templateForm' . $key . '\').submit())' }}">
                        {{ $isActive ? '✓ Template Aktif' : 'Gunakan Template' }}
                    </button>
                </form>
                <button type="button" class="btn btn-info" style="width: 110px;" onclick="editJuryTemplate({{ json_encode($template['fields']) }}, '{{ $template['name'] }}')" title="Edit kriteria template">
                    ✏️ Edit Kriteria
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
        <div style="border: 2px solid {{ $isActiveCustom ? '#28a745' : '#ddd' }}; border-radius: 8px; padding: 1.5rem; background: {{ $isActiveCustom ? '#d4edda' : '#fff' }}; position: relative;">
            @if($isActiveCustom)
            <div style="position: absolute; top: -12px; right: 10px; background: #28a745; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: bold;">
                ✓ SEDANG DIGUNAKAN
            </div>
            @endif
            <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.75rem;">
                <span style="background: #ffc107; color: #000; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.75rem; margin-right: 0.5rem;">CUSTOM</span>
                {{ $customTemplate->name }}
            </h3>
            @if($customTemplate->description)
            <p style="color: #666; margin-bottom: 1rem; font-size: 0.875rem;">{{ $customTemplate->description }}</p>
            @endif
            <div style="margin-bottom: 1rem;">
                <strong>Kriteria:</strong>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem; font-size: 0.875rem;">
                    @foreach($customTemplate->criteria_fields as $field)
                    <li>{{ $field['label'] }} ({{ $field['type'] }}{{ $field['type'] === 'score' ? ', max: ' . $field['max'] : '' }})</li>
                    @endforeach
                </ul>
                <div style="margin-top: 0.5rem;">
                    <strong>Total Maksimal:</strong> {{ $totalMaxCustom }} poin
                </div>
            </div>
            <div style="font-size: 0.75rem; color: #666; margin-bottom: 1rem;">
                Dibuat oleh: <strong>{{ $customTemplate->creator->name }}</strong> • {{ $customTemplate->created_at->diffForHumans() }}
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <form method="POST" action="{{ route('events.jury-form.custom-template', ['event' => $event->id, 'customTemplate' => $customTemplate->id]) }}" style="flex: 1;" id="customTemplateForm{{ $customTemplate->id }}">
                    @csrf
                    <button type="button" class="btn {{ $isActiveCustom ? 'btn-secondary' : 'btn-success' }}" style="width: 100%;"
                        {{ $isActiveCustom ? 'disabled' : '' }}
                        onclick="{{ $isActiveCustom ? '' : 'showConfirmModal(\'Gunakan Template?\', \'Form penilaian yang ada akan diganti dengan template ini.\', () => document.getElementById(\'customTemplateForm' . $customTemplate->id . '\').submit())' }}">
                        {{ $isActiveCustom ? '✓ Template Aktif' : 'Gunakan Template' }}
                    </button>
                </form>
                <button type="button" class="btn btn-info" style="width: 110px;" onclick="editJuryTemplate({{ json_encode($customTemplate->criteria_fields) }}, '{{ addslashes($customTemplate->name) }}', {{ $customTemplate->id }})" title="Edit kriteria template">
                    ✏️ Edit Kriteria
                </button>
            </div>
            <!-- Tombol Edit & Hapus di bagian bawah -->
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #ddd;">
                <button type="button" class="btn btn-sm btn-warning" style="flex: 1;" onclick="editCustomJuryTemplate({{ $customTemplate->id }}, '{{ addslashes($customTemplate->name) }}', '{{ addslashes($customTemplate->description ?? '') }}', {{ json_encode($customTemplate->criteria_fields) }}, {{ $customTemplate->use_default_score ? 'true' : 'false' }})" title="Edit nama, deskripsi, dan kriteria template">
                    📝 Edit Data Template
                </button>
                <form method="POST" action="{{ route('custom-jury-templates.delete', $customTemplate->id) }}" style="flex: 1;" id="deleteTemplateForm{{ $customTemplate->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-danger" style="width: 100%;" title="Hapus Template" onclick="showConfirmModal('Hapus Template?', 'Template akan dihapus permanen.', () => document.getElementById('deleteTemplateForm{{ $customTemplate->id }}').submit())">
                        🗑️ Hapus Template
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
            alert('❌ Template ini tidak memiliki kriteria untuk dipratinjau.');
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
                    <small style="color: #666; display: block; margin-top: 0.25rem;">Maksimal: ${field.max} poin</small>
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
                <div class="form-group" style="margin-bottom: 1.5rem; padding: 1rem; background: ${field.type === 'score' ? '#f0f8ff' : '#f8f9fa'}; border-radius: 8px; border-left: 4px solid ${field.type === 'score' ? '#667eea' : '#6c757d'};">
                    <label style="font-weight: bold; margin-bottom: 0.5rem; display: block; color: #333;">
                        ${index + 1}. ${field.label}
                        ${field.required ? '<span style="color: red;">*</span>' : ''}
                        ${field.type === 'score' ? `<span style="float: right; background: #667eea; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">${field.max} poin</span>` : ''}
                    </label>
                    ${inputHTML}
                </div>
            `;
        });

        modal.innerHTML = `
            <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 700px; width: 90%; max-height: 80vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0;">👁️ Preview: ${templateName}</h3>
                    <button type="button" onclick="this.closest('div[style*=fixed]').remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
                </div>
                <div style="background: #2563eb; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: white;">
                    <p style="margin: 0; font-size: 0.875rem;">
                        <strong>ℹ️ Ini adalah preview template form penilaian juri.</strong><br>
                        Kriteria di bawah ini akan digunakan juri untuk menilai peserta jika template ini diterapkan.
                    </p>
                    ${totalMax > 0 ? `<div style="margin-top: 0.75rem; padding: 0.5rem; background: rgba(255,255,255,0.2); border-radius: 5px; text-align: center;">
                        <strong>Total Maksimal Nilai: ${totalMax} poin</strong>
                    </div>` : ''}
                </div>
                <form onsubmit="event.preventDefault(); alert('Ini hanya preview, penilaian tidak akan tersimpan.');">
                    ${fieldsHTML}
                    <div style="margin-top: 1.5rem; padding: 1rem; background: #e7f3ff; border-radius: 8px; border-left: 4px solid #0d6efd;">
                        <strong style="display: block; margin-bottom: 0.5rem;">Total Nilai</strong>
                        <input type="text" class="form-control" value="0 / ${totalMax}" readonly style="font-weight: bold; font-size: 1.1rem; text-align: center;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem; width: 100%; padding: 0.75rem;">Simpan Penilaian (Preview)</button>
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
            <div style="background: white; border-radius: 16px; padding: 2.5rem; max-width: 450px; width: 90%; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: slideUp 0.3s;" onclick="event.stopPropagation()">
                <div style="width: 80px; height: 80px; background: #16a34a; border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                    <span style="font-size: 3rem; color: white;">✓</span>
                </div>
                <h2 style="margin: 0 0 1rem 0; color: #333; font-size: 1.75rem; font-weight: bold;">Berhasil Disimpan!</h2>
                <p style="margin: 0 0 2rem 0; color: #666; font-size: 1.05rem; line-height: 1.6;">
                    Form penilaian juri telah berhasil disimpan.<br>
                    Perubahan sudah diterapkan ke event ini.
                </p>
                <button onclick="this.closest('div[style*=fixed]').remove()" class="btn-premium btn-premium-success" style="padding: 0.875rem 2.5rem; border-radius: 8px; font-size: 1.05rem; border: none; cursor: pointer;">
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
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 2rem;" onclick="this.remove()">
                <div style="background: white; border-radius: 8px; padding: 2rem; max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation()">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 2px solid #3498db; padding-bottom: 1rem;">
                        <h2 style="margin: 0; color: #3498db;">👁️ Preview Form Penilaian Juri</h2>
                        <button onclick="this.closest('div[style*=fixed]').remove()" style="background: #e74c3c; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 1rem;">✕ Tutup</button>
                    </div>
                    
                    <div style="background: #e8f5e9; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #27ae60;">
                        <h3 style="margin-top: 0; color: #27ae60;">📊 Kriteria Penilaian</h3>
                        <p style="color: #666; margin-bottom: 1rem;">Ini adalah preview form yang akan dilihat oleh juri</p>
        `;

        fields.forEach((field, index) => {
            console.log(`9. Processing field ${index + 1}:`, field);
            if (field.type === 'score') {
                console.log(`   -> Score field detected, max value: ${field.max}`);
                totalMax += field.max;
                console.log(`   -> Total max updated to: ${totalMax}`);
                previewHTML += `
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #ddd;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                            ${field.label}
                            ${field.required ? '<span style="color: red;">*</span>' : ''}
                            <span style="color: #27ae60; font-weight: bold;">(Max: ${field.max} poin)</span>
                        </label>
                        ${field.description ? `<p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                        <input 
                            type="number" 
                            style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                            placeholder="0 - ${field.max}"
                            min="0"
                            max="${field.max}"
                            disabled
                        >
                    </div>
                `;
            } else if (field.type === 'textarea') {
                previewHTML += `
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #ddd;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                            ${field.label}
                            ${field.required ? '<span style="color: red;">*</span>' : ''}
                        </label>
                        ${field.description ? `<p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                        <textarea 
                            style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                            rows="3"
                            disabled
                        ></textarea>
                    </div>
                `;
            } else {
                previewHTML += `
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #ddd;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                            ${field.label}
                            ${field.required ? '<span style="color: red;">*</span>' : ''}
                        </label>
                        ${field.description ? `<p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                        <input 
                            type="text" 
                            style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                            disabled
                        >
                    </div>
                `;
            }
        });

        if (totalMax > 0) {
            console.log('10. Total max calculated:', totalMax);
            previewHTML += `
                <div style="background: white; padding: 1rem; border-radius: 5px; margin-top: 1rem; border: 2px solid #27ae60;">
                    <strong style="color: #27ae60; font-size: 1.125rem;">Total Maksimal Poin: ${totalMax}</strong>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #666;">
                        ${useDefaultScore ? '✓ Nilai akhir akan dihitung otomatis dari total kriteria penilaian' : 'Juri akan memasukkan nilai total manual'}
                    </p>
                </div>
            `;
        }

        previewHTML += `
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Feedback Publik</label>
                        <textarea 
                            style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                            rows="4"
                            placeholder="Feedback yang dapat dilihat oleh peserta..."
                            disabled
                        ></textarea>
                        <small style="color: #666;">Feedback ini akan terlihat oleh peserta dan publik</small>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Evaluasi Private</label>
                        <textarea 
                            style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                            rows="4"
                            placeholder="Evaluasi internal untuk organizer dan admin..."
                            disabled
                        ></textarea>
                        <small style="color: #666;">Evaluasi ini hanya terlihat oleh organizer, admin, dan juri</small>
                    </div>
                    
                    <div style="text-align: center; padding-top: 1rem; border-top: 2px solid #ddd;">
                        <button onclick="this.closest('div[style*=fixed]').remove()" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">
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
        alert('❌ Tidak ada form penilaian untuk di-preview.\n\nSilakan buat form penilaian terlebih dahulu.');
        @endif
    }

    function editExistingJuryForm() {
        const existingFields = @json($existingForm ? $existingForm->criteria_fields : []);
        const useDefaultScore = @json($existingForm ? $existingForm->use_default_score : false);

        if (!existingFields || existingFields.length === 0) {
            alert('❌ Tidak ada form penilaian yang bisa diedit.');
            return;
        }

        // Clear current fields
        const container = document.getElementById('criteriaContainer');
        if (!container) {
            alert('❌ Form builder tidak ditemukan.');
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
            criteriaItem.style.cssText = 'border: 1px solid #dadce0; padding: 1.5rem; margin-bottom: 1rem; border-radius: 8px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: box-shadow 0.3s;';

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
                           style="font-size: 1rem; font-weight: 500; border: none; border-bottom: 1px solid #dadce0; border-radius: 0; padding: 0.5rem 0; background: transparent;"
                           onfocus="this.style.borderBottom='2px solid #1a73e8'" onblur="this.style.borderBottom='1px solid #dadce0'">
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
                <select class="form-control criteria-type" style="max-width: 180px; border: 1px solid #dadce0; border-radius: 4px;">
                    <option value="score" ${field.type === 'score' ? 'selected' : ''}>📊 Nilai Poin</option>
                    <option value="text" ${field.type === 'text' ? 'selected' : ''}>📝 Text</option>
                    <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>📄 Paragraf</option>
                </select>
                
                <div class="form-group max-score-field" style="display: ${field.type === 'score' ? 'block' : 'none'}; margin: 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label style="margin: 0; font-size: 0.875rem; color: #5f6368; white-space: nowrap;">Max:</label>
                        <input type="number" class="form-control criteria-max" value="${field.max || 25}" min="1" max="100" 
                               style="width: 80px; border: 1px solid #dadce0; border-radius: 4px; padding: 0.375rem 0.5rem;">
                        <span style="font-size: 0.875rem; color: #5f6368;">poin</span>
                    </div>
                </div>
                
                <div style="flex: 1;"></div>
                
                <label style="display: flex; align-items: center; gap: 0.5rem; margin: 0; cursor: pointer;">
                    <span style="font-size: 0.875rem; color: #5f6368;">Wajib</span>
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
        formBuilder.style.backgroundColor = '#e8f0fe';
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
        formBuilder.style.backgroundColor = '#e3f2fd';
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
                cardGrid.style.backgroundColor = '#f0f8ff';
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
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
            <h3 style="margin-bottom: 1rem;">💾 Simpan Sebagai Template</h3>
            <form method="POST" action="{{ route('events.jury-form.save-template', $event->id) }}">
                @csrf
                <div class="form-group">
                    <label>Nama Template *</label>
                    <input type="text" name="template_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
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
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
            <h3 style="margin-bottom: 1rem;">✏️ Edit Template</h3>
            <form method="POST" action="/custom-jury-templates/${id}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nama Template *</label>
                    <input type="text" name="name" class="form-control" value="${name}" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
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
            <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 450px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: slideUp 0.3s;" onclick="event.stopPropagation()">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div style="width: 70px; height: 70px; background: #2563eb; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                        <span style="font-size: 2.5rem; color: white;">❓</span>
                    </div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #333; font-size: 1.5rem; font-weight: bold;">${title}</h3>
                    <p style="margin: 0; color: #666; font-size: 1rem; line-height: 1.5;">${message}</p>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <button onclick="this.closest('div[style*=fixed]').remove()" class="btn btn-secondary" style="flex: 1; padding: 0.75rem; border-radius: 8px; font-size: 1rem;">
                        ❌ Batal
                    </button>
                    <button id="confirmBtn" class="btn-premium btn-premium-primary" style="flex: 1; padding: 0.75rem; border-radius: 8px; font-size: 1rem; border: none;">
                        ✅ Ya, Lanjutkan
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
            criteriaItem.style.cssText = 'border: 1px solid #dadce0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 8px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.3s; cursor: move;';

            criteriaItem.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                    <span class="drag-handle" style="font-size: 1.2rem; color: #5f6368; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                    <input type="text" class="form-control criteria-label" value="${field.label}" style="border: none; border-bottom: 1px solid #dadce0; font-size: 1.1rem; font-weight: 500; padding: 0.5rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text;" placeholder="Nama Kriteria" required>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-sm" onclick="duplicateCriteria(this)" title="Duplikat Kriteria" style="background: transparent; border: none; font-size: 1.2rem; padding: 0.25rem 0.5rem; cursor: pointer;">📋</button>
                    <button type="button" class="btn btn-sm" onclick="this.closest('.criteria-item').remove()" title="Hapus Kriteria" style="background: transparent; border: none; color: #dc3545; font-size: 1.2rem; padding: 0.25rem 0.5rem; cursor: pointer;">🗑️</button>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.9rem; color: #5f6368;">📊</span>
                    <select class="form-control criteria-type" style="border: none; background: #f1f3f4; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.9rem; color: #202124; cursor: pointer;">
                        <option value="score" ${field.type === 'score' ? 'selected' : ''}>Nilai Poin</option>
                        <option value="text" ${field.type === 'text' ? 'selected' : ''}>Teks Panjang</option>
                    </select>
                </div>
                <div class="max-score-field" style="display: ${field.type === 'score' ? 'flex' : 'none'}; align-items: center; gap: 0.5rem;">
                    <label style="margin: 0; font-size: 0.9rem; color: #5f6368;">Max:</label>
                    <input type="number" class="form-control criteria-max" value="${field.max || 30}" min="1" max="100" style="border: 1px solid #dadce0; width: 80px; padding: 0.5rem; border-radius: 4px; text-align: center; font-size: 0.9rem;">
                    <span style="font-size: 0.9rem; color: #5f6368;">poin</span>
                </div>
                <div style="margin-left: auto; display: flex; align-items: center;">
                    <label style="margin: 0; font-size: 0.9rem; color: #5f6368; margin-right: 0.5rem;">Wajib</label>
                    <input type="checkbox" class="criteria-required" ${field.required ? 'checked' : ''} style="width: 20px; height: 20px; cursor: pointer; accent-color: #1a73e8;">
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
            formBuilder.style.backgroundColor = '#e8f0fe';
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
