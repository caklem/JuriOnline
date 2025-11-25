@extends('layouts.app')

@section('title', 'Buat Event Baru')

@section('content')
<div class="card">
    <h1>Buat Event Baru</h1>

    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="name">Nama Event *</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="category_id">Kategori</label>
            <select name="category_id" id="category_id" class="form-control">
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="description">Deskripsi *</label>
            <textarea name="description" id="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="price">Harga (Rp) *</label>
                <input type="number" name="price" id="price" class="form-control" value="{{ old('price', 0) }}" min="0" step="1000" required>
            </div>

            <div class="form-group">
                <label for="quota">Kuota Peserta *</label>
                <input type="number" name="quota" id="quota" class="form-control" value="{{ old('quota') }}" min="1" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="registration_start">Mulai Pendaftaran *</label>
                <input type="date" name="registration_start" id="registration_start" class="form-control" value="{{ old('registration_start') }}" required>
            </div>

            <div class="form-group">
                <label for="registration_end">Akhir Pendaftaran *</label>
                <input type="date" name="registration_end" id="registration_end" class="form-control" value="{{ old('registration_end') }}" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="event_start">Mulai Event *</label>
                <input type="date" name="event_start" id="event_start" class="form-control" value="{{ old('event_start') }}" required>
            </div>

            <div class="form-group">
                <label for="event_end">Akhir Event *</label>
                <input type="date" name="event_end" id="event_end" class="form-control" value="{{ old('event_end') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="location">Lokasi</label>
            <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}" placeholder="Contoh: Hotel Bintang 5 Jakarta atau Online via Zoom">
        </div>

        <div class="form-group">
            <label for="banner">Banner Event (JPG, PNG - Max 2MB)</label>
            <input type="file" name="banner" id="banner" class="form-control" accept="image/jpeg,image/png">
        </div>

        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-success">Buat Event</button>
            <a href="{{ route('events.index') }}" class="btn" style="background: #ddd;">Batal</a>
        </div>
    </form>
</div>
@endsection