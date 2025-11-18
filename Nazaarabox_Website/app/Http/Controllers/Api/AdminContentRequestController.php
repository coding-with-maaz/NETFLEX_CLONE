<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContentRequestNotification;

class AdminContentRequestController extends Controller
{
    /**
     * Get all content requests with filters
     */
    public function index(Request $request)
    {
        $query = ContentRequest::with('processedBy');

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

        // Sort
        $sortBy = $request->get('sort_by', 'requested_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $requests = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => ContentRequest::count(),
            'pending' => ContentRequest::where('status', 'pending')->count(),
            'approved' => ContentRequest::where('status', 'approved')->count(),
            'rejected' => ContentRequest::where('status', 'rejected')->count(),
            'completed' => ContentRequest::where('status', 'completed')->count(),
            'movies' => ContentRequest::where('type', 'movie')->count(),
            'tvshows' => ContentRequest::where('type', 'tvshow')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'requests' => $requests->items(),
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                ],
                'stats' => $stats
            ]
        ], 200);
    }

    /**
     * Get single content request
     */
    public function show($id)
    {
        $request = ContentRequest::with('processedBy')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'request' => $request
            ]
        ], 200);
    }

    /**
     * Update content request status
     */
    public function update(Request $request, $id)
    {
        $contentRequest = ContentRequest::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected,completed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get admin ID from token if available, otherwise set to null
        $adminId = null;
        $token = $request->bearerToken() ?? $request->header('Authorization');
        // In a real implementation, you would validate the token and get admin ID
        // For now, we'll make it nullable
        
        $oldStatus = $contentRequest->status;
        $oldNotes = $contentRequest->admin_notes;
        
        $contentRequest->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'processed_at' => now(),
            'processed_by' => $adminId,
        ]);

        // Send email notification if email exists and (status changed or notes added/updated)
        if ($contentRequest->email && 
            ($oldStatus !== $request->status || $oldNotes !== $request->admin_notes)) {
            try {
                Mail::to($contentRequest->email)->send(
                    new ContentRequestNotification($contentRequest)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send content request notification: ' . $e->getMessage());
                // Don't fail the request if email fails
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Content request updated successfully',
            'data' => [
                'request' => $contentRequest->fresh(['processedBy'])
            ]
        ], 200);
    }

    /**
     * Delete content request
     */
    public function destroy($id)
    {
        $contentRequest = ContentRequest::findOrFail($id);
        $contentRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Content request deleted successfully'
        ], 200);
    }

    /**
     * Bulk update requests
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:content_requests,id',
            'status' => 'required|in:pending,approved,rejected,completed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get admin ID from token if available, otherwise set to null
        $adminId = null;
        $token = $request->bearerToken() ?? $request->header('Authorization');
        // In a real implementation, you would validate the token and get admin ID
        
        // Get requests before update to send emails
        $requestsToUpdate = ContentRequest::whereIn('id', $request->ids)->get();
        
        $updated = ContentRequest::whereIn('id', $request->ids)->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'processed_at' => now(),
            'processed_by' => $adminId,
        ]);

        // Send email notifications for updated requests
        foreach ($requestsToUpdate as $req) {
            if ($req->email) {
                try {
                    // Refresh to get updated data
                    $req->refresh();
                    Mail::to($req->email)->send(
                        new ContentRequestNotification($req)
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send content request notification: ' . $e->getMessage());
                    // Don't fail the request if email fails
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} content request(s) successfully",
            'data' => [
                'updated_count' => $updated
            ]
        ], 200);
    }
}

