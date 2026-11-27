@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="page-header-premium">
    <h1>Profil Saya</h1>
    <p>Kelola informasi akun, kata sandi, dan keamanan akun JURI.ON Anda.</p>
</div>

<div>
    <div class="premium-card">
        <div class="max-w-2xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="premium-card">
        <div class="max-w-2xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <div class="premium-card">
        <div class="max-w-2xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
