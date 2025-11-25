<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'JuriOnline') }} - @yield('title', 'Authentication')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Premium Global Styles -->
        <link rel="stylesheet" href="{{ asset('css/premium-global.css') }}">
        
        <style>
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: #f5f7fa;
                min-height: 100vh;
                position: relative;
                overflow-x: hidden;
            }

            body::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: 
                    linear-gradient(180deg, rgba(30, 58, 138, 0.03) 0%, transparent 50%),
                    linear-gradient(90deg, rgba(30, 58, 138, 0.02) 0%, transparent 100%);
                pointer-events: none;
            }

            .auth-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
                position: relative;
                z-index: 1;
            }

            .auth-logo {
                text-align: center;
                margin-bottom: 2.5rem;
            }

            .auth-logo a {
                display: inline-block;
                transition: opacity 0.2s;
            }

            .auth-logo a:hover {
                opacity: 0.9;
            }

            .auth-card {
                width: 100%;
                max-width: 450px;
                background: #ffffff;
                border-radius: 8px;
                padding: 3rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
                border: 1px solid #e5e7eb;
                animation: slideUp 0.3s ease-out;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .auth-header {
                text-align: center;
                margin-bottom: 2.5rem;
            }

            .auth-header h1 {
                font-size: 1.75rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 0.5rem;
                letter-spacing: -0.5px;
            }

            .auth-header h2 {
                font-size: 1.5rem;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 0.5rem;
            }

            .auth-header p {
                color: #64748b;
                font-size: 0.95rem;
                line-height: 1.5;
            }

            .auth-form-group {
                margin-bottom: 1.5rem;
            }

            .auth-label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
                color: #334155;
                font-size: 0.875rem;
            }

            .auth-input {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.9375rem;
                transition: all 0.2s;
                background: white;
                color: #1e293b;
                box-sizing: border-box;
            }

            .auth-input:focus {
                outline: none;
                border-color: #2563eb;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            }

            .auth-input::placeholder {
                color: #9ca3af;
            }

            .auth-checkbox {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 1.5rem;
            }

            .auth-checkbox input[type="checkbox"] {
                width: 18px;
                height: 18px;
                cursor: pointer;
                accent-color: #2563eb;
            }

            .auth-checkbox label {
                color: #475569;
                font-size: 0.875rem;
                cursor: pointer;
            }

            .auth-button {
                width: 100%;
                padding: 0.875rem 1.5rem;
                background: #2563eb;
                color: white;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                font-size: 0.9375rem;
                cursor: pointer;
                transition: all 0.2s;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            }

            .auth-button:hover {
                background: #1d4ed8;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .auth-button:active {
                background: #1e40af;
                transform: translateY(0);
            }

            .auth-link {
                color: #2563eb;
                text-decoration: none;
                font-weight: 500;
                font-size: 0.875rem;
                transition: color 0.2s;
            }

            .auth-link:hover {
                color: #1d4ed8;
                text-decoration: underline;
            }

            .auth-footer {
                margin-top: 2rem;
                text-align: center;
                padding-top: 2rem;
                border-top: 1px solid #e5e7eb;
            }

            .auth-footer p {
                color: #64748b;
                font-size: 0.875rem;
            }

            .auth-error {
                background: #fef2f2;
                color: #991b1b;
                padding: 1rem;
                border-radius: 6px;
                margin-bottom: 1.5rem;
                border-left: 3px solid #dc2626;
                font-size: 0.875rem;
            }

            .auth-success {
                background: #f0fdf4;
                color: #166534;
                padding: 1rem;
                border-radius: 6px;
                margin-bottom: 1.5rem;
                border-left: 3px solid #16a34a;
                font-size: 0.875rem;
            }

            .input-error {
                color: #dc2626;
                font-size: 0.8125rem;
                margin-top: 0.5rem;
                display: block;
            }

            @media (max-width: 640px) {
                .auth-card {
                    padding: 2rem 1.5rem;
                }

                .auth-header h1 {
                    font-size: 1.5rem;
                }

                .auth-header h2 {
                    font-size: 1.25rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-logo">
                    <a href="/" style="text-decoration: none; display: inline-block;">
                        <div style="width: 70px; height: 70px; margin: 0 auto 1.25rem; background: #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">
                            <span style="font-size: 2.5rem; color: white;">⚖️</span>
                        </div>
                    </a>
                    
                    <!-- Tulisan JuriOnline -->
                    <h1 style="margin: 0; font-size: 2rem; font-weight: 700; color: #1e293b; letter-spacing: -0.5px; display: block; line-height: 1.2;">
                        JuriOnline
                    </h1>
                </div>

                <div class="auth-header">
                    <h2>
                        @yield('auth-title', 'Welcome')
                    </h2>
                    <p>
                        @yield('auth-subtitle', 'Sign in to your account')
                    </p>
                </div>

                @if(session('status'))
                <div class="auth-success">
                    {{ session('status') }}
                </div>
                @endif

                @if($errors->any())
                <div class="auth-error">
                    <strong>⚠️ Terjadi kesalahan:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
