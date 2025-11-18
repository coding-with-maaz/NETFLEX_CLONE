<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\TVShow;
use App\Models\Episode;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminReplyNotification;
use Illuminate\Support\Facades\DB;

class AdminCommentController extends Controller
{
    /**
     * Get all comments with filters (admin)
     */
    public function index(Request $request)
    {
        try {
            $query = Comment::query();

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by content type
            if ($request->has('type') && $request->type) {
                $modelClass = match($request->type) {
                    'movie' => Movie::class,
                    'tvshow' => TVShow::class,
                    'episode' => Episode::class,
                    default => null,
                };
                if ($modelClass) {
                    $query->where('commentable_type', $modelClass);
                }
            }

            // Filter by content ID
            if ($request->has('content_id') && $request->content_id) {
                $query->where('commentable_id', $request->content_id);
            }

            // Filter by email
            if ($request->has('email') && $request->email) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            // Filter by admin replies only
            if ($request->has('admin_replies') && $request->admin_replies) {
                $query->where('is_admin_reply', true);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('comment', 'like', '%' . $searchTerm . '%');
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $order = $request->get('order', 'desc');
            $query->orderBy($sortBy, $order);

            // Pagination
            $page = max(1, (int) $request->get('page', 1));
            $limit = min(100, max(1, (int) $request->get('limit', 20)));
            $offset = ($page - 1) * $limit;

            $total = $query->count();
            $comments = $query->with(['commentable', 'parent', 'admin'])
                ->offset($offset)
                ->limit($limit)
                ->get();

            $totalPages = (int) ceil($total / $limit);

            // Format comments
            $formattedComments = $comments->map(function($comment) {
                return $this->formatCommentForAdmin($comment);
            });

            // Get stats
            $stats = [
                'total' => Comment::count(),
                'pending' => Comment::where('status', 'pending')->count(),
                'approved' => Comment::where('status', 'approved')->count(),
                'rejected' => Comment::where('status', 'rejected')->count(),
                'spam' => Comment::where('status', 'spam')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'comments' => $formattedComments,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $total,
                        'total_pages' => $totalPages,
                        'has_next' => $page < $totalPages,
                        'has_prev' => $page > 1,
                    ],
                    'stats' => $stats,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching comments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single comment details
     */
    public function show($id)
    {
        try {
            $comment = Comment::with(['commentable', 'parent', 'replies', 'admin'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'comment' => $this->formatCommentForAdmin($comment)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found'
            ], 404);
        }
    }

    /**
     * Update comment status or reply as admin
     */
    public function update(Request $request, $id)
    {
        try {
            $comment = Comment::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|in:pending,approved,rejected,spam',
                'comment' => 'sometimes|string|min:3|max:5000', // For admin replies
                'admin_id' => 'sometimes|integer|exists:admins,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];

            // Update status
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }

            // If comment field is provided, create an admin reply
            if ($request->has('comment') && $request->comment) {
                // Get admin ID from request
                $adminId = $request->admin_id;
                
                // If not provided, try to get from token
                // Since admin auth doesn't use Laravel guards, get admin from token
                if (!$adminId) {
                    $admin = $this->getAdminFromRequest($request);
                    if ($admin) {
                        $adminId = $admin->id;
                    }
                }

                // Create admin reply
                $adminReply = Comment::create([
                    'commentable_type' => $comment->commentable_type,
                    'commentable_id' => $comment->commentable_id,
                    'parent_id' => $comment->id,
                    'name' => 'Admin',
                    'email' => config('mail.from.address'),
                    'comment' => $request->comment,
                    'status' => 'approved',
                    'admin_id' => $adminId,
                    'is_admin_reply' => true,
                    'ip_address' => $request->ip(),
                ]);

                // Notify the original commenter
                try {
                    $content = $comment->commentable;
                    Mail::to($comment->email)->send(
                        new AdminReplyNotification($adminReply, $comment, $content)
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send admin reply notification: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Admin reply added and notification sent',
                    'data' => [
                        'comment' => $this->formatCommentForAdmin($comment->fresh()),
                        'admin_reply' => $this->formatCommentForAdmin($adminReply)
                    ]
                ]);
            }

            // Update comment
            if (!empty($updateData)) {
                $comment->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully',
                'data' => [
                    'comment' => $this->formatCommentForAdmin($comment->fresh())
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete comment
     */
    public function destroy($id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update comments
     */
    public function bulkUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'comment_ids' => 'required|array',
                'comment_ids.*' => 'integer|exists:comments,id',
                'status' => 'required|in:pending,approved,rejected,spam',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = Comment::whereIn('id', $request->comment_ids)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} comment(s) updated successfully",
                'data' => [
                    'updated_count' => $updated
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error bulk updating comments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export all user emails as JSON
     */
    public function exportEmails(Request $request)
    {
        try {
            $query = Comment::query();

            // Filter by status if provided
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Get unique emails
            $emails = $query->select('email', 'name')
                ->distinct()
                ->orderBy('email')
                ->get()
                ->map(function($comment) {
                    return [
                        'email' => $comment->email,
                        'name' => $comment->name,
                    ];
                });

            return response()->json($emails->toArray(), 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="user_emails_' . date('Y-m-d') . '.json"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting emails: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get admin from request token (similar to AdminAuthController profile method)
     */
    private function getAdminFromRequest(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return null;
            }
            
            // Since token validation is simplified, get the first active admin
            // In production, implement proper token validation with admin_sessions table
            $admin = Admin::where('is_active', true)->first();
            return $admin;
        } catch (\Exception $e) {
            \Log::error('Error getting admin from request: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Format comment for admin view
     */
    private function formatCommentForAdmin($comment)
    {
        $content = $comment->commentable;
        $contentInfo = null;

        if ($content) {
            if ($content instanceof Movie) {
                $contentInfo = [
                    'type' => 'movie',
                    'id' => $content->id,
                    'title' => $content->title,
                ];
            } elseif ($content instanceof TVShow) {
                $contentInfo = [
                    'type' => 'tvshow',
                    'id' => $content->id,
                    'name' => $content->name,
                ];
            } elseif ($content instanceof Episode) {
                $contentInfo = [
                    'type' => 'episode',
                    'id' => $content->id,
                    'name' => $content->name,
                    'tv_show' => $content->season->tvShow->name ?? null,
                ];
            }
        }

        return [
            'id' => $comment->id,
            'name' => $comment->name,
            'email' => $comment->email,
            'comment' => $comment->comment,
            'status' => $comment->status,
            'is_admin_reply' => $comment->is_admin_reply,
            'parent_id' => $comment->parent_id,
            'parent' => $comment->parent ? [
                'id' => $comment->parent->id,
                'name' => $comment->parent->name,
                'comment' => substr($comment->parent->comment, 0, 100) . '...',
            ] : null,
            'replies_count' => $comment->allReplies()->count(),
            'content' => $contentInfo,
            'admin' => $comment->admin ? [
                'id' => $comment->admin->id,
                'name' => $comment->admin->name,
            ] : null,
            'ip_address' => $comment->ip_address,
            'like_count' => $comment->like_count,
            'dislike_count' => $comment->dislike_count,
            'created_at' => $comment->created_at->toISOString(),
            'updated_at' => $comment->updated_at->toISOString(),
        ];
    }
}
