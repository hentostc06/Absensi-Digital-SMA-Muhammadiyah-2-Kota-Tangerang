<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpsCameraPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = (string) config('app.url');
        $mustHttps = str_starts_with($appUrl, 'https://');

        $forwardedProto = strtolower((string) $request->header('X-Forwarded-Proto'));
        $isHttps = $request->isSecure()
            || $forwardedProto === 'https'
            || strtolower((string) $request->server('HTTPS')) === 'on';

        if ($mustHttps && ! $isHttps) {
            return redirect()->to('https://' . $request->getHost() . $request->getRequestUri(), 301);
        }

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), fullscreen=(self)');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
