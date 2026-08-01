<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that attaches Cache-Control and CDN surrogate headers
 * to cacheable API responses.
 *
 * Usage (in routes/api.php):
 *   Route::get('products', ...)->middleware('cache:public,300');
 *   Route::get('categories', ...)->middleware('cache:public,21600');
 *
 * The first parameter is the cache visibility (public/private).
 * The second parameter is max-age in seconds.
 * A third, optional parameter sets the CDN surrogate-control max-age.
 */
class HttpCache
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $visibility = 'private', string $maxAge = '0', string $surrogateMaxAge = null): Response
    {
        $response = $next($request);

        // Only cache successful GET/HEAD responses
        if (! $request->isMethodCacheable() || ! $response->isSuccessful()) {
            return $response;
        }

        // Don't override existing Cache-Control headers
        if ($response->headers->has('Cache-Control') && $response->headers->get('Cache-Control') !== 'no-cache, private') {
            return $response;
        }

        $maxAge = (int) $maxAge;
        $cacheDirective = match ($visibility) {
            'public' => 'public',
            default => 'private',
        };

        $cacheControl = "{$cacheDirective}, max-age={$maxAge}";

        if ($maxAge > 0) {
            $cacheControl .= ', must-revalidate';
            $response->setLastModified(new \DateTimeImmutable());
            $response->setEtag(md5($response->getContent() ?: ''));
            $response->isNotModified($request);
        }

        $response->headers->set('Cache-Control', $cacheControl);

        // CDN surrogate control (used by Cloudflare, Fastly, Varnish)
        if ($surrogateMaxAge !== null) {
            $response->headers->set('Surrogate-Control', "max-age={$surrogateMaxAge}");
        }

        // Vary header for correct CDN caching behind proxies
        $response->headers->set('Vary', 'Accept-Encoding, Authorization');

        return $response;
    }
}
