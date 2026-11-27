@extends('layouts.app')

@section('title', 'Kelola Assignment Form Juri - ' . $event->name)

@push('styles')
<style>
    /* Modal + interactive list styles only — the rest comes from the global design system */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .modal-content {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 2rem;
        width: 100%;
        max-width: 42rem;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 24px 64px rgba(15, 23, 42, 0.25);
    }

    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .modal-header h2 {
        margin: 0;
        color: #0f172a;
        font-size: 1.125rem;
        font-weight: 600;
        letter-spacing: -0.01em;
        line-height: 1.4;
    }

    .modal-close {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        padding: 0;
        border: none;
        border-radius: 9999px;
        background: transparent;
        color: #94a3b8;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
        transition: background-color 150ms ease, color 150ms ease;
    }

    .modal-close:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .jury-checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 0.875rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        margin-bottom: 0.5rem;
        background: #ffffff;
        transition: border-color 150ms ease, background-color 150ms ease;
    }

    .jury-checkbox-item:last-child {
        margin-bottom: 0;
    }

    .jury-checkbox-item:hover {
        border-color: #c7d2fe;
        background: #f8fafc;
    }

    .jury-checkbox-item:has(input[type="checkbox"]:checked) {
        border-color: #a5b4fc;
        background: #eef2ff;
    }

    .jury-checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        cursor: pointer;
        accent-color: #4f46e5;
    }

    .jury-checkbox-item label {
        display: flex;
        flex: 1;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin: 0;
        cursor: pointer;
    }

    .jury-list {
        max-height: 250px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.5rem;
        background: #ffffff;
    }

    .jury-list-item {
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        transition: background-color 150ms ease;
    }

    .jury-list-item:hover {
        background: #f8fafc;
    }

    .jury-list-item label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin: 0;
        cursor: pointer;
    }

    .jury-list-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        cursor: pointer;
        accent-color: #4f46e5;
    }

    .template-preview {
        margin-top: 0.75rem;
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 3px solid #4f46e5;
        border-radius: 0.75rem;
    }

    .template-preview strong {
        display: block;
        margin-bottom: 0.5rem;
        color: #0f172a;
        font-size: 0.875rem;
    }

    .template-preview ul {
        margin: 0;
        padding-left: 1.25rem;
        color: #475569;
        font-size: 0.875rem;
    }

    .template-preview li {
        margin-bottom: 0.35rem;
        line-height: 1.6;
    }

    .assigned-juries-section {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f1f5f9;
    }

    .assigned-juries-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 0.875rem;
        margin-top: 1rem;
    }

    /* Override flex-row dari premium-global.css: kartu disusun vertikal agar nama tidak terhimpit tombol */
    .jury-card {
        flex-direction: column;
        flex-wrap: nowrap;
        align-items: stretch;
        justify-content: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.125rem;
    }

    .jury-card-main {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        min-width: 0;
        padding: 0;
        border: none;
        background: transparent;
        text-align: left;
        cursor: pointer;
        font: inherit;
        color: inherit;
    }

    .jury-card-main:focus-visible {
        outline: 2px solid #6366f1;
        outline-offset: 4px;
        border-radius: 0.5rem;
    }

    .jury-card-avatar {
        display: grid;
        flex-shrink: 0;
        place-items: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #4338ca;
        font-size: 0.8125rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .jury-card-identity {
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
        min-width: 0;
        flex: 1;
    }

    .jury-card-identity .jury-card-email {
        display: block;
        max-width: 100%;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .jury-card-identity .jury-card-name {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        overflow: hidden;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .jury-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
    }

    .jury-card-role {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        margin-top: 0;
        padding: 0.1875rem 0.625rem;
        border-radius: 9999px;
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #4338ca;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.4;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .jury-card-role.is-empty {
        border-color: #e2e8f0;
        background: #f8fafc;
        color: #94a3b8;
        font-weight: 500;
        font-style: italic;
    }

    .jury-card-status {
        display: inline-flex;
        align-items: center;
        padding: 0.1875rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.4;
        white-space: nowrap;
    }

    .jury-card-status.is-accepted {
        border: 1px solid #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .jury-card-status.is-pending {
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #b45309;
    }

    .jury-card-status.is-declined {
        border: 1px solid #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .jury-card-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }

    .jury-card-meta {
        margin-top: 0.125rem;
    }

    .jury-detail-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }

    .jury-detail-label {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #64748b;
    }

    .jury-detail-value {
        margin-top: 0.25rem;
        font-weight: 600;
        color: #0f172a;
        word-break: break-word;
    }

    .jury-detail-note {
        margin-top: 1.25rem;
        padding: 0.875rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #f8fafc;
        font-size: 0.8125rem;
        line-height: 1.6;
        color: #475569;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-indigo-600">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6" />
        </svg>
        Kembali ke Detail Event
    </a>

    <div class="page-header">
        <h1 class="flex items-center gap-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7 shrink-0 text-slate-400" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
            </svg>
            Kelola Assignment Form Juri
        </h1>
        <p>Event: <strong class="font-semibold text-slate-700">{{ $event->name }}</strong></p>
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

    <!-- Dua opsi onboarding juri -->
    <div class="info-card" id="akun-juri">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-900">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-indigo-500" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    Opsi 1: Panitia Membuatkan Akun Juri
                </h3>
                <p class="mb-0 max-w-2xl text-sm leading-6 text-slate-600">
                    Cocok untuk juri yang tidak terbiasa teknologi. Panitia mengisi nama dan email, sistem membuat kode akses, lalu akun langsung di-assign ke form penilaian.
                </p>
            </div>
            <span class="badge-premium badge-premium-info">Disarankan</span>
        </div>

        @if(session('jury_credentials'))
        @php
            $credentials = session('jury_credentials');
        @endphp
        <div class="alert alert-success mt-4" role="status">
            <strong>Akun juri berhasil disiapkan.</strong>
            <p class="mt-1 text-sm">Berikan data berikut kepada juri. Kode akses hanya ditampilkan sekali.</p>
            <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</div>
                    <div class="font-semibold text-slate-900">{{ $credentials['name'] }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</div>
                    <div class="font-semibold text-slate-900">{{ $credentials['email'] }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kode akses / password</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <code id="jury-access-code" class="rounded-lg bg-slate-900 px-3 py-1.5 font-mono text-base tracking-widest text-white">{{ $credentials['code'] }}</code>
                        <button type="button" id="copy-jury-code" onclick="copyJuryCode()" class="btn btn-secondary btn-sm">Salin</button>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Form penilaian</div>
                    <div class="font-semibold text-slate-900">{{ $credentials['form'] }}</div>
                </div>
            </div>
            <p class="mt-4 text-xs leading-5 text-slate-600">
                Juri login di <a href="{{ route('login') }}" class="font-semibold underline">halaman login</a> menggunakan email dan kode akses di atas. Setelah login, minta juri mengganti password melalui menu Profil.
            </p>
        </div>
        @endif

        @if($formTemplates->isEmpty())
        <div class="alert alert-warning mt-4">
            Buat form penilaian terlebih dahulu sebelum membuat akun juri. Gunakan tombol <strong>Quick: Form + Assign</strong> di bawah.
        </div>
        @else
        <form method="POST" action="{{ route('events.jury-accounts.store', $event) }}" class="mt-4 grid gap-4 md:grid-cols-2">
            @csrf
            <div class="form-group">
                <label class="form-label" for="jury_name">Nama Juri <span class="text-rose-600">*</span></label>
                <input id="jury_name" name="name" type="text" value="{{ old('name') }}" class="form-control" required autocomplete="off" placeholder="Nama lengkap juri">
                @error('name') <span class="input-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="jury_email">Email Juri <span class="text-rose-600">*</span></label>
                <input id="jury_email" name="email" type="email" value="{{ old('email') }}" class="form-control" required autocomplete="off" placeholder="juri@email.com">
                @error('email') <span class="input-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label" for="jury_form_template_id">Assign ke Form Penilaian <span class="text-rose-600">*</span></label>
                <select id="jury_form_template_id" name="jury_form_template_id" class="form-control" required>
                    <option value="">Pilih form penilaian...</option>
                    @foreach($formTemplates as $formTemplate)
                    <option value="{{ $formTemplate->id }}" @selected(old('jury_form_template_id') == $formTemplate->id)>{{ $formTemplate->name }}</option>
                    @endforeach
                </select>
                @error('jury_form_template_id') <span class="input-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label" for="jury_role">Sebagai Juri Apa? <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input id="jury_role" name="jury_role" type="text" value="{{ old('jury_role') }}" class="form-control" autocomplete="off" placeholder="Contoh: Juri Vokal, Juri Adab, Juri Terbang">
                <span class="form-help">Label bebas untuk membedakan peran juri di event ini. Boleh dikosongkan.</span>
                @error('jury_role') <span class="input-error">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <p class="text-xs leading-5 text-slate-500">
                    Sistem membuatkan kode akses otomatis. Juri tidak perlu mendaftar sendiri.
                </p>
                <button type="submit" class="btn btn-primary">
                    Buat Akun + Assign
                </button>
            </div>
        </form>
        @endif

        <div class="mt-5 rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm leading-6 text-indigo-900">
            <strong>Opsi 2: Juri mendaftar sendiri.</strong>
            Minta juri membuka <a href="{{ route('register.jury') }}" target="_blank" rel="noopener" class="font-semibold underline">halaman daftar juri</a>, membuat akun, lalu hubungi panitia untuk di-assign ke event.
        </div>
    </div>

    @if($formTemplates->isEmpty())
    <div class="alert alert-warning">
        <strong>Belum ada form penilaian!</strong><br>
        Gunakan fitur <strong>"Quick: Form + Assign"</strong> di bawah untuk membuat form dan assign juri sekaligus, atau buat form terlebih dahulu di <a href="{{ route('events.jury-form.create', $event) }}" class="font-semibold underline">Kelola Form Penilaian</a>
    </div>
    @endif

    <!-- Info Card -->
    @if($formTemplates->isEmpty())
    <div class="info-card">
        <h3 class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-900">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-indigo-500" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m13 2-3 7h5l-1 6 6-8h-5l1-5h-3Z" />
            </svg>
            Quick Create: Form + Assign Juri
        </h3>
        <p class="mb-4 max-w-2xl text-sm leading-6 text-slate-600">
            Buat form baru dan assign juri sekaligus dalam satu langkah. Pilih template kriteria, beri nama form, pilih juri, lalu klik "Buat Form & Assign Juri".
        </p>
        <button type="button" onclick="showQuickCreateFormModal()" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
            </svg>
            Quick: Form + Assign
        </button>
    </div>
    @else
    <div class="info-card">
        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-900">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-amber-500" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.4.3.5.7.5 1.1h6c0-.4.1-.8.5-1.1A6 6 0 0 0 12 3Z" />
            </svg>
            Cara Menambahkan Juri ke-2 dengan Form Berbeda
        </h3>
        <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="mb-2 text-sm font-semibold text-slate-800">Langkah-langkah:</p>
            <ol class="list-decimal space-y-1 pl-5 text-sm leading-6 text-slate-600">
                <li>Klik tombol <strong class="font-semibold text-slate-800">"Quick: Form + Assign"</strong> untuk membuat form baru dan assign juri sekaligus</li>
                <li>Atau klik <strong class="font-semibold text-slate-800">"Buat Form Baru"</strong> untuk membuat form dengan kriteria custom</li>
                <li>Form baru akan muncul sebagai card terpisah di bawah</li>
                <li>Pilih juri di form yang baru, lalu klik <strong class="font-semibold text-slate-800">"Assign Juri Terpilih ke Form Ini"</strong></li>
            </ol>
        </div>
        <p class="text-sm text-slate-600">
            <strong class="font-semibold text-slate-800">Hasil:</strong> Juri 1 menggunakan <strong class="font-semibold text-slate-800">Form Juri Vokal</strong>, Juri 2 menggunakan <strong class="font-semibold text-slate-800">Form Juri Adab</strong>, dll.
        </p>
        <div class="mt-4 flex flex-wrap gap-3">
            <button type="button" onclick="showQuickCreateFormModal()" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                </svg>
                Quick: Form + Assign
            </button>
            <a href="{{ route('events.jury-form.create', $event) }}" class="btn btn-secondary">
                Buat Form Baru
            </a>
        </div>
    </div>
    @endif

    <!-- Quick Create Form Modal -->
    <div id="quickCreateFormModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Quick Create: Form Baru + Assign Juri</h2>
                <button type="button" onclick="closeQuickCreateFormModal()" class="modal-close" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('events.jury-assignments.quick-create', $event) }}" id="quickCreateForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Form <span class="text-rose-600">*</span></label>
                    <input type="text" name="form_name" required placeholder="Contoh: Form Juri Vokal, Form Juri Adab, dll" class="form-control">
                    <span class="form-help">Berikan nama yang jelas untuk membedakan form</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih Template Kriteria <span class="text-rose-600">*</span></label>
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
                    <label class="form-label">Sebagai Juri Apa? <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                    <input type="text" name="jury_role" class="form-control" autocomplete="off" placeholder="Contoh: Juri Vokal, Juri Adab, Juri Terbang">
                    <span class="form-help">Label bebas untuk peran juri pada form ini. Boleh dikosongkan.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih Juri untuk Di-assign <span class="text-rose-600">*</span></label>
                    <div class="jury-list">
                        @foreach($juries as $jury)
                        <div class="jury-list-item">
                            <label>
                                <input type="checkbox" name="jury_ids[]" value="{{ $jury->id }}">
                                <span class="text-sm font-medium text-slate-800">{{ $jury->name }}</span>
                                <span class="text-xs text-slate-500">({{ $jury->email }})</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <div class="btn-group mt-3">
                        <button type="button" onclick="selectAllQuickJuries()" class="btn btn-sm btn-select-all">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="deselectAllQuickJuries()" class="btn btn-sm btn-deselect">
                            Batal Pilih
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" onclick="closeQuickCreateFormModal()" class="btn btn-cancel">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Buat Form & Assign Juri
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
                <h2 class="form-title flex items-center gap-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5 shrink-0 text-slate-400" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                    </svg>
                    {{ $formTemplate->name ?? 'Form Juri' }}
                </h2>
                <p class="form-meta">{{ $criteriaCount }} kriteria penilaian</p>
            </div>
            <span class="badge-premium badge-premium-info">{{ count($assignedJuries) }} Juri Ter-assign</span>
        </div>

        @if($unassignedJuries->isEmpty() && count($assignedJuries) > 0)
        <div class="alert alert-success" style="margin-bottom: 0;">
            <p class="m-0">Semua juri sudah di-assign ke form ini.</p>
        </div>
        @elseif($unassignedJuries->isEmpty())
        <div class="alert alert-warning" style="margin-bottom: 0;">
            <p class="m-0">Tidak ada juri yang tersedia untuk di-assign.</p>
        </div>
        @else
        <form method="POST" action="{{ route('events.jury-assignments.store', $event) }}" id="assignForm{{ $formTemplate->id }}">
            @csrf
            <input type="hidden" name="jury_form_template_id" value="{{ $formTemplate->id }}">

            <div class="mb-6">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <label class="form-label m-0">Pilih Juri untuk Form Ini:</label>
                    <div class="btn-group">
                        <button type="button" onclick="selectAllJuries({{ $formTemplate->id }})" class="btn btn-sm btn-select-all">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="deselectAllJuries({{ $formTemplate->id }})" class="btn btn-sm btn-deselect">
                            Batal Pilih
                        </button>
                    </div>
                </div>

                <div class="max-h-[350px] overflow-y-auto rounded-xl border border-slate-200 bg-white p-3">
                    @foreach($unassignedJuries as $jury)
                    <div class="jury-checkbox-item">
                        <input type="checkbox"
                               name="jury_ids[]"
                               id="jury_{{ $formTemplate->id }}_{{ $jury->id }}"
                               value="{{ $jury->id }}">
                        <label for="jury_{{ $formTemplate->id }}_{{ $jury->id }}">
                            <span class="text-sm font-medium text-slate-800">{{ $jury->name }}</span>
                            <span class="text-xs text-slate-500">({{ $jury->email }})</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="jury_role_{{ $formTemplate->id }}">Sebagai Juri Apa? <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input id="jury_role_{{ $formTemplate->id }}" name="jury_role" type="text" class="form-control" autocomplete="off" placeholder="Contoh: Juri Vokal, Juri Adab, Juri Terbang" maxlength="100">
                <span class="form-help">Label ini berlaku untuk semua juri yang dipilih di form ini. Boleh dikosongkan.</span>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                    </svg>
                    Assign Juri Terpilih ke Form Ini
                </button>
            </div>
        </form>
        @endif

        <!-- Daftar Juri yang sudah di-assign -->
        @if(count($assignedJuries) > 0)
        <div class="assigned-juries-section">
            <h3 class="text-sm font-semibold text-slate-900">
                Juri yang Sudah Di-assign ({{ count($assignedJuries) }})
            </h3>

            @if(session('jury_password_reset'))
            @php
                $reset = session('jury_password_reset');
                $isCustomPassword = !empty($reset['custom']);
            @endphp
            <div class="alert alert-success mt-3" role="status">
                <strong>Password {{ $isCustomPassword ? 'berhasil diganti' : 'baru dibuat' }} untuk {{ $reset['name'] }}.</strong>
                <p class="mt-1 text-sm">
                    @if($isCustomPassword)
                    Password di bawah ini langsung aktif. Password lama sudah tidak berlaku.
                    @else
                    Kode akses hanya ditampilkan sekali. Salin sekarang dan berikan kepada juri.
                    @endif
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <code id="jury-reset-code" class="rounded-lg bg-slate-900 px-3 py-1.5 font-mono text-base tracking-widest text-white">{{ $reset['code'] }}</code>
                    <button type="button" id="copy-jury-reset-code" onclick="copyJuryResetCode()" class="btn btn-secondary btn-sm">Salin</button>
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-600">
                    Login: <strong>{{ $reset['email'] }}</strong>@if($reset['role']) &middot; Posisi: <strong>{{ $reset['role'] }}</strong>@endif
                </p>
                @if(!empty($reset['other_events']))
                <p class="mt-2 text-xs leading-5 text-amber-700">
                    <strong>Perhatian:</strong> akun ini juga menjadi juri di
                    {{ implode(', ', $reset['other_events']) }}.
                    Password berlaku untuk seluruh akun, jadi password di event lain ikut berubah.
                </p>
                @endif
            </div>
            @endif

            <div class="assigned-juries-grid">
                @foreach($assignments->filter(function($assignment) use ($formTemplate) {
                    return $assignment->jury_form_template_id == $formTemplate->id;
                }) as $assignment)
                <div class="jury-card">
                    <button type="button" class="jury-card-main"
                        data-jury-assignment-id="{{ $assignment->id }}"
                        data-jury-name="{{ $assignment->jury->name }}"
                        data-jury-email="{{ $assignment->jury->email }}"
                        data-jury-role="{{ $assignment->jury_role }}"
                        data-jury-form="{{ $assignment->juryFormTemplate->name ?? '-' }}"
                        data-jury-status="{{ $assignment->jury->isActive() ? 'Aktif' : 'Nonaktif' }}"
                        data-jury-assigned="{{ $assignment->created_at?->translatedFormat('d F Y, H:i') ?? '-' }}"
                        data-jury-reset-action="{{ route('events.jury-assignments.reset-password', [$event, $assignment]) }}"
                        onclick="openJuryDetailModal(this)">
                        <span class="jury-card-avatar" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr(trim($assignment->jury->name), 0, 1)) }}
                        </span>
                        <span class="jury-card-identity">
                            <span class="jury-card-name">{{ $assignment->jury->name }}</span>
                            <span class="jury-card-email">{{ $assignment->jury->email }}</span>
                        </span>
                    </button>
                    <div class="jury-card-meta">
                        @if($assignment->jury_role)
                        <span class="jury-card-role">{{ $assignment->jury_role }}</span>
                        @else
                        <span class="jury-card-role is-empty">Belum ada posisi juri</span>
                        @endif
                        @if($assignment->isAccepted())
                        <span class="jury-card-status is-accepted">Diterima</span>
                        @elseif($assignment->isDeclined())
                        <span class="jury-card-status is-declined">Ditolak</span>
                        @else
                        <span class="jury-card-status is-pending">Menunggu persetujuan</span>
                        @endif
                    </div>
                    <div class="jury-card-actions">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="openJuryDetailModal(this.closest('.jury-card').querySelector('.jury-card-main'))">
                            Detail
                        </button>
                        <form method="POST" action="{{ route('events.jury-assignments.destroy', [$event, $assignment]) }}" class="inline" onsubmit="return confirm('Hapus assignment untuk ' + (this.closest('.jury-card')?.querySelector('.jury-card-main')?.dataset.juryName ?? 'juri ini') + '?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-delete btn-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3" />
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
    @endif
</div>

<!-- Modal detail juri -->
<div id="juryDetailModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="juryDetailTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="juryDetailTitle">Detail Juri</h2>
            <button type="button" onclick="closeJuryDetailModal()" class="modal-close" aria-label="Tutup">&times;</button>
        </div>

        <div class="jury-detail-grid">
            <div>
                <div class="jury-detail-label">Nama</div>
                <div class="jury-detail-value" data-field="name">-</div>
            </div>
            <div>
                <div class="jury-detail-label">Email</div>
                <div class="jury-detail-value" data-field="email">-</div>
            </div>
            <div>
                <div class="jury-detail-label">Posisi Juri</div>
                <div class="jury-detail-value" data-field="role">-</div>
            </div>
            <div>
                <div class="jury-detail-label">Form Penilaian</div>
                <div class="jury-detail-value" data-field="form">-</div>
            </div>
            <div>
                <div class="jury-detail-label">Status Akun</div>
                <div class="jury-detail-value" data-field="status">-</div>
            </div>
            <div>
                <div class="jury-detail-label">Di-assign Pada</div>
                <div class="jury-detail-value" data-field="assigned">-</div>
            </div>
        </div>

        <div class="jury-detail-note">
            <strong>Password tidak dapat ditampilkan.</strong>
            Password juri disimpan dalam bentuk hash satu arah, jadi kode aslinya tidak bisa dibaca kembali — termasuk oleh admin.
            Isi password baru di bawah ini, atau kosongkan agar sistem membuat kode akses acak. Password lama langsung tidak berlaku.
            <br><br>
            <strong>Catatan:</strong> password milik akun juri, bukan milik event. Bila juri ini juga bertugas di event lain, password di event tersebut ikut berubah.
        </div>

        <form method="POST" id="juryResetPasswordForm" class="mt-4 border-t border-slate-100 pt-4"
              onsubmit="return confirm('Ganti password juri ini? Password lama akan langsung tidak berlaku.');">
            @csrf
            <div class="form-group">
                <label class="form-label" for="jury_new_password">Password Baru <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input id="jury_new_password" name="password" type="text" class="form-control" autocomplete="new-password"
                       placeholder="Kosongkan untuk kode akses acak" minlength="8" maxlength="100">
                <span class="form-help">Minimal 8 karakter. Isi dengan password yang mudah diingat juri, misalnya <code>juri2026</code>.</span>
                @error('password') <span class="input-error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs leading-5 text-slate-500">
                    Juri login memakai email di atas dan password baru ini.
                </p>
                <button type="submit" class="btn btn-primary">Simpan Password</button>
            </div>
        </form>
    </div>
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
                alert('Silakan pilih minimal 1 juri untuk di-assign!');
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
            previewList.replaceChildren();
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
            alert('Silakan pilih minimal 1 juri untuk di-assign!');
            return false;
        }
    });
    function copyJuryCode() {
        const codeElement = document.getElementById('jury-access-code');
        const button = document.getElementById('copy-jury-code');

        if (!codeElement || !button) {
            return;
        }

        const code = codeElement.textContent.trim();

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(() => {
                button.textContent = 'Tersalin';
            }).catch(() => {
                button.textContent = 'Salin manual';
            });
        } else {
            const range = document.createRange();
            range.selectNodeContents(codeElement);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            button.textContent = 'Tekan Ctrl+C';
        }
    }

    function openJuryDetailModal(trigger) {
        if (!trigger) {
            return;
        }

        const modal = document.getElementById('juryDetailModal');
        if (!modal) {
            return;
        }

        const fields = {
            name: trigger.dataset.juryName,
            email: trigger.dataset.juryEmail,
            role: trigger.dataset.juryRole,
            form: trigger.dataset.juryForm,
            status: trigger.dataset.juryStatus,
            assigned: trigger.dataset.juryAssigned,
        };

        Object.entries(fields).forEach(([key, value]) => {
            const target = modal.querySelector(`[data-field="${key}"]`);
            if (target) {
                target.textContent = value && value.trim() !== '' ? value : '-';
            }
        });

        const form = document.getElementById('juryResetPasswordForm');
        if (form) {
            form.action = trigger.dataset.juryResetAction || '';
        }

        modal.style.display = 'flex';
    }

    function closeJuryDetailModal() {
        const modal = document.getElementById('juryDetailModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Buka kembali modal untuk assignment yang gagal validasi password.
    (function reopenAfterValidationError() {
        const failedAssignmentId = @json(session('jury_password_error_assignment'));

        if (!failedAssignmentId) {
            return;
        }

        const trigger = document.querySelector(
            '.jury-card-main[data-jury-assignment-id="' + failedAssignmentId + '"]'
        );

        if (trigger) {
            openJuryDetailModal(trigger);
        }
    })();

    document.getElementById('juryDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeJuryDetailModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeJuryDetailModal();
        }
    });

    function copyJuryResetCode() {
        const codeElement = document.getElementById('jury-reset-code');
        const button = document.getElementById('copy-jury-reset-code');

        if (!codeElement || !button) {
            return;
        }

        const code = codeElement.textContent.trim();

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(() => {
                button.textContent = 'Tersalin';
            }).catch(() => {
                button.textContent = 'Salin manual';
            });
        } else {
            const range = document.createRange();
            range.selectNodeContents(codeElement);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            button.textContent = 'Tekan Ctrl+C';
        }
    }
</script>
@endpush
@endsection
