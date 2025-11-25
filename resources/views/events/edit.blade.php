@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="card">
    <h1>Edit Event</h1>

    <form method="POST" action="{{ route('events.update', $event) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div>
                <div class="form-group">
                    <label for="name">Nama Event *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $event->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Kategori</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $event->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi *</label>
                    <textarea name="description" id="description" class="form-control" rows="5" required>{{ old('description', $event->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="location">Lokasi</label>
                    <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $event->location) }}" placeholder="Contoh: Jakarta Convention Center atau Online">
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="draft" {{ old('status', $event->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status', $event->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="closed" {{ old('status', $event->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="banner">Banner Event (JPG/PNG, Max 2MB)</label>
                    @if($event->banner)
                    <div style="margin-bottom: 0.5rem;">
                        <img src="{{ Storage::url($event->banner) }}" alt="Current Banner" style="max-width: 200px; border-radius: 5px;">
                        <p style="font-size: 0.875rem; color: #666;">Banner saat ini (akan diganti jika upload baru)</p>
                    </div>
                    @endif
                    <input type="file" name="banner" id="banner" class="form-control" accept="image/jpeg,image/png">
                </div>
            </div>

            <div>
                <div class="form-group">
                    <label for="price">Harga (Rp) *</label>
                    <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $event->price) }}" min="0" step="1000" required>
                    <small style="color: #666;">Masukkan 0 untuk event gratis</small>
                </div>

                <div class="form-group">
                    <label for="quota">Kuota Peserta *</label>
                    <input type="number" name="quota" id="quota" class="form-control" value="{{ old('quota', $event->quota) }}" min="{{ $event->registered_count }}" required>
                    <small style="color: #666;">Peserta terdaftar: {{ $event->registered_count }} (kuota minimal harus {{ $event->registered_count }})</small>
                </div>

                <h3 style="margin-top: 1.5rem; margin-bottom: 1rem;">Periode Pendaftaran</h3>

                <div class="form-group">
                    <label for="registration_start">Tanggal Mulai Pendaftaran *</label>
                    <input type="date" name="registration_start" id="registration_start" class="form-control" value="{{ old('registration_start', $event->registration_start->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label for="registration_end">Tanggal Akhir Pendaftaran *</label>
                    <input type="date" name="registration_end" id="registration_end" class="form-control" value="{{ old('registration_end', $event->registration_end->format('Y-m-d')) }}" required>
                </div>

                <h3 style="margin-top: 1.5rem; margin-bottom: 1rem;">Periode Event</h3>

                <div class="form-group">
                    <label for="event_start">Tanggal Mulai Event *</label>
                    <input type="date" name="event_start" id="event_start" class="form-control" value="{{ old('event_start', $event->event_start->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label for="event_end">Tanggal Akhir Event *</label>
                    <input type="date" name="event_end" id="event_end" class="form-control" value="{{ old('event_end', $event->event_end->format('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-success">Update Event</button>
            <a href="{{ route('events.show', $event) }}" class="btn" style="background: #ddd;">Batal</a>
        </div>
    </form>
</div>
@endsection