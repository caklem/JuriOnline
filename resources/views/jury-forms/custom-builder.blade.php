@extends('layouts.app')

@section('title', 'Buat Form Penilaian Custom')

@section('content')
<div class="card">
    <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('events.jury-form.create', $event) }}" style="color: #3498db; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <span>←</span> Kembali ke Kelola Form
        </a>
    </div>

    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; font-weight: bold; margin-bottom: 0.5rem; color: #1e293b;">
            🎨 Form Penilaian Custom
        </h1>
        <h2 style="font-size: 1.25rem; color: #666; margin-bottom: 0.5rem;">{{ $event->name }}</h2>
        <p style="color: #888; font-size: 0.95rem;">Buat kriteria penilaian sesuai kebutuhan event Anda dengan drag & drop</p>
    </div>

    @if($existingForm)
    <div class="alert alert-info" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        ℹ️ <strong>Mode Edit:</strong> Form penilaian sudah ada. Anda dapat mengedit kriteria di bawah ini.
    </div>
    @endif
</div>

<!-- Custom Form Builder -->
<div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 12px; padding: 2rem;">
    <div style="background: #2563eb; color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <h3 style="margin: 0; font-size: 1.25rem;">📝 Kriteria Penilaian</h3>
            <div id="templateNameDisplay" style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem; display: none;">
                <strong>Template:</strong> <span id="templateNameText"></span>
            </div>
        </div>
        <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 0.9rem;">Drag & drop untuk mengubah urutan kriteria</p>
    </div>

    <div id="formBuilder">
        <div id="criteriaContainer">
            @if($existingForm && $existingForm->criteria_fields)
            @foreach($existingForm->criteria_fields as $index => $field)
            <div class="criteria-item" data-index="{{ $index }}" draggable="true" style="border: 2px solid #e0e0e0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                        <span class="drag-handle" style="font-size: 1.5rem; color: #9e9e9e; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                        <input type="text" class="form-control criteria-label" value="{{ $field['label'] }}" style="border: none; border-bottom: 2px solid #e0e0e0; font-size: 1.15rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #333;" placeholder="Nama Kriteria (mis: Teknik Vokal)" required>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="button" class="btn btn-sm" onclick="duplicateCriteria(this)" title="Duplikat Kriteria" style="background: #e3f2fd; border: none; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px; transition: all 0.2s;">📋</button>
                        <button type="button" class="btn btn-sm btn-danger remove-criteria" title="Hapus Kriteria" style="background: #ffebee; border: none; color: #c62828; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px; transition: all 0.2s;">🗑️</button>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <textarea class="form-control criteria-description" rows="2" style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 0.75rem; font-size: 0.9rem; resize: vertical;" placeholder="Deskripsi kriteria (opsional) - Jelaskan apa yang dinilai dari kriteria ini">{{ $field['description'] ?? '' }}</textarea>
                </div>
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.1rem; color: #5f6368;">📊</span>
                        <select class="form-control criteria-type" style="border: 2px solid #e0e0e0; background: white; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.95rem; color: #333; cursor: pointer; font-weight: 500;">
                            <option value="score" {{ $field['type'] === 'score' ? 'selected' : '' }}>⭐ Nilai Poin</option>
                            <option value="text" {{ $field['type'] === 'text' ? 'selected' : '' }}>📝 Teks Pendek</option>
                            <option value="textarea" {{ $field['type'] === 'textarea' ? 'selected' : '' }}>📄 Teks Panjang</option>
                        </select>
                    </div>
                    <div class="max-score-field" style="display: {{ $field['type'] === 'score' ? 'flex' : 'none' }}; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 2px solid #4caf50;">
                        <label style="margin: 0; font-size: 0.95rem; color: #333; font-weight: 600;">Nilai Maksimal:</label>
                        <!-- DEBUG: {{ json_encode($field) }} -->
                        <input type="number" class="form-control criteria-max" value="{{ isset($field['max']) ? $field['max'] : 30 }}" min="1" max="100" style="border: 2px solid #e0e0e0; width: 90px; padding: 0.5rem; border-radius: 8px; text-align: center; font-size: 1rem; font-weight: 600; color: #4caf50;">
                        <span style="font-size: 0.95rem; color: #666; font-weight: 500;">poin</span>
                    </div>
                    <div style="margin-left: auto; display: flex; align-items: center; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 2px solid #ff9800;">
                        <label style="margin: 0; font-size: 0.95rem; color: #333; margin-right: 0.75rem; font-weight: 600;">Wajib Diisi</label>
                        <input type="checkbox" class="criteria-required" {{ $field['required'] ?? false ? 'checked' : '' }} style="width: 22px; height: 22px; cursor: pointer; accent-color: #ff9800;">
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        <button type="button" id="addCriteria" class="btn-premium btn-premium-success" style="margin-bottom: 2rem; padding: 1rem 2rem; border-radius: 8px; font-size: 1.05rem; border: none; display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 1.5rem;">➕</span> Tambah Kriteria Baru
        </button>

        <form method="POST" action="{{ route('events.jury-form.store', $event) }}" id="saveFormBtn" style="border-top: 3px solid #e0e0e0; padding-top: 2rem; margin-top: 1rem;">
            @csrf
            <input type="hidden" name="criteria_fields" id="criteriaFieldsData">
            <input type="hidden" name="editing_template" id="editingTemplateFlag" value="false">
            <input type="hidden" name="template_id" id="templateIdInput" value="">
            @if($existingForm)
            <input type="hidden" name="form_template_id" id="formTemplateId" value="{{ $existingForm->id }}">
            @endif
            <!-- Hidden field untuk nama form - akan diisi otomatis dari template atau default -->
            <input type="hidden" name="name" id="form_name" value="{{ $existingForm->name ?? '' }}">

            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer; padding: 1.25rem; background: #eff6ff; border-radius: 8px; transition: all 0.2s; border: 2px solid #e5e7eb;">
                    <input type="checkbox" name="use_default_score" id="use_default_score" value="1" {{ $existingForm && $existingForm->use_default_score ? 'checked' : 'checked' }} style="width: 24px; height: 24px; cursor: pointer; accent-color: #2196f3;">
                    <div>
                        <span style="font-weight: 600; color: #1976d2; font-size: 1.05rem;">🧮 Hitung Total Poin Otomatis</span>
                        <p style="margin: 0.25rem 0 0 0; color: #666; font-size: 0.9rem;">Sistem akan menjumlahkan semua nilai poin kriteria secara otomatis</p>
                    </div>
                </label>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('events.jury-form.create', $event) }}" class="btn btn-secondary" style="padding: 1rem 2rem; border-radius: 12px; font-weight: 600; font-size: 1.05rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; background: #e0e0e0; border: none; color: #666;">
                    ❌ Batal
                </a>
                <button type="submit" class="btn-premium btn-premium-primary" style="font-size: 1.05rem; padding: 1rem 2.5rem; border-radius: 8px; border: none; display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 1.5rem;">💾</span> Simpan Form Penilaian
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
            <span>Mengalihkan ke halaman template...</span>
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
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

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

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    setTimeout(function() {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.style.animation = 'fadeOut 0.3s';
            setTimeout(function() {
                window.location.href = "{{ route('events.jury-form.create', $event) }}";
            }, 300);
        }
    }, 2000);
</script>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let criteriaIndex = {{ $existingForm && $existingForm->criteria_fields ? count($existingForm->criteria_fields) : 0 }};

        @if($existingForm && $existingForm->criteria_fields)
        console.log('Existing form data loaded:');
        console.log(@json($existingForm->criteria_fields));
        @endif

        // Check if there's template preview data in localStorage
        const previewCriteria = localStorage.getItem('jury_template_preview_criteria');
        const templateName = localStorage.getItem('jury_template_preview_name');
        const isEditingTemplate = localStorage.getItem('editing_template') === 'true';
        const templateId = localStorage.getItem('editing_template_id');
        
        if (previewCriteria) {
            try {
                const criteria = JSON.parse(previewCriteria);
                localStorage.removeItem('jury_template_preview_criteria');
                localStorage.removeItem('editing_template'); // Clear flag setelah dibaca
                
                // Set template ID jika ada
                if (templateId) {
                    document.getElementById('templateIdInput').value = templateId;
                    localStorage.removeItem('editing_template_id');
                }
                
                // Clear existing and load template
                const container = document.getElementById('criteriaContainer');
                if (container && criteria.length > 0) {
                    container.innerHTML = '';
                    criteriaIndex = 0;
                    
                    criteria.forEach((criterion) => {
                        loadCriterionFromTemplate(criterion);
                        criteriaIndex++;
                    });
                    
                    // Show template name if available
                    if (templateName) {
                        document.getElementById('templateNameDisplay').style.display = 'block';
                        document.getElementById('templateNameText').textContent = templateName;
                        
                        // Auto-fill form name from template name
                        const formNameInput = document.getElementById('form_name');
                        if (formNameInput && templateName) {
                            formNameInput.value = templateName;
                        }
                        
                        localStorage.removeItem('jury_template_preview_name');
                    }
                    
                    // Set flag untuk menandakan sedang edit template
                    if (isEditingTemplate) {
                        document.getElementById('editingTemplateFlag').value = 'true';
                    }
                    
                    // Show notification
                    const notification = document.createElement('div');
                    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #4caf50; color: white; padding: 1rem 1.5rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.3);';
                    notification.innerHTML = '<strong>✅ Template berhasil dimuat!</strong><br>Anda bisa edit kriteria sebelum menyimpan.';
                    document.body.appendChild(notification);
                    setTimeout(() => notification.remove(), 4000);
                }
            } catch (e) {
                console.error('Error loading template:', e);
                localStorage.removeItem('jury_template_preview_criteria');
                localStorage.removeItem('jury_template_preview_name');
            }
        }

        document.getElementById('addCriteria').addEventListener('click', function() {
            const container = document.getElementById('criteriaContainer');
            const criteriaItem = document.createElement('div');
            criteriaItem.className = 'criteria-item';
            criteriaItem.setAttribute('data-index', criteriaIndex);
            criteriaItem.setAttribute('draggable', 'true');
            
            criteriaItem.style.cssText = 'border: 2px solid #e0e0e0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move; animation: slideIn 0.3s;';

            criteriaItem.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                    <span class="drag-handle" style="font-size: 1.5rem; color: #9e9e9e; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                    <input type="text" class="form-control criteria-label" style="border: none; border-bottom: 2px solid #e0e0e0; font-size: 1.15rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #333;" placeholder="Nama Kriteria (mis: Teknik Vokal)" required>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <button type="button" class="btn btn-sm" onclick="duplicateCriteria(this)" title="Duplikat Kriteria" style="background: #e3f2fd; border: none; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px; transition: all 0.2s;">📋</button>
                    <button type="button" class="btn btn-sm btn-danger remove-criteria" title="Hapus Kriteria" style="background: #ffebee; border: none; color: #c62828; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px; transition: all 0.2s;">🗑️</button>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 1.1rem; color: #5f6368;">📊</span>
                    <select class="form-control criteria-type" style="border: 2px solid #e0e0e0; background: white; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.95rem; color: #333; cursor: pointer; font-weight: 500;">
                        <option value="score">⭐ Nilai Poin</option>
                        <option value="text">📝 Teks Pendek</option>
                        <option value="textarea">📄 Teks Panjang</option>
                    </select>
                </div>
                <div class="max-score-field" style="display: flex; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 2px solid #4caf50;">
                    <label style="margin: 0; font-size: 0.95rem; color: #333; font-weight: 600;">Nilai Maksimal:</label>
                    <input type="number" class="form-control criteria-max" value="30" min="1" max="100" style="border: 2px solid #e0e0e0; width: 90px; padding: 0.5rem; border-radius: 8px; text-align: center; font-size: 1rem; font-weight: 600; color: #4caf50;">
                    <span style="font-size: 0.95rem; color: #666; font-weight: 500;">poin</span>
                </div>
                <div style="margin-left: auto; display: flex; align-items: center; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 2px solid #ff9800;">
                    <label style="margin: 0; font-size: 0.95rem; color: #333; margin-right: 0.75rem; font-weight: 600;">Wajib Diisi</label>
                    <input type="checkbox" class="criteria-required" style="width: 22px; height: 22px; cursor: pointer; accent-color: #ff9800;">
                </div>
            </div>
        `;

            container.appendChild(criteriaItem);
            enableDragAndDrop(criteriaItem);
            criteriaIndex++;

            // Add slide in animation
            criteriaItem.style.animation = 'slideIn 0.3s';
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
                document.querySelectorAll('.criteria-item').forEach(item => {
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

                const draggedElement = document.querySelector('.criteria-item[style*="opacity: 0.4"]');
                if (draggedElement && draggedElement !== this) {
                    const container = document.getElementById('criteriaContainer');
                    const allItems = Array.from(container.querySelectorAll('.criteria-item'));
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
        document.querySelectorAll('.criteria-item').forEach(item => {
            enableDragAndDrop(item);
        });

        // Event delegation for remove button
        document.getElementById('criteriaContainer').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-criteria') || e.target.closest('.remove-criteria')) {
                const criteriaItem = e.target.closest('.criteria-item');
                if (confirm('Yakin ingin menghapus kriteria ini?')) {
                    criteriaItem.style.transition = 'all 0.3s';
                    criteriaItem.style.opacity = '0';
                    criteriaItem.style.transform = 'scale(0.8)';
                    setTimeout(() => criteriaItem.remove(), 300);
                }
            }
        });

        // Event delegation for type change
        document.getElementById('criteriaContainer').addEventListener('change', function(e) {
            if (e.target.classList.contains('criteria-type')) {
                const criteriaItem = e.target.closest('.criteria-item');
                const maxScoreField = criteriaItem.querySelector('.max-score-field');
                maxScoreField.style.display = e.target.value === 'score' ? 'flex' : 'none';
            }
        });

        // Load criterion from template
        function loadCriterionFromTemplate(criterion) {
            const container = document.getElementById('criteriaContainer');
            const criteriaItem = document.createElement('div');
            criteriaItem.className = 'criteria-item';
            criteriaItem.setAttribute('data-index', criteriaIndex);
            criteriaItem.setAttribute('draggable', 'true');
            criteriaItem.style.cssText = 'border: 2px solid #e0e0e0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move; animation: slideIn 0.3s;';

            criteriaItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                        <span class="drag-handle" style="font-size: 1.5rem; color: #9e9e9e; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                        <input type="text" class="form-control criteria-label" value="${criterion.label || ''}" style="border: none; border-bottom: 2px solid #e0e0e0; font-size: 1.15rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #333;" placeholder="Nama Kriteria (mis: Teknik Vokal)" required>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="button" class="btn btn-sm" onclick="duplicateCriteria(this)" title="Duplikat Kriteria" style="background: #e3f2fd; border: none; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px;">📋</button>
                        <button type="button" class="btn btn-sm btn-danger remove-criteria" title="Hapus Kriteria" style="background: #ffebee; border: none; color: #c62828; font-size: 1.25rem; padding: 0.5rem 0.75rem; cursor: pointer; border-radius: 8px;">🗑️</button>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555;">Deskripsi (Opsional)</label>
                    <textarea class="form-control criteria-description" rows="2" style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 0.75rem; font-size: 0.95rem; resize: vertical;" placeholder="Tambahkan penjelasan atau panduan untuk kriteria ini...">${criterion.description || ''}</textarea>
                </div>
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555;">Tipe Kriteria</label>
                        <select class="form-control criteria-type" style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 0.75rem; font-size: 1rem;" onchange="toggleMaxScoreField(this)">
                            <option value="score" ${criterion.type === 'score' ? 'selected' : ''}>Skor (0 - max)</option>
                            <option value="textarea" ${criterion.type === 'textarea' ? 'selected' : ''}>Komentar/Feedback</option>
                        </select>
                    </div>
                    <div class="max-score-field" style="flex: 1; min-width: 150px; display: ${criterion.type === 'score' ? 'block' : 'none'};">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555;">Nilai Maksimal</label>
                        <input type="number" class="form-control criteria-max" value="${criterion.max || 10}" min="1" max="1000" style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 0.75rem; font-size: 1rem;" placeholder="10">
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: white; border-radius: 8px; border: 2px solid #e0e0e0;">
                        <input type="checkbox" class="criteria-required" ${criterion.required ? 'checked' : ''} style="width: 20px; height: 20px; cursor: pointer;">
                        <label style="margin: 0; font-weight: 600; color: #555; cursor: pointer;">Wajib diisi</label>
                    </div>
                </div>
            `;

            container.appendChild(criteriaItem);
            enableDragAndDrop(criteriaItem);
        }

        // Duplicate criteria function
        window.duplicateCriteria = function(button) {
            const originalItem = button.closest('.criteria-item');
            const newItem = originalItem.cloneNode(true);

            // Update data-index
            newItem.setAttribute('data-index', criteriaIndex);
            criteriaIndex++;

            // Insert after original
            originalItem.parentNode.insertBefore(newItem, originalItem.nextSibling);

            // Enable drag and drop for cloned item
            enableDragAndDrop(newItem);

            // Add animation
            newItem.style.animation = 'slideIn 0.3s';
        };

        // DEBUG: Log existing form data on page load
        console.log('=== CUSTOM BUILDER PAGE LOAD ===');
        @if($existingForm && $existingForm->criteria_fields)
        const existingFormData = @json($existingForm->criteria_fields);
        console.log('Existing form data from server:', existingFormData);
        console.log('Number of criteria:', existingFormData.length);
        @else
        console.log('No existing form data found');
        @endif
        console.log('=== END DEBUG ===');

        // Save form
        console.log('=== ATTACHING FORM SUBMIT LISTENER ===');
        const formElement = document.getElementById('saveFormBtn');
        console.log('Form element found:', formElement);

        formElement.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMIT EVENT TRIGGERED ===');
            e.preventDefault();

            const criteria = [];
            const criteriaItems = document.querySelectorAll('.criteria-item');
            console.log('Found criteria items:', criteriaItems.length);

            let hasError = false;

            criteriaItems.forEach(function(item) {
                const labelInput = item.querySelector('.criteria-label');
                const label = labelInput ? labelInput.value : '';
                const description = item.querySelector('.criteria-description') ? item.querySelector('.criteria-description').value : '';
                const type = item.querySelector('.criteria-type').value;
                const maxInput = item.querySelector('.criteria-max');
                const max = maxInput ? maxInput.value : 30;
                const required = item.querySelector('.criteria-required').checked;

                console.log('=== Processing Criteria Item ===');
                console.log('Label Input Element:', labelInput);
                console.log('Label Value:', label);
                console.log('Description:', description);
                console.log('Type:', type, 'Max:', max, 'Required:', required);

                if (label.trim() === '') {
                    alert('❌ Semua kriteria harus memiliki nama!');
                    item.querySelector('.criteria-label').focus();
                    hasError = true;
                    return;
                }

                const criteriaData = {
                    label: label,
                    description: description,
                    type: type,
                    required: required
                };

                if (type === 'score') {
                    const maxValue = parseInt(max) || 30;
                    criteriaData.max = maxValue;
                    console.log('Score criteria - Max value:', maxValue);
                }

                criteria.push(criteriaData);
            });

            if (hasError) {
                return;
            }

            if (criteria.length === 0) {
                alert('❌ Minimal harus ada 1 kriteria penilaian!');
                return;
            }

            console.log('=== FINAL DATA CHECK ===');
            console.log('Total criteria collected:', criteria.length);
            console.log('Criteria data:', criteria);
            console.log('JSON string:', JSON.stringify(criteria));

            document.getElementById('criteriaFieldsData').value = JSON.stringify(criteria);
            console.log('Hidden input value set:', document.getElementById('criteriaFieldsData').value);

            this.submit();
        });

        document.getElementById('addCriteria').addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 16px rgba(76, 175, 80, 0.4)';
        });
        document.getElementById('addCriteria').addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px rgba(76, 175, 80, 0.3)';
        });
    }); // Close DOMContentLoaded
</script>
@endpush
@endsection