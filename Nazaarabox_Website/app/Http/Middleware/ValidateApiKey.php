<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get API key from header (preferred) or query parameter
        $apiKey = $request->header('X-API-Key') 
               ?? $request->header('Authorization')
               ?? $request->query('api_key');

        // If Authorization header is used, extract key (format: "Bearer {key}" or just "{key}")
        if ($apiKey && str_starts_with($apiKey, 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }

        // Check if API key is provided
        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide your API key in the X-API-Key header or api_key query parameter.',
                'error' => 'missing_api_key'
            ], 401);
        }

        // Validate the API key
        $validKey = ApiKey::validate($apiKey);

        if (!$validKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API key.',
                'error' => 'invalid_api_key'
            ], 401);
        }

        // Check IP restrictions if set
        $clientIp = $request->ip();
        if (!$validKey->isIpAllowed($clientIp)) {
            return response()->json([
                'success' => false,
                'message' => 'API key is not allowed from this IP address.',
                'error' => 'ip_not_allowed'
            ], 403);
        }

        // Attach API key to request for use in controllers if needed
        $request->merge(['api_key_model' => $validKey]);

        return $next($request);
    }
}

