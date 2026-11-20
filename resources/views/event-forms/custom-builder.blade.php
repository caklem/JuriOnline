@extends('layouts.app')

@section('title', 'Buat Form Pendaftaran Custom')

@section('content')
<div class="card">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('events.form.create', $event) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-indigo-600">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0 6-6m-6 6 6 6" />
            </svg>
            Kembali ke Kelola Form
        </a>
    </div>

    <div class="mt-4">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Form Pendaftaran Custom</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $event->name }}</p>
        <p class="mt-2 text-sm text-slate-500">Buat field form pendaftaran sesuai kebutuhan event Anda dengan drag & drop</p>
    </div>

    @if($existingForm)
    <div class="alert alert-info mt-5">
        <strong class="font-semibold">Mode Edit:</strong> Form pendaftaran sudah ada. Anda dapat mengedit field di bawah ini.
    </div>
    @endif
</div>

<!-- Custom Form Builder -->
<div class="card">
    <div class="mb-6 rounded-xl bg-slate-900 px-6 py-5 text-white">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <h3 class="flex items-center gap-2 text-lg font-semibold">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5 text-indigo-300" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h7" />
                </svg>
                Form Builder
            </h3>
            <div id="templateNameDisplay" style="background: rgba(255,255,255,0.12); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; display: none;">
                <strong class="font-semibold">Template:</strong> <span id="templateNameText"></span>
            </div>
        </div>
        <p class="mt-2 text-sm text-slate-300">Drag & drop untuk mengatur urutan field, atau klik tombol di bawah untuk menambah field baru</p>
    </div>

    <div id="formBuilder">
        <div id="fieldsContainer">
            @if($existingForm && $existingForm->form_fields)
            @foreach($existingForm->form_fields as $index => $field)
            <div class="field-item" data-index="{{ $index }}" draggable="true" style="border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 1px 2px rgba(15,23,42,0.04); transition: border-color 0.2s, box-shadow 0.2s; cursor: move;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                        <span class="drag-handle" style="font-size: 1.5rem; color: #94a3b8; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                        <input type="text" class="form-control field-label" value="{{ $field['label'] }}" style="border: none; border-bottom: 1px solid #e2e8f0; font-size: 1.125rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #0f172a;" placeholder="Label Field (mis: Nama Tim)" required>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="duplicateField(this)" title="Duplikat Field" aria-label="Duplikat Field" style="padding: 0.4rem 0.6rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2M6 8h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" />
                            </svg>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger remove-field" title="Hapus Field" aria-label="Hapus Field" style="padding: 0.4rem 0.6rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">Deskripsi (Opsional)</label>
                    <textarea class="form-control field-description" rows="2" placeholder="Tambahkan penjelasan atau panduan untuk field ini...">{{ $field['description'] ?? '' }}</textarea>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; background: #f8fafc; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <select class="form-control field-type" style="cursor: pointer; font-weight: 500;" onchange="toggleOptionsField(this)">
                            <option value="text" {{ $field['type'] === 'text' ? 'selected' : '' }}>Teks Pendek</option>
                            <option value="textarea" {{ $field['type'] === 'textarea' ? 'selected' : '' }}>Teks Panjang</option>
                            <option value="email" {{ $field['type'] === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="number" {{ $field['type'] === 'number' ? 'selected' : '' }}>Angka</option>
                            <option value="date" {{ $field['type'] === 'date' ? 'selected' : '' }}>Tanggal</option>
                            <option value="tel" {{ $field['type'] === 'tel' ? 'selected' : '' }}>Telepon</option>
                            <option value="file" {{ $field['type'] === 'file' ? 'selected' : '' }}>File Upload</option>
                            <option value="select" {{ $field['type'] === 'select' ? 'selected' : '' }}>Dropdown</option>
                        </select>
                    </div>
                    
                    <div class="select-options-field" style="display: {{ $field['type'] === 'select' ? 'flex' : 'none' }}; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 0.75rem; border-radius: 10px; border: 1px solid #e2e8f0; flex: 1;">
                        <label style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600; white-space: nowrap;">Opsi (pisahkan dengan koma):</label>
                        <input type="text" class="form-control field-options" value="{{ isset($field['options']) ? implode(', ', $field['options']) : '' }}" placeholder="Opsi 1, Opsi 2, Opsi 3">
                    </div>
                    
                    <div style="margin-left: auto; display: flex; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 0.875rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <label style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600;">Wajib Diisi</label>
                        <input type="checkbox" class="field-required h-5 w-5 cursor-pointer accent-indigo-600" {{ $field['required'] ?? false ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        <button type="button" id="addField" class="btn-premium btn-premium-success" style="margin-bottom: 2rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
            </svg>
            Tambah Field Baru
        </button>

        <form method="POST" action="{{ route('events.form.store', $event) }}" id="saveFormBtn" style="border-top: 1px solid #e2e8f0; padding-top: 2rem; margin-top: 1rem;">
            @csrf
            <input type="hidden" name="form_fields" id="formFieldsData">
            <input type="hidden" name="editing_template" id="editingTemplateFlag" value="false">
            <input type="hidden" name="template_id" id="templateIdInput" value="">

            <div class="form-group" style="margin-bottom: 2rem;">
                <label class="form-label" style="font-size: 0.9375rem;">
                    Deskripsi Form Pendaftaran <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                </label>
                <textarea name="description" id="formDescription" class="form-control" rows="3" placeholder="Tambahkan deskripsi atau catatan untuk form pendaftaran ini...">{{ $existingForm->description ?? '' }}</textarea>
                <small class="mt-1 block text-xs text-slate-500">Deskripsi akan ditampilkan kepada peserta saat mengisi form pendaftaran</small>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('events.form.create', $event) }}" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn-premium btn-premium-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13v6h14v-6M12 3v12m0 0 4-4m-4 4-4-4" />
                    </svg>
                    Simpan Form Pendaftaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
@if(session('show_success_modal'))
<div id="successModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/70 p-4 backdrop-blur-sm" style="animation: fadeIn 0.3s;">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl" style="animation: slideUp 0.4s;">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 ring-1 ring-emerald-200" style="animation: checkPulse 0.6s;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-8 w-8 text-emerald-600" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
            </svg>
        </div>
        <h3 class="text-2xl font-semibold tracking-tight text-slate-900">Berhasil Disimpan!</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ session('success') }}</p>
        <div class="mt-6 flex items-center justify-center gap-2 text-xs font-medium text-slate-500">
            <div class="spinner" style="width: 16px; height: 16px; border: 2px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
            <span>Mengalihkan ke halaman kelola form...</span>
        </div>
    </div>
</div>

<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideUp {
        from { transform: translateY(50px) scale(0.9); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.95); }
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
                window.location.href = "{{ route('events.form.create', $event) }}";
            }, 300);
        }
    }, 2000);
</script>
@endif

@push('scripts')
<script>
    // Define fieldIndex globally
    let fieldIndex = {{ $existingForm && $existingForm->form_fields ? count($existingForm->form_fields) : 0 }};

    // Define enableDragAndDropForElement first
    function enableDragAndDropForElement(item) {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
            this.style.opacity = '0.4';
        });

        item.addEventListener('dragend', function(e) {
            this.style.opacity = '1';
            document.querySelectorAll('.field-item').forEach(item => {
                item.style.border = '1px solid #e2e8f0';
            });
        });

        item.addEventListener('dragover', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            this.style.border = '1px dashed #6366f1';
            return false;
        });

        item.addEventListener('dragleave', function(e) {
            this.style.border = '1px solid #e2e8f0';
        });

        item.addEventListener('drop', function(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            this.style.border = '1px solid #e2e8f0';

            const draggedElement = document.querySelector('.field-item[style*="opacity: 0.4"]');
            if (draggedElement && draggedElement !== this) {
                const container = document.getElementById('fieldsContainer');
                const allItems = Array.from(container.querySelectorAll('.field-item'));
                const draggedIndex = allItems.indexOf(draggedElement);
                const targetIndex = allItems.indexOf(this);

                if (draggedIndex < targetIndex) {
                    this.parentNode.insertBefore(draggedElement, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(draggedElement, this);
                }
            }
            return false;
        });
    }

    // Define helper functions
    function loadFieldFromTemplate(field) {
        const container = document.getElementById('fieldsContainer');
        const fieldItem = document.createElement('div');
        fieldItem.className = 'field-item';
        fieldItem.setAttribute('data-index', fieldIndex);
        fieldItem.setAttribute('draggable', 'true');
        fieldItem.style.cssText = 'border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move; animation: slideIn 0.3s;';

        let optionsHTML = '';
        if (field.type === 'select' && field.options) {
            optionsHTML = field.options.map((opt, i) => `
                <div class="option-item" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <input type="text" class="form-control" value="${opt}" placeholder="Opsi ${i + 1}">
                    <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">×</button>
                </div>
            `).join('');
        }

        fieldItem.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                    <span class="drag-handle" style="font-size: 1.5rem; color: #94a3b8; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                    <input type="text" class="form-control field-label" value="${field.label || ''}" style="border: none; border-bottom: 1px solid #e2e8f0; font-size: 1.15rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #0f172a;" placeholder="Label Field (mis: Nama Tim)" required>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="duplicateField(this)" title="Duplikat Field" aria-label="Duplikat Field" style="padding: 0.4rem 0.6rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2M6 8h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" /></svg>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger remove-field" title="Hapus Field" aria-label="Hapus Field" style="padding: 0.4rem 0.6rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" /></svg>
                    </button>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #475569;">Deskripsi (Opsional)</label>
                <textarea class="form-control field-description" rows="2" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; font-size: 0.95rem; resize: vertical;" placeholder="Tambahkan penjelasan atau panduan untuk field ini...">${field.description || ''}</textarea>
            </div>
            <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; background: #f8fafc; padding: 1rem; border-radius: 8px;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #475569;">Tipe Field</label>
                    <select class="form-control field-type" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; font-size: 1rem;" onchange="toggleOptionsField(this)">
                        <option value="text" ${field.type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="email" ${field.type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="number" ${field.type === 'number' ? 'selected' : ''}>Number</option>
                        <option value="tel" ${field.type === 'tel' ? 'selected' : ''}>Telepon</option>
                        <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                        <option value="select" ${field.type === 'select' ? 'selected' : ''}>Select/Dropdown</option>
                        <option value="date" ${field.type === 'date' ? 'selected' : ''}>Date</option>
                    </select>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <input type="checkbox" class="field-required" ${field.required ? 'checked' : ''} style="width: 20px; height: 20px; cursor: pointer;">
                    <label style="margin: 0; font-weight: 600; color: #475569; cursor: pointer;">Wajib diisi</label>
                </div>
            </div>
            <div class="options-container" style="margin-top: 1rem; display: ${field.type === 'select' ? 'block' : 'none'};">
                <label style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">Opsi Pilihan:</label>
                <div class="options-list">${optionsHTML}</div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addOption(this)" style="margin-top: 0.5rem;">+ Tambah Opsi</button>
            </div>
        `;

        container.appendChild(fieldItem);
        enableDragAndDropForElement(fieldItem);
    }

    function toggleOptionsField(select) {
        const fieldItem = select.closest('.field-item');
        if (!fieldItem) return;

        const optionsContainer = fieldItem.querySelector('.options-container');
        const selectOptionsField = fieldItem.querySelector('.select-options-field');
        const isSelect = select.value === 'select';

        if (optionsContainer) {
            optionsContainer.style.display = isSelect ? 'block' : 'none';
        }

        if (selectOptionsField) {
            selectOptionsField.style.display = isSelect ? 'flex' : 'none';
        }
    }

    function addOption(button) {
        const optionsContainer = button.closest('.options-container');
        const optionsList = optionsContainer ? optionsContainer.querySelector('.options-list') : null;
        if (!optionsList) return;

        const optionCount = optionsList.querySelectorAll('.option-item').length + 1;
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        optionItem.style.cssText = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem;';
        optionItem.innerHTML = `
            <input type="text" class="form-control" placeholder="Opsi ${optionCount}">
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">×</button>
        `;
        optionsList.appendChild(optionItem);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Check if there's template preview data in localStorage
        const previewFields = localStorage.getItem('template_preview_fields');
        const templateName = localStorage.getItem('template_preview_name');
        const isEditingTemplate = localStorage.getItem('editing_template') === 'true';
        const templateId = localStorage.getItem('editing_template_id');
        
        if (previewFields) {
            try {
                const fields = JSON.parse(previewFields);
                localStorage.removeItem('template_preview_fields');
                localStorage.removeItem('editing_template'); // Clear flag setelah dibaca
                
                // Set template ID jika ada
                if (templateId) {
                    document.getElementById('templateIdInput').value = templateId;
                    localStorage.removeItem('editing_template_id');
                }
                
                // Clear existing fields and load template
                const container = document.getElementById('fieldsContainer');
                if (container && fields.length > 0) {
                    container.innerHTML = '';
                    fieldIndex = 0;
                    
                    fields.forEach((field) => {
                        loadFieldFromTemplate(field);
                        fieldIndex++;
                    });
                    
                    // Show template name if available
                    if (templateName) {
                        document.getElementById('templateNameDisplay').style.display = 'block';
                        document.getElementById('templateNameText').textContent = templateName;
                        localStorage.removeItem('template_preview_name');
                    }
                    
                    // Set flag untuk menandakan sedang edit template
                    if (isEditingTemplate) {
                        document.getElementById('editingTemplateFlag').value = 'true';
                    }
                    
                    // Show notification
                    const notification = document.createElement('div');
                    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #059669; color: white; padding: 1rem 1.5rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.3);';
                    notification.innerHTML = '<strong>Template berhasil dimuat!</strong><br>Anda bisa edit field sebelum menyimpan.';
                    document.body.appendChild(notification);
                    setTimeout(() => notification.remove(), 4000);
                }
            } catch (e) {
                console.error('Error loading template:', e);
                localStorage.removeItem('template_preview_fields');
            }
        }

        document.getElementById('addField').addEventListener('click', function() {
            const container = document.getElementById('fieldsContainer');
            const fieldItem = document.createElement('div');
            fieldItem.className = 'field-item';
            fieldItem.setAttribute('data-index', fieldIndex);
            fieldItem.setAttribute('draggable', 'true');
            
            fieldItem.style.cssText = 'border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move; animation: slideIn 0.3s;';

            fieldItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                        <span class="drag-handle" style="font-size: 1.5rem; color: #94a3b8; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                        <input type="text" class="form-control field-label" style="border: none; border-bottom: 1px solid #e2e8f0; font-size: 1.125rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #0f172a;" placeholder="Label Field (mis: Nama Tim)" required>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="duplicateField(this)" title="Duplikat Field" aria-label="Duplikat Field" style="padding: 0.4rem 0.6rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2M6 8h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" /></svg>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger remove-field" title="Hapus Field" aria-label="Hapus Field" style="padding: 0.4rem 0.6rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" /></svg>
                        </button>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; background: #f8fafc; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <select class="form-control field-type" style="cursor: pointer; font-weight: 500;" onchange="toggleOptionsField(this)">
                            <option value="text">Teks Pendek</option>
                            <option value="textarea">Teks Panjang</option>
                            <option value="email">Email</option>
                            <option value="number">Angka</option>
                            <option value="date">Tanggal</option>
                            <option value="tel">Telepon</option>
                            <option value="file">File Upload</option>
                            <option value="select">Dropdown</option>
                        </select>
                    </div>
                    
                    <div class="select-options-field" style="display: none; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 0.75rem; border-radius: 10px; border: 1px solid #e2e8f0; flex: 1;">
                        <label style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600; white-space: nowrap;">Opsi (pisahkan dengan koma):</label>
                        <input type="text" class="form-control field-options" placeholder="Opsi 1, Opsi 2, Opsi 3">
                    </div>
                    
                    <div style="margin-left: auto; display: flex; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 0.875rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <label style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600;">Wajib Diisi</label>
                        <input type="checkbox" class="field-required h-5 w-5 cursor-pointer accent-indigo-600">
                    </div>
                </div>
            `;

            container.appendChild(fieldItem);
            enableDragAndDrop(fieldItem);
            fieldIndex++;

            // Add slide in animation
            fieldItem.style.animation = 'slideIn 0.3s';
        });

        // Drag and drop functionality
        function enableDragAndDrop(item) {
            item.addEventListener('dragstart', function(e) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', this.innerHTML);
                this.style.opacity = '0.4';
            });

            item.addEventListener('dragend', function(e) {
                this.style.opacity = '1';
                document.querySelectorAll('.field-item').forEach(item => {
                    item.style.border = '1px solid #e2e8f0';
                });
            });

            item.addEventListener('dragover', function(e) {
                if (e.preventDefault) {
                    e.preventDefault();
                }
                e.dataTransfer.dropEffect = 'move';
                this.style.border = '1px dashed #6366f1';
                return false;
            });

            item.addEventListener('dragleave', function(e) {
                this.style.border = '1px solid #e2e8f0';
            });

            item.addEventListener('drop', function(e) {
                if (e.stopPropagation) {
                    e.stopPropagation();
                }
                this.style.border = '1px solid #e2e8f0';

                const draggedElement = document.querySelector('.field-item[style*="opacity: 0.4"]');
                if (draggedElement && draggedElement !== this) {
                    const container = document.getElementById('fieldsContainer');
                    const allItems = Array.from(container.querySelectorAll('.field-item'));
                    const draggedIndex = allItems.indexOf(draggedElement);
                    const targetIndex = allItems.indexOf(this);

                    if (draggedIndex < targetIndex) {
                        this.parentNode.insertBefore(draggedElement, this.nextSibling);
                    } else {
                        this.parentNode.insertBefore(draggedElement, this);
                    }
                }
                return false;
            });
        }

        // Enable drag and drop for existing items
        document.querySelectorAll('.field-item').forEach(item => {
            enableDragAndDrop(item);
        });

        // Event delegation for remove button
        document.getElementById('fieldsContainer').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-field') || e.target.closest('.remove-field')) {
                const fieldItem = e.target.closest('.field-item');
                if (confirm('Yakin ingin menghapus field ini?')) {
                    fieldItem.style.transition = 'all 0.3s';
                    fieldItem.style.opacity = '0';
                    fieldItem.style.transform = 'scale(0.8)';
                    setTimeout(() => fieldItem.remove(), 300);
                }
            }
        });

        // Event delegation for type change
        document.getElementById('fieldsContainer').addEventListener('change', function(e) {
            if (e.target.classList.contains('field-type')) {
                const fieldItem = e.target.closest('.field-item');
                const optionsField = fieldItem.querySelector('.select-options-field');
                optionsField.style.display = e.target.value === 'select' ? 'flex' : 'none';
            }
        });

        // Save form
        console.log('=== ATTACHING FORM SUBMIT LISTENER ===');
        const formElement = document.getElementById('saveFormBtn');
        console.log('Form element found:', formElement);
        
        formElement.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMIT EVENT TRIGGERED ===');
            e.preventDefault();

            const fields = [];
            const fieldItems = document.querySelectorAll('.field-item');
            console.log('Found field items:', fieldItems.length);

            let hasError = false;

            fieldItems.forEach(function(item) {
                const label = item.querySelector('.field-label').value;
                const type = item.querySelector('.field-type').value;
                const required = item.querySelector('.field-required').checked;
                const optionsInput = item.querySelector('.field-options');
                const description = item.querySelector('.field-description') ? item.querySelector('.field-description').value : '';

                console.log('Field:', label, 'Type:', type, 'Required:', required, 'Description:', description);

                if (label.trim() === '') {
                    alert('Semua field harus memiliki label!');
                    item.querySelector('.field-label').focus();
                    hasError = true;
                    return;
                }

                const fieldData = {
                    label: label,
                    type: type,
                    required: required,
                    description: description
                };

                if (type === 'select' && optionsInput) {
                    const optionsText = optionsInput.value.trim();
                    if (optionsText === '') {
                        alert('Field dropdown harus memiliki opsi!');
                        optionsInput.focus();
                        hasError = true;
                        return;
                    }
                    fieldData.options = optionsText.split(',').map(opt => opt.trim()).filter(opt => opt !== '');
                    console.log('Select options:', fieldData.options);
                }

                fields.push(fieldData);
            });

            if (hasError) {
                return;
            }

            if (fields.length === 0) {
                alert('Minimal harus ada 1 field!');
                return;
            }

            console.log('=== FINAL DATA CHECK ===');
            console.log('Total fields collected:', fields.length);
            console.log('Fields data:', fields);
            console.log('JSON string:', JSON.stringify(fields));
            
            document.getElementById('formFieldsData').value = JSON.stringify(fields);
            console.log('Hidden input value set:', document.getElementById('formFieldsData').value);
            
            this.submit();
        });

        // Button hover effects
        document.getElementById('addField').addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 16px rgba(79, 70, 229, 0.28)';
        });
        document.getElementById('addField').addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px rgba(79, 70, 229, 0.22)';
        });
    });

    // Duplicate field function
    function duplicateField(button) {
        const fieldItem = button.closest('.field-item');
        const clone = fieldItem.cloneNode(true);
        
        // Clear label value in clone
        clone.querySelector('.field-label').value = fieldItem.querySelector('.field-label').value + ' (Copy)';
        
        fieldItem.parentNode.insertBefore(clone, fieldItem.nextSibling);
        
        // Re-enable drag and drop for clone
        enableDragAndDropForElement(clone);
        
        // Add animation
        clone.style.animation = 'slideIn 0.3s';
    }
</script>

<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .field-item:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important;
    }
    
    .drag-handle:active {
        cursor: grabbing !important;
    }
</style>
@endpush
@endsection
