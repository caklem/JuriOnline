@extends('layouts.app')

@section('title', 'Buat Form Penilaian Custom')

@section('content')
<div class="card">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('events.jury-form.create', $event) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-indigo-600">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0 6-6m-6 6 6 6" />
            </svg>
            Kembali ke Kelola Form
        </a>
    </div>

    <div class="mt-4">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $existingForm ? 'Edit Form Penilaian' : 'Buat Form Penilaian Baru' }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $event->name }}</p>
        <p class="mt-2 text-sm text-slate-500">
            {{ $existingForm ? 'Perbarui kriteria, nama, atau bobot nilai untuk form ini.' : 'Buat kriteria penilaian sesuai kebutuhan event Anda dengan drag & drop. Satu event boleh memiliki beberapa form juri.' }}
        </p>
    </div>

    @if($existingForm)
    <div class="alert alert-info mt-5">
        <strong class="font-semibold">Mode Edit:</strong> Form penilaian sudah ada. Anda dapat mengedit kriteria di bawah ini.
    </div>
    @endif
</div>

<!-- Custom Form Builder -->
<div class="card">
    <div class="mb-6 rounded-xl bg-slate-900 px-6 py-5 text-white">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <h3 class="flex items-center gap-2 text-lg font-semibold">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5 text-indigo-300" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                </svg>
                Kriteria Penilaian
            </h3>
            <div id="templateNameDisplay" style="background: rgba(255,255,255,0.12); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; display: none;">
                <strong class="font-semibold">Template:</strong> <span id="templateNameText"></span>
            </div>
        </div>
        <p class="mt-2 text-sm text-slate-300">Drag & drop untuk mengubah urutan kriteria</p>
    </div>

    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
        <label class="form-label" for="form_name">Nama Form Penilaian <span class="text-rose-600">*</span></label>
        <input type="text" name="name" id="form_name" form="saveFormBtn" class="form-control" required maxlength="255"
               value="{{ $existingForm->name ?? old('name') }}"
               placeholder="Contoh: Form Juri Vokal, Form Juri Adab, Form Juri Terbang">
        <span class="form-help">Nama ini dipakai untuk membedakan form saat assign juri. Wajib diisi.</span>
    </div>

    <div id="formBuilder">
        <div id="criteriaContainer">
            @if($existingForm && $existingForm->criteria_fields)
            @foreach($existingForm->criteria_fields as $index => $field)
            <div class="criteria-item" data-index="{{ $index }}" draggable="true" style="border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 1px 2px rgba(15,23,42,0.04); transition: border-color 0.2s, box-shadow 0.2s; cursor: move;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                        <span class="drag-handle" style="font-size: 1.5rem; color: #94a3b8; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                        <input type="text" class="form-control criteria-label" value="{{ $field['label'] }}" style="border: none; border-bottom: 1px solid #e2e8f0; font-size: 1.125rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #0f172a;" placeholder="Nama Kriteria (mis: Teknik Vokal)" required>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="duplicateCriteria(this)" title="Duplikat Kriteria" aria-label="Duplikat Kriteria" style="padding: 0.4rem 0.6rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2M6 8h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" /></svg>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger remove-criteria" title="Hapus Kriteria" aria-label="Hapus Kriteria" style="padding: 0.4rem 0.6rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" /></svg>
                        </button>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <textarea class="form-control criteria-description" rows="2" placeholder="Deskripsi kriteria (opsional) - Jelaskan apa yang dinilai dari kriteria ini">{{ $field['description'] ?? '' }}</textarea>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; background: #f8fafc; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <select class="form-control criteria-type" style="cursor: pointer; font-weight: 500;">
                            <option value="score" {{ $field['type'] === 'score' ? 'selected' : '' }}>Nilai Poin</option>
                            <option value="text" {{ $field['type'] === 'text' ? 'selected' : '' }}>Teks Pendek</option>
                            <option value="textarea" {{ $field['type'] === 'textarea' ? 'selected' : '' }}>Teks Panjang</option>
                        </select>
                    </div>
                    <div class="max-score-field" style="display: {{ $field['type'] === 'score' ? 'flex' : 'none' }}; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 0.875rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <label style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600; white-space: nowrap;">Nilai Maksimal:</label>
                        <!-- DEBUG: {{ json_encode($field) }} -->
                        <input type="number" class="form-control criteria-max" value="{{ isset($field['max']) ? $field['max'] : 30 }}" min="1" max="100" style="width: 90px; text-align: center; font-weight: 600; color: #4f46e5;">
                        <span style="font-size: 0.875rem; color: #64748b; font-weight: 500;">poin</span>
                    </div>
                    <div style="margin-left: auto; display: flex; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 0.875rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <label style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600;">Wajib Diisi</label>
                        <input type="checkbox" class="criteria-required h-5 w-5 cursor-pointer accent-indigo-600" {{ $field['required'] ?? false ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        <button type="button" id="addCriteria" class="btn-premium btn-premium-success" style="margin-bottom: 2rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
            </svg>
            Tambah Kriteria Baru
        </button>

        <form method="POST" action="{{ route('events.jury-form.store', $event) }}" id="saveFormBtn" style="border-top: 1px solid #e2e8f0; padding-top: 2rem; margin-top: 1rem;">
            @csrf
            <input type="hidden" name="criteria_fields" id="criteriaFieldsData">
            <input type="hidden" name="editing_template" id="editingTemplateFlag" value="false">
            <input type="hidden" name="template_id" id="templateIdInput" value="">
            @if($existingForm)
            <input type="hidden" name="form_template_id" id="formTemplateId" value="{{ $existingForm->id }}">
            @endif

            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer; padding: 1.25rem; background: #eef2ff; border-radius: 12px; transition: border-color 0.2s; border: 1px solid #c7d2fe;">
                    <input type="checkbox" name="use_default_score" id="use_default_score" value="1" {{ $existingForm && $existingForm->use_default_score ? 'checked' : 'checked' }} style="width: 22px; height: 22px; cursor: pointer; accent-color: #4f46e5;">
                    <div>
                        <span style="font-weight: 600; color: #3730a3; font-size: 0.9375rem;">Hitung Total Poin Otomatis</span>
                        <p style="margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.875rem;">Sistem akan menjumlahkan semua nilai poin kriteria secara otomatis</p>
                    </div>
                </label>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('events.jury-form.create', $event) }}" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn-premium btn-premium-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13v6h14v-6M12 3v12m0 0 4-4m-4 4-4-4" />
                    </svg>
                    Simpan Form Penilaian
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
    function toggleMaxScoreField(select) {
        const criteriaItem = select.closest('.criteria-item');
        if (!criteriaItem) return;

        const maxScoreField = criteriaItem.querySelector('.max-score-field');
        if (maxScoreField) {
            maxScoreField.style.display = select.value === 'score' ? 'flex' : 'none';
        }
    }

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
                    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #059669; color: white; padding: 1rem 1.5rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.3);';
                    notification.innerHTML = '<strong>Template berhasil dimuat!</strong><br>Anda bisa edit kriteria sebelum menyimpan.';
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
            
            criteriaItem.style.cssText = 'border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move; animation: slideIn 0.3s;';

            criteriaItem.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                    <span class="drag-handle" style="font-size: 1.5rem; color: #94a3b8; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                    <input type="text" class="form-control criteria-label" style="border: none; border-bottom: 1px solid #e2e8f0; font-size: 1.15rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #0f172a;" placeholder="Nama Kriteria (mis: Teknik Vokal)" required>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="duplicateCriteria(this)" title="Duplikat Kriteria" aria-label="Duplikat Kriteria" style="padding: 0.4rem 0.6rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2M6 8h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" /></svg>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger remove-criteria" title="Hapus Kriteria" aria-label="Hapus Kriteria" style="padding: 0.4rem 0.6rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" /></svg>
                    </button>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; background: #f8fafc; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <select class="form-control criteria-type" style="cursor: pointer; font-weight: 500;">
                        <option value="score">Nilai Poin</option>
                        <option value="text">Teks Pendek</option>
                        <option value="textarea">Teks Panjang</option>
                    </select>
                </div>
                <div class="max-score-field" style="display: flex; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 0.875rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <label style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600; white-space: nowrap;">Nilai Maksimal:</label>
                    <input type="number" class="form-control criteria-max" value="30" min="1" max="100" style="width: 90px; text-align: center; font-weight: 600; color: #4f46e5;">
                    <span style="font-size: 0.875rem; color: #64748b; font-weight: 500;">poin</span>
                </div>
                <div style="margin-left: auto; display: flex; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 0.875rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <label style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600;">Wajib Diisi</label>
                    <input type="checkbox" class="criteria-required h-5 w-5 cursor-pointer accent-indigo-600">
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
            criteriaItem.style.cssText = 'border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: move; animation: slideIn 0.3s;';

            criteriaItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                        <span class="drag-handle" style="font-size: 1.5rem; color: #94a3b8; cursor: grab; padding: 0.25rem; user-select: none;">⋮⋮</span>
                        <input type="text" class="form-control criteria-label" value="${criterion.label || ''}" style="border: none; border-bottom: 1px solid #e2e8f0; font-size: 1.15rem; font-weight: 600; padding: 0.75rem 0; background: transparent; flex: 1; margin-right: 1rem; cursor: text; color: #0f172a;" placeholder="Nama Kriteria (mis: Teknik Vokal)" required>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="duplicateCriteria(this)" title="Duplikat Kriteria" aria-label="Duplikat Kriteria" style="padding: 0.4rem 0.6rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2M6 8h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" /></svg>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger remove-criteria" title="Hapus Kriteria" aria-label="Hapus Kriteria" style="padding: 0.4rem 0.6rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" /></svg>
                        </button>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">Deskripsi (Opsional)</label>
                    <textarea class="form-control criteria-description" rows="2" placeholder="Tambahkan penjelasan atau panduan untuk kriteria ini...">${criterion.description || ''}</textarea>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; background: #f8fafc; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #475569;">Tipe Kriteria</label>
                        <select class="form-control criteria-type" style="border-radius: 8px; padding: 0.625rem 0.875rem; font-size: 0.9375rem;" onchange="toggleMaxScoreField(this)">
                            <option value="score" ${criterion.type === 'score' ? 'selected' : ''}>Skor (0 - max)</option>
                            <option value="textarea" ${criterion.type === 'textarea' ? 'selected' : ''}>Komentar/Feedback</option>
                        </select>
                    </div>
                    <div class="max-score-field" style="flex: 1; min-width: 150px; display: ${criterion.type === 'score' ? 'block' : 'none'};">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #475569;">Nilai Maksimal</label>
                        <input type="number" class="form-control criteria-max" value="${criterion.max || 10}" min="1" max="1000" style="border-radius: 8px; padding: 0.625rem 0.875rem; font-size: 0.9375rem;" placeholder="10">
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.875rem; background: white; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <input type="checkbox" class="criteria-required h-5 w-5 cursor-pointer accent-indigo-600" ${criterion.required ? 'checked' : ''}>
                        <label style="margin: 0; font-weight: 600; color: #475569; cursor: pointer;">Wajib diisi</label>
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

            const nameInput = document.getElementById('form_name');
            if (!nameInput || nameInput.value.trim() === '') {
                alert('Nama form penilaian wajib diisi!');
                if (nameInput) nameInput.focus();
                return;
            }

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
                    alert('Semua kriteria harus memiliki nama!');
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
                alert('Minimal harus ada 1 kriteria penilaian!');
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
            this.style.boxShadow = '0 6px 16px rgba(79, 70, 229, 0.28)';
        });
        document.getElementById('addCriteria').addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px rgba(79, 70, 229, 0.22)';
        });
    }); // Close DOMContentLoaded
</script>
@endpush
@endsection