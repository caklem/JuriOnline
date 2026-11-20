<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds baseline browser-hardening headers to every response.
 *
 * The CSP keeps inline handlers/styles enabled because the Blade views use
 * inline `onclick` attributes and Tailwind utility styles; tightening to
 * nonces is a separate change.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; "
            . "img-src 'self' data:; "
            . "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; "
            . "script-src 'self' 'unsafe-inline'; "
            . "font-src 'self' data: https://fonts.bunny.net; "
            . "connect-src 'self'; "
            . "form-action 'self'; "
            . "frame-ancestors 'self'; "
            . "base-uri 'self'; "
            . "object-src 'none'"
        );

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
