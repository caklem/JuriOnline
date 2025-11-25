@extends('layouts.app')

@section('title', 'Kelola Assignment Form Juri - ' . $event->name)

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }

    .page-header {
        background: #2563eb;
        color: white;
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .page-header h1 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
        font-weight: 700;
    }

    .page-header p {
        margin: 0;
        opacity: 0.95;
        font-size: 1.1rem;
    }

    .back-link {
        color: #667eea;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .back-link:hover {
        color: #764ba2;
        transform: translateX(-4px);
    }

    .alert {
        padding: 1.25rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border-left: 4px solid;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: #f0fdf4;
        color: #166534;
        border-color: #16a34a;
    }

    .alert-error {
        background: #fef2f2;
        color: #991b1b;
        border-color: #dc2626;
    }

    .alert-warning {
        background: #fffbeb;
        color: #854d0e;
        border-color: #f59e0b;
    }

    .info-card {
        background: #eff6ff;
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        border-left: 4px solid #2563eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .info-card h3 {
        margin: 0 0 1rem 0;
        color: #1976d2;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .info-card p {
        margin: 0;
        color: #2c3e50;
        line-height: 1.7;
    }

    .btn-primary {
        background: #16a34a;
        color: white;
        padding: 0.875rem 1.75rem;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: all 0.2s;
        font-size: 1rem;
    }

    .btn-primary:hover {
        background: #15803d;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-secondary {
        background: #2563eb;
        color: white;
        padding: 0.875rem 1.75rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: #1d4ed8;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        color: white;
    }

    .form-card {
        background: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        border: 2px solid #e8eaf6;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }

    .form-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 3px solid #e8eaf6;
    }

    .form-title {
        margin: 0;
        font-size: 1.75rem;
        color: #2c3e50;
        font-weight: 700;
    }

    .form-meta {
        margin: 0.5rem 0 0 0;
        color: #7f8c8d;
        font-size: 0.95rem;
    }

    .badge {
        background: #2563eb;
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .jury-checkbox-item {
        padding: 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        margin-bottom: 0.75rem;
        transition: all 0.3s;
        cursor: pointer;
        background: white;
    }

    .jury-checkbox-item:hover {
        border-color: #2563eb;
        background: #eff6ff;
        transform: translateX(4px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .jury-checkbox-item:has(input[type="checkbox"]:checked) {
        border-color: #2563eb;
        background: #eff6ff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .jury-checkbox-item input[type="checkbox"] {
        width: 22px;
        height: 22px;
        cursor: pointer;
        margin-right: 1rem;
        accent-color: #667eea;
    }

    .jury-checkbox-item label {
        cursor: pointer;
        margin: 0;
        display: flex;
        align-items: center;
        flex: 1;
    }

    .jury-checkbox-item label span:first-child {
        font-weight: 600;
        color: #2c3e50;
        font-size: 1rem;
    }

    .jury-checkbox-item label span:last-child {
        color: #7f8c8d;
        margin-left: 0.75rem;
        font-size: 0.9rem;
    }

    .btn-group {
        display: flex;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-sm:hover {
        transform: translateY(-1px);
    }

    .btn-select-all {
        background: #e3f2fd;
        color: #1976d2;
        border-color: #2196f3;
    }

    .btn-deselect {
        background: #fff3e0;
        color: #e65100;
        border-color: #ff9800;
    }

    .assigned-juries-section {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 3px solid #e8eaf6;
    }

    .assigned-juries-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .jury-card {
        background: #f0fdf4;
        padding: 1.25rem;
        border-radius: 8px;
        border-left: 4px solid #16a34a;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.2s;
    }

    .jury-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
    }

    .jury-card-name {
        font-weight: 700;
        color: #2c3e50;
        font-size: 1rem;
    }

    .jury-card-email {
        font-size: 0.85rem;
        color: #7f8c8d;
        margin-top: 0.25rem;
    }

    .btn-delete {
        background: #dc2626;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 2px 6px rgba(231, 76, 60, 0.3);
    }

    .btn-delete:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(231, 76, 60, 0.4);
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        max-width: 650px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: slideUp 0.3s;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #e8eaf6;
    }

    .modal-header h2 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.75rem;
        font-weight: 700;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 2rem;
        cursor: pointer;
        color: #999;
        padding: 0;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s;
    }

    .modal-close:hover {
        background: #f5f5f5;
        color: #333;
    }

    .form-group {
        margin-bottom: 1.75rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 700;
        color: #2c3e50;
        font-size: 1.05rem;
    }

    .form-control {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-help {
        color: #7f8c8d;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    .template-preview {
        margin-top: 1rem;
        padding: 1.25rem;
        background: #ffffff;
        border-radius: 12px;
        border-left: 4px solid #667eea;
    }

    .template-preview strong {
        display: block;
        margin-bottom: 0.75rem;
        color: #2c3e50;
    }

    .template-preview ul {
        margin: 0;
        padding-left: 1.5rem;
        color: #555;
    }

    .template-preview li {
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }

    .jury-list {
        max-height: 250px;
        overflow-y: auto;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 1rem;
        background: white;
    }

    .jury-list-item {
        padding: 0.75rem;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s;
    }

    .jury-list-item:last-child {
        border-bottom: none;
    }

    .jury-list-item:hover {
        background: #f8f9fa;
        border-radius: 8px;
    }

    .jury-list-item label {
        display: flex;
        align-items: center;
        cursor: pointer;
        margin: 0;
    }

    .jury-list-item input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: 0.75rem;
        cursor: pointer;
        accent-color: #667eea;
    }

    .modal-footer {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 2px solid #e8eaf6;
    }

    .btn-cancel {
        background: #e0e0e0;
        color: #666;
        padding: 0.875rem 1.75rem;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-cancel:hover {
        background: #d0d0d0;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        background: #ffffff;
        border-radius: 16px;
        border: 2px dashed #dee2e6;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #7f8c8d;
        margin: 0;
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #764ba2;
    }
</style>
@endpush

@section('content')
<div class="card" style="padding: 0; background: transparent; box-shadow: none;">
    <a href="{{ route('events.show', $event) }}" class="back-link">
        <span>←</span>
        <span>Kembali ke Detail Event</span>
    </a>

    <div class="page-header">
        <h1>📋 Kelola Assignment Form Juri</h1>
        <p>Event: <strong>{{ $event->name }}</strong></p>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
    @endif

    @if($formTemplates->isEmpty())
    <div class="alert alert-warning">
        <strong>⚠️ Belum ada form penilaian!</strong><br>
        Gunakan fitur <strong>"⚡ Quick: Form + Assign"</strong> di bawah untuk membuat form dan assign juri sekaligus, atau buat form terlebih dahulu di <a href="{{ route('events.jury-form.create', $event) }}" style="color: #856404; font-weight: bold; text-decoration: underline;">Kelola Form Penilaian</a>
    </div>
    @endif

    <!-- Info Card -->
    @if($formTemplates->isEmpty())
    <div class="info-card">
        <h3>⚡ Quick Create: Form + Assign Juri</h3>
        <p style="margin-bottom: 1.5rem;">
            Buat form baru dan assign juri sekaligus dalam satu langkah. Pilih template kriteria, beri nama form, pilih juri, lalu klik "Buat Form & Assign Juri".
        </p>
        <button type="button" onclick="showQuickCreateFormModal()" class="btn-primary">
            ⚡ Quick: Form + Assign
        </button>
    </div>
    @else
    <div class="info-card">
        <h3>💡 Cara Menambahkan Juri ke-2 dengan Form Berbeda</h3>
        <div style="background: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <p style="margin: 0 0 0.75rem 0; font-weight: 700; color: #2c3e50;">
                📝 Langkah-langkah:
            </p>
            <ol style="margin: 0; padding-left: 1.5rem; color: #2c3e50; line-height: 2;">
                <li>Klik tombol <strong>"⚡ Quick: Form + Assign"</strong> untuk membuat form baru dan assign juri sekaligus</li>
                <li>Atau klik <strong>"➕ Buat Form Baru"</strong> untuk membuat form dengan kriteria custom</li>
                <li>Form baru akan muncul sebagai card terpisah di bawah</li>
                <li>Pilih juri di form yang baru, lalu klik <strong>"Assign Juri Terpilih ke Form Ini"</strong></li>
            </ol>
        </div>
        <p style="margin: 0;">
            ✅ <strong>Hasil:</strong> Juri 1 menggunakan <strong>Form Juri Vokal</strong>, Juri 2 menggunakan <strong>Form Juri Adab</strong>, dll.
        </p>
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
            <button type="button" onclick="showQuickCreateFormModal()" class="btn-primary">
                ⚡ Quick: Form + Assign
            </button>
            <a href="{{ route('events.jury-form.create', $event) }}" class="btn-secondary">
                ➕ Buat Form Baru
            </a>
        </div>
    </div>
    @endif

    <!-- Quick Create Form Modal -->
    <div id="quickCreateFormModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>⚡ Quick Create: Form Baru + Assign Juri</h2>
                <button type="button" onclick="closeQuickCreateFormModal()" class="modal-close">&times;</button>
            </div>
            
            <form method="POST" action="{{ route('events.jury-assignments.quick-create', $event) }}" id="quickCreateForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">📋 Nama Form <span style="color: #e74c3c;">*</span></label>
                    <input type="text" name="form_name" required placeholder="Contoh: Form Juri Vokal, Form Juri Adab, dll" class="form-control">
                    <span class="form-help">Berikan nama yang jelas untuk membedakan form</span>
                </div>

                <div class="form-group">
                    <label class="form-label">📊 Pilih Template Kriteria <span style="color: #e74c3c;">*</span></label>
                    <select name="template_key" id="templateSelect" required class="form-control" onchange="updateTemplatePreview()">
                        <option value="">-- Pilih Template --</option>
                        @php
                            $templates = \App\Models\JuryFormTemplate::getTemplates();
                        @endphp
                        @foreach($templates as $key => $template)
                        <option value="{{ $key }}" data-fields="{{ json_encode($template['fields']) }}" data-name="{{ $template['name'] }}">
                            {{ $template['name'] }}
                        </option>
                        @endforeach
                    </select>
                    <div id="templatePreview" class="template-preview" style="display: none;">
                        <strong>Preview Kriteria:</strong>
                        <ul id="templatePreviewList"></ul>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">👥 Pilih Juri untuk Di-assign <span style="color: #e74c3c;">*</span></label>
                    <div class="jury-list">
                        @foreach($juries as $jury)
                        <div class="jury-list-item">
                            <label>
                                <input type="checkbox" name="jury_ids[]" value="{{ $jury->id }}">
                                <span style="font-weight: 600; color: #2c3e50;">{{ $jury->name }}</span>
                                <span style="color: #7f8c8d; margin-left: 0.5rem;">({{ $jury->email }})</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <div class="btn-group">
                        <button type="button" onclick="selectAllQuickJuries()" class="btn-sm btn-select-all">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="deselectAllQuickJuries()" class="btn-sm btn-deselect">
                            Batal Pilih
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeQuickCreateFormModal()" class="btn-cancel">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary">
                        ✅ Buat Form & Assign Juri
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form Cards -->
    @if($formTemplates->isNotEmpty())
    @foreach($formTemplates as $formTemplate)
    @php
        $assignedJuries = $assignments->filter(function($assignment) use ($formTemplate) {
            return $assignment->jury_form_template_id == $formTemplate->id;
        })->pluck('jury_id')->toArray();
        $unassignedJuries = $juries->filter(function($jury) use ($assignedJuries) {
            return !in_array($jury->id, $assignedJuries);
        });
        $criteriaCount = is_array($formTemplate->criteria_fields) ? count(array_filter($formTemplate->criteria_fields, fn($f) => $f['type'] === 'score')) : 0;
    @endphp
    
    <div class="form-card">
        <div class="form-header">
            <div>
                <h2 class="form-title">📋 {{ $formTemplate->name ?? 'Form Juri' }}</h2>
                <p class="form-meta">{{ $criteriaCount }} kriteria penilaian</p>
            </div>
            <span class="badge">{{ count($assignedJuries) }} Juri Ter-assign</span>
        </div>

        @if($unassignedJuries->isEmpty() && count($assignedJuries) > 0)
        <div class="alert alert-success" style="margin-bottom: 0;">
            <p style="margin: 0;">✅ Semua juri sudah di-assign ke form ini.</p>
        </div>
        @elseif($unassignedJuries->isEmpty())
        <div class="alert alert-warning" style="margin-bottom: 0;">
            <p style="margin: 0;">⚠️ Tidak ada juri yang tersedia untuk di-assign.</p>
        </div>
        @else
        <form method="POST" action="{{ route('events.jury-assignments.store', $event) }}" id="assignForm{{ $formTemplate->id }}">
            @csrf
            <input type="hidden" name="jury_form_template_id" value="{{ $formTemplate->id }}">
            
            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <label class="form-label" style="margin: 0;">Pilih Juri untuk Form Ini:</label>
                    <div class="btn-group" style="margin: 0;">
                        <button type="button" onclick="selectAllJuries({{ $formTemplate->id }})" class="btn-sm btn-select-all">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="deselectAllJuries({{ $formTemplate->id }})" class="btn-sm btn-deselect">
                            Batal Pilih
                        </button>
                    </div>
                </div>
                
                <div style="max-height: 350px; overflow-y: auto; border: 2px solid #e0e0e0; border-radius: 12px; padding: 1.25rem; background: white;">
                    @foreach($unassignedJuries as $jury)
                    <div class="jury-checkbox-item">
                        <input type="checkbox" 
                               name="jury_ids[]" 
                               id="jury_{{ $formTemplate->id }}_{{ $jury->id }}" 
                               value="{{ $jury->id }}">
                        <label for="jury_{{ $formTemplate->id }}_{{ $jury->id }}">
                            <span>{{ $jury->name }}</span>
                            <span>({{ $jury->email }})</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary">
                    ✅ Assign Juri Terpilih ke Form Ini
                </button>
            </div>
        </form>
        @endif

        <!-- Daftar Juri yang sudah di-assign -->
        @if(count($assignedJuries) > 0)
        <div class="assigned-juries-section">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: #27ae60;">
                ✅ Juri yang Sudah Di-assign ({{ count($assignedJuries) }})
            </h3>
            <div class="assigned-juries-grid">
                @foreach($assignments->filter(function($assignment) use ($formTemplate) {
                    return $assignment->jury_form_template_id == $formTemplate->id;
                }) as $assignment)
                <div class="jury-card">
                    <div>
                        <div class="jury-card-name">{{ $assignment->jury->name }}</div>
                        <div class="jury-card-email">{{ $assignment->jury->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('events.jury-assignments.destroy', [$event, $assignment]) }}" style="display: inline;" onsubmit="return confirm('Hapus assignment untuk {{ $assignment->jury->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">
                            ✕ Hapus
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
    @endif
</div>

@push('scripts')
<script>
    function selectAllJuries(formId) {
        const form = document.getElementById('assignForm' + formId);
        const checkboxes = form.querySelectorAll('input[type="checkbox"][name="jury_ids[]"]');
        checkboxes.forEach(cb => cb.checked = true);
    }

    function deselectAllJuries(formId) {
        const form = document.getElementById('assignForm' + formId);
        const checkboxes = form.querySelectorAll('input[type="checkbox"][name="jury_ids[]"]');
        checkboxes.forEach(cb => cb.checked = false);
    }

    document.querySelectorAll('form[id^="assignForm"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const checkboxes = this.querySelectorAll('input[type="checkbox"][name="jury_ids[]"]:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('⚠️ Silakan pilih minimal 1 juri untuk di-assign!');
                return false;
            }
        });
    });

    function showQuickCreateFormModal() {
        document.getElementById('quickCreateFormModal').style.display = 'flex';
    }

    function closeQuickCreateFormModal() {
        document.getElementById('quickCreateFormModal').style.display = 'none';
        document.getElementById('quickCreateForm').reset();
        document.getElementById('templatePreview').style.display = 'none';
    }

    function updateTemplatePreview() {
        const select = document.getElementById('templateSelect');
        const selectedOption = select.options[select.selectedIndex];
        const previewDiv = document.getElementById('templatePreview');
        const previewList = document.getElementById('templatePreviewList');

        if (selectedOption.value) {
            const fields = JSON.parse(selectedOption.getAttribute('data-fields'));
            previewList.innerHTML = '';
            fields.forEach(field => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${field.label}</strong> ${field.type === 'score' ? `(Max: ${field.max} poin)` : ''}`;
                previewList.appendChild(li);
            });
            previewDiv.style.display = 'block';
        } else {
            previewDiv.style.display = 'none';
        }
    }

    function selectAllQuickJuries() {
        const checkboxes = document.querySelectorAll('#quickCreateFormModal input[type="checkbox"][name="jury_ids[]"]');
        checkboxes.forEach(cb => cb.checked = true);
    }

    function deselectAllQuickJuries() {
        const checkboxes = document.querySelectorAll('#quickCreateFormModal input[type="checkbox"][name="jury_ids[]"]');
        checkboxes.forEach(cb => cb.checked = false);
    }

    document.getElementById('quickCreateFormModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeQuickCreateFormModal();
        }
    });

    document.getElementById('quickCreateForm')?.addEventListener('submit', function(e) {
        const checkboxes = this.querySelectorAll('input[type="checkbox"][name="jury_ids[]"]:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            alert('⚠️ Silakan pilih minimal 1 juri untuk di-assign!');
            return false;
        }
    });
</script>
@endpush
@endsection
