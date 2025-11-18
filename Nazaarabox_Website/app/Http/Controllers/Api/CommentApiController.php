<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\TVShow;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\CommentReplyNotification;

class CommentApiController extends Controller
{
    /**
     * Get comments for a content (movie, TV show, or episode)
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:movie,tvshow,episode',
                'id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->type;
            $id = $request->id;

            // Map type to model class
            $modelClass = match($type) {
                'movie' => Movie::class,
                'tvshow' => TVShow::class,
                'episode' => Episode::class,
                default => null,
            };

            if (!$modelClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid content type'
                ], 422);
            }

            // Get top-level approved comments with nested replies
            $comments = Comment::where('commentable_type', $modelClass)
                ->where('commentable_id', $id)
                ->where('status', 'approved')
                ->whereNull('parent_id')
                ->with(['replies' => function($query) {
                    $query->where('status', 'approved')
                        ->orderBy('created_at', 'asc')
                        ->with('replies');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            // Format comments with nested structure
            $formattedComments = $comments->map(function($comment) {
                return $this->formatComment($comment);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'comments' => $formattedComments,
                    'total' => $comments->count()
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
     * Submit a new comment (public endpoint - no authentication required)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:movie,tvshow,episode',
                'id' => 'required|integer',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'comment' => 'required|string|min:3|max:5000',
                'parent_id' => 'nullable|integer|exists:comments,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->type;
            $id = $request->id;

            // Map type to model class
            $modelClass = match($type) {
                'movie' => Movie::class,
                'tvshow' => TVShow::class,
                'episode' => Episode::class,
                default => null,
            };

            if (!$modelClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid content type'
                ], 422);
            }

            // Verify content exists
            $content = $modelClass::find($id);
            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => ucfirst($type) . ' not found'
                ], 404);
            }

            // If parent_id is provided, verify it exists and belongs to same content
            if ($request->parent_id) {
                $parentComment = Comment::find($request->parent_id);
                if (!$parentComment || 
                    $parentComment->commentable_type !== $modelClass || 
                    $parentComment->commentable_id !== $id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid parent comment'
                    ], 422);
                }
            }

            // Create comment
            $comment = Comment::create([
                'commentable_type' => $modelClass,
                'commentable_id' => $id,
                'parent_id' => $request->parent_id,
                'name' => $request->name,
                'email' => $request->email,
                'comment' => $request->comment,
                'status' => 'pending', // All comments start as pending
                'is_admin_reply' => false,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // If this is a reply, notify the parent commenter
            if ($request->parent_id && $parentComment) {
                try {
                    Mail::to($parentComment->email)->send(
                        new CommentReplyNotification($comment, $parentComment, $content)
                    );
                } catch (\Exception $e) {
                    // Log error but don't fail the request
                    \Log::error('Failed to send comment reply notification: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment submitted successfully. It will be visible after approval.',
                'data' => [
                    'comment' => $this->formatComment($comment)
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format comment with nested replies
     */
    private function formatComment($comment)
    {
        $formatted = [
            'id' => $comment->id,
            'name' => $comment->name,
            // Email not included in public API for privacy
            'comment' => $comment->comment,
            'status' => $comment->status,
            'is_admin_reply' => $comment->is_admin_reply,
            'like_count' => $comment->like_count,
            'dislike_count' => $comment->dislike_count,
            'created_at' => $comment->created_at->toISOString(),
            'replies' => [],
        ];

        // Add nested replies
        if ($comment->replies && $comment->replies->count() > 0) {
            $formatted['replies'] = $comment->replies->map(function($reply) {
                return $this->formatComment($reply);
            });
        }

        return $formatted;
    }
}
