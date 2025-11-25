@extends('layouts.app')

@section('title', 'Buat Form Pendaftaran Custom')

@section('content')
<div class="card">
    <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('events.form.create', $event) }}" style="color: #3498db; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <span>←</span> Kembali ke Kelola Form
        </a>
    </div>

    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; font-weight: bold; margin-bottom: 0.5rem; color: #1e293b;">
            🎨 Form Pendaftaran Custom
        </h1>
        <h2 style="font-size: 1.25rem; color: #666; margin-bottom: 0.5rem;">{{ $event->name }}</h2>
        <p style="color: #888; font-size: 0.95rem;">Buat field form pendaftaran sesuai kebutuhan event Anda dengan drag & drop</p>
    </div>

    @if($existingForm)
    <div class="alert alert-info" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        ℹ️ <strong>Mode Edit:</strong> Form pendaftaran sudah ada. Anda dapat mengedit field di bawah ini.
    </div>
    @endif
</div>

<!-- Custom Form Builder -->
<div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 12px; padding: 2rem;">
    <div style="background: #2563eb; color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <h3 style="margin: 0; font-size: 1.5rem; font-weight: bold;">✨ Form Builder</h3>
            <div id="templateNameDisplay" style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem; display: none;">
                <strong>Template:</strong> <span id="templateNameText"></span>
            </div>
        </div>
        <p style="margin: 0; opacity: 0.9;">Drag & drop untuk mengatur urutan field, atau klik tombol di bawah untuk menambah field baru</p>
    </div>

    <div id="formBuilder">
        <div id="fieldsContainer">
            @if($existingForm && $existingForm->form_fields)
            @foreach($existingForm->form_fields as $index => $field)
            <div class="field-item" data-index="{{ $index }}" draggable="true" style="border: 2px solid #e0e0e0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                        <span class="drag-handle" style="font-size: 1.5rem; color: #9e9e9e; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                        <input type="text" class="form-control field-label" value="{{ $field['label'] }}" style="border: none; border-bottom: 2px solid #e0e0e0; font-size: 1.15rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #333;" placeholder="Label Field (mis: Nama Tim)" required>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="button" class="btn btn-sm" onclick="duplicateField(this)" title="Duplikat Field" style="background: #e3f2fd; border: none; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px; transition: all 0.2s;">📋</button>
                        <button type="button" class="btn btn-sm btn-danger remove-field" title="Hapus Field" style="background: #ffebee; border: none; color: #c62828; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px; transition: all 0.2s;">🗑️</button>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555;">Deskripsi (Opsional)</label>
                    <textarea class="form-control field-description" rows="2" style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 0.75rem; font-size: 0.95rem; resize: vertical;" placeholder="Tambahkan penjelasan atau panduan untuk field ini...">{{ $field['description'] ?? '' }}</textarea>
                </div>
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.1rem; color: #5f6368;">📊</span>
                        <select class="form-control field-type" style="border: 2px solid #e0e0e0; background: white; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.95rem; color: #333; cursor: pointer; font-weight: 500;">
                            <option value="text" {{ $field['type'] === 'text' ? 'selected' : '' }}>📝 Teks Pendek</option>
                            <option value="textarea" {{ $field['type'] === 'textarea' ? 'selected' : '' }}>📄 Teks Panjang</option>
                            <option value="email" {{ $field['type'] === 'email' ? 'selected' : '' }}>📧 Email</option>
                            <option value="number" {{ $field['type'] === 'number' ? 'selected' : '' }}>🔢 Angka</option>
                            <option value="date" {{ $field['type'] === 'date' ? 'selected' : '' }}>📅 Tanggal</option>
                            <option value="tel" {{ $field['type'] === 'tel' ? 'selected' : '' }}>📞 Telepon</option>
                            <option value="file" {{ $field['type'] === 'file' ? 'selected' : '' }}>📎 File Upload</option>
                            <option value="select" {{ $field['type'] === 'select' ? 'selected' : '' }}>📋 Dropdown</option>
                        </select>
                    </div>
                    
                    <div class="select-options-field" style="display: {{ $field['type'] === 'select' ? 'flex' : 'none' }}; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 2px solid #ff9800; flex: 1;">
                        <label style="margin: 0; font-size: 0.95rem; color: #333; font-weight: 600;">Opsi (pisahkan dengan koma):</label>
                        <input type="text" class="form-control field-options" value="{{ isset($field['options']) ? implode(', ', $field['options']) : '' }}" placeholder="Opsi 1, Opsi 2, Opsi 3" style="border: 2px solid #e0e0e0; padding: 0.5rem; border-radius: 8px; font-size: 0.95rem;">
                    </div>
                    
                    <div style="margin-left: auto; display: flex; align-items: center; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 2px solid #4caf50;">
                        <label style="margin: 0; font-size: 0.95rem; color: #333; margin-right: 0.75rem; font-weight: 600;">Wajib Diisi</label>
                        <input type="checkbox" class="field-required" {{ $field['required'] ?? false ? 'checked' : '' }} style="width: 22px; height: 22px; cursor: pointer; accent-color: #4caf50;">
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        <button type="button" id="addField" class="btn-premium btn-premium-success" style="margin-bottom: 2rem; padding: 1rem 2rem; border-radius: 8px; font-size: 1.05rem; border: none; display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 1.5rem;">➕</span> Tambah Field Baru
        </button>

        <form method="POST" action="{{ route('events.form.store', $event) }}" id="saveFormBtn" style="border-top: 3px solid #e0e0e0; padding-top: 2rem; margin-top: 1rem;">
            @csrf
            <input type="hidden" name="form_fields" id="formFieldsData">
            <input type="hidden" name="editing_template" id="editingTemplateFlag" value="false">
            <input type="hidden" name="template_id" id="templateIdInput" value="">

            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 600; color: #333; font-size: 1.05rem; margin-bottom: 0.75rem;">
                    📝 Deskripsi Form Pendaftaran <span style="color: #999; font-weight: normal; font-size: 0.9rem;">(Opsional)</span>
                </label>
                <textarea name="description" id="formDescription" class="form-control" rows="3" style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 0.75rem; font-size: 1rem; resize: vertical;" placeholder="Tambahkan deskripsi atau catatan untuk form pendaftaran ini...">{{ $existingForm->description ?? '' }}</textarea>
                <small style="color: #666; font-size: 0.85rem; margin-top: 0.5rem; display: block;">Deskripsi akan ditampilkan kepada peserta saat mengisi form pendaftaran</small>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: space-between; align-items: center;">
                <a href="{{ route('events.form.create', $event) }}" style="text-decoration: none; color: #666; padding: 1rem 2rem; border-radius: 12px; border: 2px solid #ddd; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s;">
                    <span style="font-size: 1.25rem;">←</span> Batal
                </a>
                <button type="submit" class="btn-premium btn-premium-primary" style="font-size: 1.05rem; padding: 1rem 2.5rem; border-radius: 8px; border: none; display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 1.5rem;">💾</span> Simpan Form Pendaftaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
@if(session('show_success_modal'))
<div id="successModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: flex; justify-content: center; align-items: center; z-index: 9999; animation: fadeIn 0.3s; backdrop-filter: blur(5px);">
    <div style="background: #ffffff; padding: 3rem 2.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; max-width: 450px; animation: slideUp 0.4s; border: 1px solid #e5e7eb;">
        <div style="width: 80px; height: 80px; background: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); animation: checkPulse 0.6s;">
            <span style="font-size: 3rem; color: white;">✓</span>
        </div>
        <h3 style="color: #2c3e50; font-size: 1.75rem; margin-bottom: 0.75rem; font-weight: 700;">Berhasil Disimpan!</h3>
        <p style="color: #5f6368; font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">{{ session('success') }}</p>
        <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: center; color: #4caf50; font-size: 0.9rem;">
            <div class="spinner" style="width: 18px; height: 18px; border: 2px solid #e0e0e0; border-top-color: #4caf50; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
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
                item.style.border = '2px solid #e0e0e0';
            });
        });

        item.addEventListener('dragover', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            this.style.border = '2px dashed #2196f3';
            return false;
        });

        item.addEventListener('dragleave', function(e) {
            this.style.border = '2px solid #e0e0e0';
        });

        item.addEventListener('drop', function(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            this.style.border = '2px solid #e0e0e0';

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
        fieldItem.style.cssText = 'border: 2px solid #e0e0e0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move; animation: slideIn 0.3s;';

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
                    <span class="drag-handle" style="font-size: 1.5rem; color: #9e9e9e; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                    <input type="text" class="form-control field-label" value="${field.label || ''}" style="border: none; border-bottom: 2px solid #e0e0e0; font-size: 1.15rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #333;" placeholder="Label Field (mis: Nama Tim)" required>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <button type="button" class="btn btn-sm" onclick="duplicateField(this)" title="Duplikat Field" style="background: #e3f2fd; border: none; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px;">📋</button>
                    <button type="button" class="btn btn-sm btn-danger remove-field" title="Hapus Field" style="background: #ffebee; border: none; color: #c62828; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px;">🗑️</button>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555;">Deskripsi (Opsional)</label>
                <textarea class="form-control field-description" rows="2" style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 0.75rem; font-size: 0.95rem; resize: vertical;" placeholder="Tambahkan penjelasan atau panduan untuk field ini...">${field.description || ''}</textarea>
            </div>
            <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555;">Tipe Field</label>
                    <select class="form-control field-type" style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 0.75rem; font-size: 1rem;" onchange="toggleOptionsField(this)">
                        <option value="text" ${field.type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="email" ${field.type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="number" ${field.type === 'number' ? 'selected' : ''}>Number</option>
                        <option value="tel" ${field.type === 'tel' ? 'selected' : ''}>Telepon</option>
                        <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                        <option value="select" ${field.type === 'select' ? 'selected' : ''}>Select/Dropdown</option>
                        <option value="date" ${field.type === 'date' ? 'selected' : ''}>Date</option>
                    </select>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: white; border-radius: 8px; border: 2px solid #e0e0e0;">
                    <input type="checkbox" class="field-required" ${field.required ? 'checked' : ''} style="width: 20px; height: 20px; cursor: pointer;">
                    <label style="margin: 0; font-weight: 600; color: #555; cursor: pointer;">Wajib diisi</label>
                </div>
            </div>
            <div class="options-container" style="margin-top: 1rem; display: ${field.type === 'select' ? 'block' : 'none'};">
                <label style="font-weight: 600; color: #555; margin-bottom: 0.5rem; display: block;">Opsi Pilihan:</label>
                <div class="options-list">${optionsHTML}</div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addOption(this)" style="margin-top: 0.5rem;">+ Tambah Opsi</button>
            </div>
        `;

        container.appendChild(fieldItem);
        enableDragAndDropForElement(fieldItem);
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
                    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #4caf50; color: white; padding: 1rem 1.5rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.3);';
                    notification.innerHTML = '<strong>✅ Template berhasil dimuat!</strong><br>Anda bisa edit field sebelum menyimpan.';
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
            
            fieldItem.style.cssText = 'border: 2px solid #e0e0e0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move; animation: slideIn 0.3s;';

            fieldItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                        <span class="drag-handle" style="font-size: 1.5rem; color: #9e9e9e; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                        <input type="text" class="form-control field-label" style="border: none; border-bottom: 2px solid #e0e0e0; font-size: 1.15rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #333;" placeholder="Label Field (mis: Nama Tim)" required>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="button" class="btn btn-sm" onclick="duplicateField(this)" title="Duplikat Field" style="background: #e3f2fd; border: none; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px; transition: all 0.2s;">📋</button>
                        <button type="button" class="btn btn-sm btn-danger remove-field" title="Hapus Field" style="background: #ffebee; border: none; color: #c62828; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px; transition: all 0.2s;">🗑️</button>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.1rem; color: #5f6368;">📊</span>
                        <select class="form-control field-type" style="border: 2px solid #e0e0e0; background: white; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.95rem; color: #333; cursor: pointer; font-weight: 500;">
                            <option value="text">📝 Teks Pendek</option>
                            <option value="textarea">📄 Teks Panjang</option>
                            <option value="email">📧 Email</option>
                            <option value="number">🔢 Angka</option>
                            <option value="date">📅 Tanggal</option>
                            <option value="tel">📞 Telepon</option>
                            <option value="file">📎 File Upload</option>
                            <option value="select">📋 Dropdown</option>
                        </select>
                    </div>
                    
                    <div class="select-options-field" style="display: none; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 2px solid #ff9800; flex: 1;">
                        <label style="margin: 0; font-size: 0.95rem; color: #333; font-weight: 600;">Opsi (pisahkan dengan koma):</label>
                        <input type="text" class="form-control field-options" placeholder="Opsi 1, Opsi 2, Opsi 3" style="border: 2px solid #e0e0e0; padding: 0.5rem; border-radius: 8px; font-size: 0.95rem;">
                    </div>
                    
                    <div style="margin-left: auto; display: flex; align-items: center; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 2px solid #4caf50;">
                        <label style="margin: 0; font-size: 0.95rem; color: #333; margin-right: 0.75rem; font-weight: 600;">Wajib Diisi</label>
                        <input type="checkbox" class="field-required" style="width: 22px; height: 22px; cursor: pointer; accent-color: #4caf50;">
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
                    item.style.border = '2px solid #e0e0e0';
                });
            });

            item.addEventListener('dragover', function(e) {
                if (e.preventDefault) {
                    e.preventDefault();
                }
                e.dataTransfer.dropEffect = 'move';
                this.style.border = '2px dashed #2196f3';
                return false;
            });

            item.addEventListener('dragleave', function(e) {
                this.style.border = '2px solid #e0e0e0';
            });

            item.addEventListener('drop', function(e) {
                if (e.stopPropagation) {
                    e.stopPropagation();
                }
                this.style.border = '2px solid #e0e0e0';

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
                    alert('❌ Semua field harus memiliki label!');
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
                        alert('❌ Field dropdown harus memiliki opsi!');
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
                alert('❌ Minimal harus ada 1 field!');
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
            this.style.boxShadow = '0 6px 16px rgba(76, 175, 80, 0.4)';
        });
        document.getElementById('addField').addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px rgba(76, 175, 80, 0.3)';
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
