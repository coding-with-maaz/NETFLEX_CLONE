<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmbedReport;
use App\Models\Movie;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class EmbedReportApiController extends Controller
{
    /**
     * Report an embed problem (public endpoint - no authentication required)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'content_type' => 'required|in:movie,episode',
                'content_id' => 'required|integer',
                'embed_id' => 'nullable|integer',
                'report_type' => 'required|in:not_working,wrong_content,poor_quality,broken_link,other',
                'description' => 'nullable|string|max:1000',
                'email' => 'required|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422)->header('Content-Type', 'application/json');
            }

            // Verify content exists
            if ($request->content_type === 'movie') {
                $content = Movie::find($request->content_id);
                if (!$content) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Movie not found'
                    ], 404)->header('Content-Type', 'application/json');
                }
            } elseif ($request->content_type === 'episode') {
                $content = Episode::find($request->content_id);
                if (!$content) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Episode not found'
                    ], 404)->header('Content-Type', 'application/json');
                }
            }

            // Check if a similar report already exists
            $existingReport = EmbedReport::where('content_type', $request->content_type)
                ->where('content_id', $request->content_id)
                ->where(function($query) use ($request) {
                    if ($request->embed_id) {
                        $query->where('embed_id', $request->embed_id);
                    } else {
                        $query->whereNull('embed_id');
                    }
                })
                ->where('report_type', $request->report_type)
                ->where('status', 'pending')
                ->first();

            if ($existingReport) {
                // Increment report count
                $existingReport->increment('report_count');
                $existingReport->update([
                    'email' => $request->email, // Update email if provided
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Report already exists. We have updated the report count.',
                    'data' => [
                        'report' => $existingReport->fresh(),
                        'report_count' => $existingReport->report_count
                    ]
                ], 200)->header('Content-Type', 'application/json');
            }

            // Create new report
            $embedReport = EmbedReport::create([
                'content_type' => $request->content_type,
                'content_id' => $request->content_id,
                'embed_id' => $request->embed_id,
                'report_type' => $request->report_type,
                'description' => $request->description,
                'email' => $request->email,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'report_count' => 1,
                'reported_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Embed problem reported successfully',
                'data' => [
                    'report' => $embedReport
                ]
            ], 201)->header('Content-Type', 'application/json');

        } catch (Exception $e) {
            Log::error('Embed Report API Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting your report. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Get embed reports (public endpoint - for checking status)
     */
    public function index(Request $request)
    {
        try {
            $query = EmbedReport::query();

            // Filter by content type
            if ($request->has('content_type') && $request->content_type) {
                $query->where('content_type', $request->content_type);
            }

            // Filter by content id
            if ($request->has('content_id') && $request->content_id) {
                $query->where('content_id', $request->content_id);
            }

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by report type
            if ($request->has('report_type') && $request->report_type) {
                $query->where('report_type', $request->report_type);
            }

            // Sort by report count or date
            $sortBy = $request->get('sort_by', 'reported_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Validate sort_by field
            $allowedSortFields = ['reported_at', 'report_count', 'report_type', 'status'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'reported_at';
            }
            
            // Validate sort_order
            $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min((int)$request->get('per_page', 20), 100); // Max 100 per page
            $reports = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'reports' => $reports->items(),
                    'pagination' => [
                        'current_page' => $reports->currentPage(),
                        'last_page' => $reports->lastPage(),
                        'per_page' => $reports->perPage(),
                        'total' => $reports->total(),
                        'from' => $reports->firstItem(),
                        'to' => $reports->lastItem(),
                    ]
                ]
            ], 200)->header('Content-Type', 'application/json');

        } catch (Exception $e) {
            Log::error('Embed Report Index API Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching reports. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'data' => [
                    'reports' => [],
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

