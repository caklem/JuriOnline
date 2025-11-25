@props(['title' => 'Authentication', 'authTitle' => 'Welcome', 'authSubtitle' => 'Sign in to your account'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'JuriOnline') }} - {{ $title }}</title>

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
                background: 
                    linear-gradient(135deg, #1e3c72 0%, #2a5298 25%, #7e22ce 50%, #9333ea 75%, #c026d3 100%);
                background-size: 400% 400%;
                animation: gradientShift 15s ease infinite;
                min-height: 100vh;
                position: relative;
                overflow-x: hidden;
                margin: 0;
                padding: 0;
            }

            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            body::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: 
                    radial-gradient(circle at 20% 30%, rgba(255,255,255,0.15) 0%, transparent 40%),
                    radial-gradient(circle at 80% 70%, rgba(255,255,255,0.1) 0%, transparent 40%),
                    radial-gradient(circle at 50% 50%, rgba(147, 51, 234, 0.2) 0%, transparent 50%);
                pointer-events: none;
                animation: pulse 8s ease-in-out infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.8; }
            }

            body::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-image: 
                    repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,255,255,0.03) 10px, rgba(255,255,255,0.03) 20px),
                    repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(255,255,255,0.03) 10px, rgba(255,255,255,0.03) 20px);
                pointer-events: none;
                opacity: 0.5;
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
                margin-bottom: 2rem;
            }

            .auth-logo a {
                display: inline-block;
                transition: transform 0.3s;
            }

            .auth-logo a:hover {
                transform: scale(1.05);
            }

            .auth-card {
                width: 100%;
                max-width: 450px;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(30px) saturate(180%);
                border-radius: 28px;
                padding: 3rem;
                box-shadow: 
                    0 25px 80px rgba(0,0,0,0.4),
                    0 0 0 1px rgba(255,255,255,0.5) inset,
                    0 0 100px rgba(147, 51, 234, 0.3);
                border: 2px solid rgba(255,255,255,0.4);
                animation: slideUp 0.5s ease-out;
                position: relative;
                overflow: hidden;
            }

            .auth-card::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(147, 51, 234, 0.1) 0%, transparent 70%);
                animation: rotate 20s linear infinite;
            }

            @keyframes rotate {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
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

            .auth-header {
                text-align: center;
                margin-bottom: 2.5rem;
            }

            .auth-header h1 {
                font-size: 2rem;
                font-weight: 700;
                background: linear-gradient(135deg, #9333ea 0%, #c026d3 50%, #7e22ce 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                margin-bottom: 0.5rem;
                filter: drop-shadow(0 2px 4px rgba(147, 51, 234, 0.3));
            }

            .auth-header p {
                color: #7f8c8d;
                font-size: 0.95rem;
            }

            .auth-form-group {
                margin-bottom: 1.5rem;
            }

            .auth-label {
                display: block;
                margin-bottom: 0.75rem;
                font-weight: 600;
                color: #2c3e50;
                font-size: 0.95rem;
            }

            .auth-input {
                width: 100%;
                padding: 0.875rem 1.25rem;
                border: 2px solid #e2e8f0;
                border-radius: 12px;
                font-size: 1rem;
                transition: all 0.3s;
                background: white;
                color: #2c3e50;
            }

            .auth-input:focus {
                outline: none;
                border-color: #9333ea;
                box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.15), 0 4px 12px rgba(147, 51, 234, 0.2);
                transform: translateY(-1px);
            }

            .auth-checkbox {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin-bottom: 1.5rem;
            }

            .auth-checkbox input[type="checkbox"] {
                width: 20px;
                height: 20px;
                cursor: pointer;
                accent-color: #9333ea;
            }

            .auth-checkbox label {
                color: #2c3e50;
                font-size: 0.9rem;
                cursor: pointer;
            }

            .auth-button {
                width: 100%;
                padding: 1rem;
                background: linear-gradient(135deg, #9333ea 0%, #c026d3 50%, #7e22ce 100%);
                background-size: 200% 200%;
                animation: gradientShift 3s ease infinite;
                color: white;
                border: none;
                border-radius: 12px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.3s;
                box-shadow: 
                    0 8px 24px rgba(147, 51, 234, 0.5),
                    0 0 0 1px rgba(255,255,255,0.2) inset;
                position: relative;
                overflow: hidden;
            }

            .auth-button::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
                transition: left 0.5s;
            }

            .auth-button:hover::before {
                left: 100%;
            }

            .auth-button:hover {
                transform: translateY(-2px);
                box-shadow: 
                    0 12px 40px rgba(147, 51, 234, 0.6),
                    0 0 0 1px rgba(255,255,255,0.3) inset;
                background-size: 200% 200%;
            }

            .auth-button:active {
                transform: translateY(0);
            }

            .auth-link {
                color: #9333ea;
                text-decoration: none;
                font-weight: 600;
                font-size: 0.9rem;
                transition: all 0.3s;
            }

            .auth-link:hover {
                color: #c026d3;
                text-decoration: underline;
                text-shadow: 0 0 8px rgba(147, 51, 234, 0.5);
            }

            .auth-footer {
                margin-top: 2rem;
                text-align: center;
                padding-top: 2rem;
                border-top: 1px solid #e2e8f0;
            }

            .auth-footer p {
                color: #7f8c8d;
                font-size: 0.9rem;
            }

            .auth-error {
                background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
                color: #721c24;
                padding: 1rem;
                border-radius: 12px;
                margin-bottom: 1.5rem;
                border-left: 4px solid #dc3545;
                font-size: 0.9rem;
            }

            .auth-success {
                background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
                color: #155724;
                padding: 1rem;
                border-radius: 12px;
                margin-bottom: 1.5rem;
                border-left: 4px solid #27ae60;
                font-size: 0.9rem;
            }

            .input-error {
                color: #dc3545;
                font-size: 0.875rem;
                margin-top: 0.5rem;
                display: block;
            }

            @media (max-width: 640px) {
                .auth-card {
                    padding: 2rem 1.5rem;
                }

                .auth-header h1 {
                    font-size: 1.75rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-logo" style="text-align: center; margin-bottom: 2rem;">
                    <a href="/" style="text-decoration: none; display: inline-block;">
                        <div style="width: 90px; height: 90px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #9333ea 0%, #c026d3 50%, #7e22ce 100%); border-radius: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 12px 40px rgba(147, 51, 234, 0.5), 0 0 0 4px rgba(255,255,255,0.2) inset; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%); animation: rotate 10s linear infinite;"></div>
                            <span style="font-size: 3rem; color: white; position: relative; z-index: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">⚖️</span>
                        </div>
                    </a>
                    
                    <!-- Tulisan JuriOnline -->
                    <div style="margin-top: 1rem;">
                        <h1 style="margin: 0; font-size: 3.5rem; font-weight: 900; color: #9333ea !important; text-shadow: 0 4px 20px rgba(147, 51, 234, 0.5), 0 0 40px rgba(147, 51, 234, 0.3); letter-spacing: 3px; display: block; line-height: 1.2; position: relative; z-index: 100;">
                            JuriOnline
                        </h1>
                    </div>
                </div>

                <div class="auth-header" style="margin-bottom: 2.5rem; text-align: center;">
                    <h2 style="margin: 0 0 0.5rem 0; font-size: 1.5rem; font-weight: 700; color: #2c3e50;">
                        {{ $authTitle ?? 'Welcome' }}
                    </h2>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.95rem;">
                        {{ $authSubtitle ?? 'Sign in to your account' }}
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

