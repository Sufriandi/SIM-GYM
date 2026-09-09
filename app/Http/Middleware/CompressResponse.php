<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompressResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Hanya kompresi jika client mendukung gzip dan respon belum di-encode
        $acceptEncoding = (string) $request->header('Accept-Encoding', '');
        if (!str_contains($acceptEncoding, 'gzip') || $response->headers->has('Content-Encoding')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        if (str_contains($contentType, 'text/html') || str_contains($contentType, 'application/json')) {
            $content = $response->getContent();

            // Lewatkan jika konten terlalu kecil (< 1KB) karena kompresi justru menambah overhead
            if (strlen($content) > 1024 && function_exists('gzencode')) {
                $compressed = gzencode($content, 4);

                if ($compressed !== false && strlen($compressed) < strlen($content)) {
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Content-Length', (string) strlen($compressed));
                }
            }
        }

        return $response;
    }
}