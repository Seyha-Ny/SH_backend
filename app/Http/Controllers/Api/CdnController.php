<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * CDN cache management.
 *
 * Provides an endpoint for admins to purge cached assets
 * on the CDN (Cloudflare, BunnyCDN, etc.) after content updates.
 */
class CdnController extends Controller
{
    /**
     * Purge the CDN cache for specific URLs or the entire site.
     *
     * Requires the CLOUDFLARE_API_TOKEN and CLOUDFLARE_ZONE_ID
     * to be set in .env. If either is missing, the endpoint
     * returns a 501 Not Implemented.
     *
     * @OA\Post(
     *     path="/api/cdn/purge",
     *     summary="Purge CDN cache",
     *     tags={"CDN"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="urls", type="array", items=@OA\Items(type="string", format="uri"),
     *                 description="Optional list of specific URLs to purge. Leave empty to purge everything."
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Purge request submitted"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=501, description="CDN not configured")
     * )
     */
    public function purge(Request $request): JsonResponse
    {
        $apiToken = config('services.cloudflare.api_token');
        $zoneId = config('services.cloudflare.zone_id');

        if (! $apiToken || ! $zoneId) {
            return response()->json([
                'message' => 'CDN purge is not configured. Set CLOUDFLARE_API_TOKEN and CLOUDFLARE_ZONE_ID in .env',
            ], 501);
        }

        $urls = $request->input('urls');

        $payload = $urls && is_array($urls) && count($urls) > 0
            ? ['files' => $urls]
            : ['purge_everything' => true];

        $response = Http::withToken($apiToken)
            ->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache", $payload);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'CDN purge failed.',
                'error' => $response->json(),
            ], 502);
        }

        return response()->json([
            'message' => 'CDN cache purge submitted successfully.',
        ]);
    }
}
