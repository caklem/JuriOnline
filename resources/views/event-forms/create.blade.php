@extends('layouts.app')

@section('title', 'Kelola Form Pendaftaran')

@section('content')

<!-- Success Modal -->
@if(session('template_success'))
<div id="successModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; animation: fadeIn 0.3s;">
    <div style="background: white; padding: 3rem; border-radius: 20px; text-align: center; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: slideUp 0.3s;">
        <div style="width: 80px; height: 80px; background: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <span style="font-size: 3rem; animation: checkPulse 0.6s;">✓</span>
        </div>
        <h2 style="font-size: 1.75rem; font-weight: bold; color: #2c3e50; margin-bottom: 1rem;">Berhasil!</h2>
        <p style="color: #666; font-size: 1.1rem; margin-bottom: 2rem;">{{ session('template_success') }}</p>
        <div style="display: inline-block; padding: 0.5rem 1rem; background: #e8f5e9; border-radius: 8px; color: #4caf50; font-size: 0.9rem;">
            <div class="spinner" style="display: inline-block; width: 12px; height: 12px; border: 2px solid #4caf50; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; margin-right: 0.5rem;"></div>
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

    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">Kelola Form Pendaftaran</h1>
    <h2 style="font-size: 1.25rem; color: #666; margin-bottom: 1.5rem;">{{ $event->name }}</h2>

    @if($existingForm)
    <div class="alert alert-success">
        ✅ Event ini sudah memiliki form pendaftaran
        <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
            <a href="{{ route('events.form.custom', $event) }}" class="btn btn-warning" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-decoration: none;">
                ✏️ Edit Form Custom
            </a>
            <button type="button" class="btn btn-primary" onclick="showRegistrationPreview()" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                👁️ Preview Form
            </button>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        ⚠️ Event ini belum memiliki form pendaftaran. Peserta hanya perlu data dasar saja.
    </div>
    @endif
</div>

<!-- Template Form -->
<div class="card">
    <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">📋 Pilih Template Form</h2>
    <p style="color: #666; margin-bottom: 1.5rem;">Gunakan template yang sudah tersedia untuk mempermudah pembuatan form</p>

    @php
    // Check if using custom form (not matching any template, including custom templates)
    $isCustomForm = false;
    if ($existingForm && $existingForm->form_fields) {
        $isCustomForm = true;
        
        // Check against standard templates
        foreach($templates as $key => $template) {
            if (json_encode($template['fields']) === json_encode($existingForm->form_fields)) {
                $isCustomForm = false;
                break;
            }
        }
        
        // Also check against custom templates
        if ($isCustomForm) {
            foreach($customTemplates as $customTemplate) {
                if (json_encode($customTemplate->form_fields) === json_encode($existingForm->form_fields)) {
                    $isCustomForm = false;
                    break;
                }
            }
        }
    }
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
        <!-- Custom Form Card -->
        <div style="border: 2px solid {{ $isCustomForm ? '#2196f3' : '#ddd' }}; border-radius: 8px; padding: 1.5rem; background: {{ $isCustomForm ? '#e3f2fd' : '#f8f9fa' }}; position: relative;">
            @if($isCustomForm)
            <div style="position: absolute; top: -12px; right: 10px; background: #2196f3; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: bold;">
                ✓ SEDANG DIGUNAKAN
            </div>
            @endif
            <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.75rem;">🎨 Form Custom</h3>
            <div style="margin-bottom: 1rem;">
                <strong>Fitur:</strong>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem; font-size: 0.875rem;">
                    <li>Buat form sesuai kebutuhan Anda</li>
                    <li>Tambah/hapus field sesuka hati</li>
                    <li>Duplikasi field dengan mudah</li>
                    <li>Edit dengan Google Form style</li>
                </ul>
                @if($isCustomForm && $existingForm)
                <div style="margin-top: 0.5rem; padding: 0.5rem; background: #bbdefb; border-radius: 5px; font-size: 0.875rem;">
                    <strong>Total Field:</strong> {{ count($existingForm->form_fields) }} field
                </div>
                @endif
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @if($isCustomForm)
                <a href="{{ route('events.form.custom', $event) }}" class="btn btn-success" style="width: 100%; text-decoration: none; display: inline-block; text-align: center;">
                    ✏️ Edit Custom Form
                </a>
                <button type="button" class="btn btn-outline-secondary" style="width: 100%;" onclick="showTemplateSelection()">
                    📋 Gunakan Template
                </button>
                @else
                <a href="{{ route('events.form.custom', $event) }}" class="btn btn-primary" style="width: 100%; text-decoration: none; display: inline-block; text-align: center;">
                    Buat Form Custom
                </a>
                @endif
            </div>
        </div>

        @foreach($templates as $key => $template)
        @php
        // Check if this template is currently being used
        $isActive = false;
        if ($existingForm && $existingForm->form_fields) {
            // Compare entire field structure (not just labels)
            $isActive = json_encode($template['fields']) === json_encode($existingForm->form_fields);
        }
        @endphp
        <div style="border: 2px solid {{ $isActive ? '#28a745' : '#ddd' }}; border-radius: 8px; padding: 1.5rem; background: {{ $isActive ? '#d4edda' : '#f8f9fa' }}; position: relative;">
            @if($isActive)
            <div style="position: absolute; top: -12px; right: 10px; background: #28a745; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: bold;">
                ✓ SEDANG DIGUNAKAN
            </div>
            @endif
            <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.75rem;">{{ $template['name'] }}</h3>
            @if(isset($template['description']))
            <p style="color: #666; margin-bottom: 1rem; font-size: 0.875rem;">{{ $template['description'] }}</p>
            @endif
            <div style="margin-bottom: 1rem;">
                <strong>Field:</strong>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem; font-size: 0.875rem;">
                    @foreach($template['fields'] as $field)
                    <li>{{ $field['label'] }} ({{ $field['type'] }})</li>
                    @endforeach
                </ul>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <form method="POST" action="{{ route('events.form.template', ['event' => $event->id, 'template' => $key]) }}" style="flex: 1;" id="eventTemplateForm{{ $key }}">
                    @csrf
                    <button type="button" class="btn {{ $isActive ? 'btn-secondary' : 'btn-primary' }}" style="width: 100%;"
                        @if($isActive) disabled @endif
                        @if(!$isActive) onclick="showConfirmModal('Gunakan Template?', 'Form pendaftaran yang ada akan diganti dengan template ini.', () => document.getElementById('eventTemplateForm{{ $key }}').submit())" @endif>
                        {{ $isActive ? '✓ Template Aktif' : 'Gunakan Template' }}
                    </button>
                </form>
                <button type="button" class="btn btn-info" style="width: 100px;"
                    onclick="editTemplateForm({{ json_encode($template['fields']) }}, '{{ $template['name'] }}')"
                    title="Edit field template">
                    ✏️ Edit Field
                </button>
            </div>
        </div>
        @endforeach

        @foreach($customTemplates as $customTemplate)
        @php
        // Check if this custom template is currently being used
        $isActiveCustom = false;
        if ($existingForm && $existingForm->form_fields) {
            // Compare entire field structure (not just labels)
            $isActiveCustom = json_encode($customTemplate->form_fields) === json_encode($existingForm->form_fields);
        }
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
                <strong>Field:</strong>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem; font-size: 0.875rem;">
                    @foreach($customTemplate->form_fields as $field)
                    <li>{{ $field['label'] }} ({{ $field['type'] }})</li>
                    @endforeach
                </ul>
            </div>
            <div style="font-size: 0.75rem; color: #666; margin-bottom: 1rem;">
                Dibuat oleh: <strong>{{ $customTemplate->creator->name }}</strong> • {{ $customTemplate->created_at->diffForHumans() }}
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <form method="POST" action="{{ route('events.form.custom-template', ['event' => $event->id, 'customTemplate' => $customTemplate->id]) }}" style="flex: 1;" id="eventCustomTemplateForm{{ $customTemplate->id }}">
                    @csrf
                    <button type="button" class="btn {{ $isActiveCustom ? 'btn-secondary' : 'btn-success' }}" style="width: 100%;"
                        @if($isActiveCustom) disabled @endif
                        @if(!$isActiveCustom) onclick="showConfirmModal('Gunakan Template?', 'Form pendaftaran yang ada akan diganti dengan template ini.', () => document.getElementById('eventCustomTemplateForm{{ $customTemplate->id }}').submit())" @endif>
                        {{ $isActiveCustom ? '✓ Template Aktif' : 'Gunakan Template' }}
                    </button>
                </form>
                <button type="button" class="btn btn-info" style="width: 100px;"
                    onclick="editTemplateForm({{ json_encode($customTemplate->form_fields) }}, '{{ addslashes($customTemplate->name) }}', {{ $customTemplate->id }})"
                    title="Edit field template">
                    ✏️ Edit Field
                </button>
            </div>
            <!-- Tombol Edit & Hapus di bagian bawah -->
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #ddd;">
                <button type="button" class="btn btn-sm btn-warning" style="flex: 1;" onclick="editCustomTemplate({{ $customTemplate->id }}, '{{ addslashes($customTemplate->name) }}', '{{ addslashes($customTemplate->description ?? '') }}', {{ json_encode($customTemplate->form_fields) }})" title="Edit nama, deskripsi, dan field template">
                    📝 Edit Data Template
                </button>
                <form method="POST" action="{{ route('custom-event-templates.delete', $customTemplate->id) }}" style="flex: 1;" id="deleteEventTemplateForm{{ $customTemplate->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-danger" style="width: 100%;" title="Hapus Template" 
                        onclick="showConfirmModal('Hapus Template?', 'Template akan dihapus permanen.', () => document.getElementById('deleteEventTemplateForm{{ $customTemplate->id }}').submit())">
                        🗑️ Hapus Template
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Save as Template Button -->
@push('scripts')
<script>
    // Function to preview template fields
    function previewTemplate(fields, templateName) {
        if (!fields || fields.length === 0) {
            alert('❌ Template ini tidak memiliki field untuk dipratinjau.');
            return;
        }

        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; overflow-y: auto; padding: 2rem;';
        
        let fieldsHTML = '';
        fields.forEach((field, index) => {
            let inputHTML = '';
            
            if (field.type === 'textarea') {
                inputHTML = `<textarea class="form-control" placeholder="Masukkan ${field.label.toLowerCase()}" ${field.required ? 'required' : ''}></textarea>`;
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
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-weight: bold; margin-bottom: 0.5rem; display: block;">
                        ${field.label}
                        ${field.required ? '<span style="color: red;">*</span>' : ''}
                    </label>
                    ${field.description ? `<p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                    ${inputHTML}
                </div>
            `;
        });

        modal.innerHTML = `
            <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0;">👁️ Preview: ${templateName}</h3>
                    <button type="button" onclick="this.closest('div[style*=fixed]').remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
                </div>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border-left: 4px solid #0d6efd;">
                    <p style="margin: 0; font-size: 0.875rem; color: #666;">
                        <strong>ℹ️ Ini adalah preview template.</strong> Field di bawah ini akan muncul dalam form pendaftaran jika template ini digunakan.
                    </p>
                </div>
                <form onsubmit="event.preventDefault(); alert('Ini hanya preview, data tidak akan tersimpan.');">
                    ${fieldsHTML}
                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Kirim Pendaftaran (Preview)</button>
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

    // Function to show registration form preview
    function showRegistrationPreview() {
        const existingForm = @json($existingForm ? $existingForm->form_fields : []);
        
        if (!existingForm || existingForm.length === 0) {
            alert('❌ Tidak ada form untuk dipratinjau.');
            return;
        }

        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; overflow-y: auto; padding: 2rem;';
        
        let fieldsHTML = '';
        existingForm.forEach((field, index) => {
            let inputHTML = '';
            
            if (field.type === 'textarea') {
                inputHTML = `<textarea class="form-control" placeholder="Masukkan ${field.label.toLowerCase()}" ${field.required ? 'required' : ''}></textarea>`;
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
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-weight: bold; margin-bottom: 0.5rem; display: block;">
                        ${field.label}
                        ${field.required ? '<span style="color: red;">*</span>' : ''}
                    </label>
                    ${field.description ? `<p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">${field.description}</p>` : ''}
                    ${inputHTML}
                </div>
            `;
        });

        modal.innerHTML = `
            <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0;">👁️ Preview Form Pendaftaran</h3>
                    <button type="button" onclick="this.closest('div[style*=fixed]').remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
                </div>
                <form onsubmit="event.preventDefault(); alert('Ini hanya preview, data tidak akan tersimpan.');">
                    ${fieldsHTML}
                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Kirim Pendaftaran</button>
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

    // Function to show save template modal
    function showSaveTemplateModal() {
        // Use existing form fields instead of collecting from builder
        const fields = @json($existingForm ? $existingForm->form_fields : []);

        if (fields.length === 0) {
            alert('❌ Tidak ada field untuk disimpan sebagai template!');
            return;
        }

        const formFields = JSON.stringify(fields);

        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        modal.innerHTML = `
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
            <h3 style="margin-bottom: 1rem;">💾 Simpan Sebagai Template</h3>
            <form method="POST" action="{{ route('events.form.save-template', $event->id) }}">
                @csrf
                <div class="form-group">
                    <label>Nama Template *</label>
                    <input type="text" name="template_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="template_description" class="form-control" rows="3"></textarea>
                </div>
                <input type="hidden" name="form_fields" value='${formFields}'>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Simpan Template</button>
                    <button type="button" class="btn btn-secondary" onclick="this.closest('div[style*=fixed]').remove()">Batal</button>
                </div>
            </form>
        </div>
    `;
        document.body.appendChild(modal);
    }

    // Function to edit custom template
    function editCustomTemplate(id, name, description, fields) {
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        modal.innerHTML = `
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
            <h3 style="margin-bottom: 1rem;">✏️ Edit Template</h3>
            <form method="POST" action="/custom-event-templates/${id}">
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
                <input type="hidden" name="form_fields" value='${JSON.stringify(fields)}'>
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

    // Function to edit template form (redirect to custom builder with template data)
    function editTemplateForm(fields, templateName, templateId = null) {
        // Save template fields to localStorage
        localStorage.setItem('template_preview_fields', JSON.stringify(fields));
        localStorage.setItem('editing_template', 'true'); // Flag untuk tidak auto-save sebagai template baru
        if (templateName) {
            localStorage.setItem('template_preview_name', templateName);
        }
        if (templateId) {
            localStorage.setItem('editing_template_id', templateId); // Simpan ID template untuk update
        }
        // Redirect to custom builder
        window.location.href = "{{ route('events.form.custom', $event) }}";
    }
</script>
@endpush
@endsection