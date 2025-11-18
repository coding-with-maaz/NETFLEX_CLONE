<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ApiKeyController extends Controller
{
    /**
     * List all API keys (admin only)
     */
    public function index(Request $request)
    {
        // TODO: Add admin authentication middleware
        $query = ApiKey::query();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        // Sort by creation date
        $sortBy = $request->get('sort_by', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sortBy, $order);

        // Pagination
        $perPage = min($request->get('limit', 20), 100);
        $apiKeys = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'api_keys' => $apiKeys->items(),
                'pagination' => [
                    'current_page' => $apiKeys->currentPage(),
                    'per_page' => $apiKeys->perPage(),
                    'total' => $apiKeys->total(),
                    'total_pages' => $apiKeys->lastPage(),
                    'has_next' => $apiKeys->hasMorePages(),
                    'has_prev' => $apiKeys->currentPage() > 1,
                ]
            ]
        ]);
    }

    /**
     * Generate a new API key
     */
    public function store(Request $request)
    {
        // TODO: Add admin authentication middleware
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'expires_at' => 'nullable|date',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $options = [];
        if ($request->has('expires_at')) {
            $options['expires_at'] = Carbon::parse($request->expires_at);
        }
        if ($request->has('allowed_ips')) {
            $options['allowed_ips'] = $request->allowed_ips;
        }
        if ($request->has('notes')) {
            $options['notes'] = $request->notes;
        }

        $result = ApiKey::generate($request->name, $options);

        return response()->json([
            'success' => true,
            'message' => 'API key generated successfully',
            'data' => [
                'key' => $result['key'], // Only shown once
                'api_key' => [
                    'id' => $result['apiKey']->id,
                    'name' => $result['apiKey']->name,
                    'prefix' => $result['apiKey']->key_prefix,
                    'is_active' => $result['apiKey']->is_active,
                    'expires_at' => $result['apiKey']->expires_at,
                    'created_at' => $result['apiKey']->created_at,
                ]
            ]
        ], 201);
    }

    /**
     * Get API key details (without showing the actual key)
     */
    public function show($id)
    {
        // TODO: Add admin authentication middleware
        
        $apiKey = ApiKey::find($id);

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'api_key' => [
                    'id' => $apiKey->id,
                    'name' => $apiKey->name,
                    'prefix' => $apiKey->key_prefix,
                    'is_active' => $apiKey->is_active,
                    'request_count' => $apiKey->request_count,
                    'last_used_at' => $apiKey->last_used_at,
                    'expires_at' => $apiKey->expires_at,
                    'allowed_ips' => $apiKey->allowed_ips,
                    'notes' => $apiKey->notes,
                    'created_at' => $apiKey->created_at,
                    'updated_at' => $apiKey->updated_at,
                ]
            ]
        ]);
    }

    /**
     * Update API key
     */
    public function update(Request $request, $id)
    {
        // TODO: Add admin authentication middleware
        
        $apiKey = ApiKey::find($id);

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|required|boolean',
            'expires_at' => 'nullable|date',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('name')) {
            $apiKey->name = $request->name;
        }
        if ($request->has('is_active')) {
            $apiKey->is_active = $request->is_active;
        }
        if ($request->has('expires_at')) {
            $apiKey->expires_at = $request->expires_at ? Carbon::parse($request->expires_at) : null;
        }
        if ($request->has('allowed_ips')) {
            $apiKey->allowed_ips = $request->allowed_ips;
        }
        if ($request->has('notes')) {
            $apiKey->notes = $request->notes;
        }

        $apiKey->save();

        return response()->json([
            'success' => true,
            'message' => 'API key updated successfully',
            'data' => [
                'api_key' => [
                    'id' => $apiKey->id,
                    'name' => $apiKey->name,
                    'prefix' => $apiKey->key_prefix,
                    'is_active' => $apiKey->is_active,
                    'expires_at' => $apiKey->expires_at,
                    'allowed_ips' => $apiKey->allowed_ips,
                    'notes' => $apiKey->notes,
                ]
            ]
        ]);
    }

    /**
     * Delete API key
     */
    public function destroy($id)
    {
        // TODO: Add admin authentication middleware
        
        $apiKey = ApiKey::find($id);

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }

        $apiKey->delete();

        return response()->json([
            'success' => true,
            'message' => 'API key deleted successfully'
        ]);
    }
}

