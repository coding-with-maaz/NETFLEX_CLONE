<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class ContentRequestApiController extends Controller
{
    /**
     * Create a new content request (public endpoint - no authentication required)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:movie,tvshow',
                'title' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'description' => 'nullable|string|max:1000',
                'tmdb_id' => 'nullable|string|max:50',
                'year' => 'nullable|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if a similar request already exists
            $existingRequest = ContentRequest::where('type', $request->type)
                ->where('title', $request->title)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                // Increment request count
                $existingRequest->increment('request_count');
                $existingRequest->update([
                    'email' => $request->email, // Update email if provided
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Request already exists. We have updated the request count.',
                    'data' => [
                        'request' => $existingRequest->fresh(),
                        'request_count' => $existingRequest->request_count
                    ]
                ], 200)->header('Content-Type', 'application/json');
            }

            // Create new request
            $contentRequest = ContentRequest::create([
                'type' => $request->type,
                'title' => $request->title,
                'email' => $request->email,
                'description' => $request->description,
                'tmdb_id' => $request->tmdb_id,
                'year' => $request->year,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_count' => 1,
                'requested_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Content request submitted successfully',
                'data' => [
                    'request' => $contentRequest
                ]
            ], 201)->header('Content-Type', 'application/json');

        } catch (Exception $e) {
            Log::error('Content Request API Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting your request. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Get content requests (public endpoint - for checking status)
     */
    public function index(Request $request)
    {
        try {
            $query = ContentRequest::query();

            // Filter by type
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Search by title
            if ($request->has('search') && $request->search) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            // Sort by request count (most requested first) or by date
            $sortBy = $request->get('sort_by', 'requested_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Validate sort_by field
            $allowedSortFields = ['requested_at', 'request_count', 'title', 'status'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'requested_at';
            }
            
            // Validate sort_order
            $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min((int)$request->get('per_page', 20), 100); // Max 100 per page
            $requests = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'requests' => $requests->items(),
                    'pagination' => [
                        'current_page' => $requests->currentPage(),
                        'last_page' => $requests->lastPage(),
                        'per_page' => $requests->perPage(),
                        'total' => $requests->total(),
                        'from' => $requests->firstItem(),
                        'to' => $requests->lastItem(),
                    ]
                ]
            ], 200)->header('Content-Type', 'application/json');

        } catch (Exception $e) {
            Log::error('Content Request Index API Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching requests. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'data' => [
                    'requests' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 20,
                        'total' => 0,
                    ]
                ]
            ], 500)->header('Content-Type', 'application/json');
        }
    }
}

