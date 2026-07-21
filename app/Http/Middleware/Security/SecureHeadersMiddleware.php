<?php

namespace App\Http\Middleware\Security;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->remove('X-Powered-By');

        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $response->headers->set('Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), usb=(), payment=()'
        );

        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; ".
            "script-src 'self'; ".
            "style-src 'self' 'unsafe-inline'; ".
            "img-src 'self' data: https:; ".
            "font-src 'self'; ".
            "connect-src 'self'; ".
            "frame-ancestors 'none'; ".
            "form-action 'self'; ".
            "base-uri 'self'; ".
            "object-src 'none';"
        );

        return $response;
    }
}