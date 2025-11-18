<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmbedReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmbedReportNotification;

class AdminEmbedReportController extends Controller
{
    /**
     * Get all embed reports with filters
     */
    public function index(Request $request)
    {
        try {
            $query = EmbedReport::with(['processedBy']);

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

            // Sort - validate sort_by field
            $sortBy = $request->get('sort_by', 'reported_at');
            $allowedSortFields = ['reported_at', 'report_count', 'report_type', 'status', 'created_at', 'updated_at'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'reported_at';
            }
            $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min((int)$request->get('per_page', 20), 100);
            $reports = $query->paginate($perPage);

            // Load content and embed relationships manually
            // We can't use eager loading with conditional relationships, so we load them directly
            $reports->getCollection()->each(function ($report) {
                try {
                    // Load content based on type
                    if ($report->content_type === 'movie') {
                        $movie = \App\Models\Movie::find($report->content_id);
                        $report->setRelation('content', $movie);
                    } elseif ($report->content_type === 'episode') {
                        // Load episode with season and TV show relationships
                        try {
                            $episode = \App\Models\Episode::with(['season.tvShow'])->find($report->content_id);
                            $report->setRelation('content', $episode);
                        } catch (\Exception $e) {
                            // If eager loading fails, try without eager loading
                            $episode = \App\Models\Episode::find($report->content_id);
                            if ($episode) {
                                try {
                                    $episode->load('season.tvShow');
                                } catch (\Exception $e2) {
                                    // If season doesn't exist, just load episode
                                }
                            }
                            $report->setRelation('content', $episode);
                        }
                    }
                    
                    // Load embed if embed_id exists
                    if ($report->embed_id) {
                        try {
                            if ($report->content_type === 'movie') {
                                $embed = \App\Models\MovieEmbed::find($report->embed_id);
                                $report->setRelation('embed', $embed);
                            } elseif ($report->content_type === 'episode') {
                                $embed = \App\Models\EpisodeEmbed::find($report->embed_id);
                                $report->setRelation('embed', $embed);
                            }
                        } catch (\Exception $e) {
                            // Embed not found, set to null
                            $report->setRelation('embed', null);
                        }
                    }
                } catch (\Exception $e) {
                    // Silently handle relationship loading errors
                    \Log::warning('Failed to load relationships for embed report', [
                        'report_id' => $report->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            });

            // Statistics
            $stats = [
                'total' => EmbedReport::count(),
                'pending' => EmbedReport::where('status', 'pending')->count(),
                'reviewed' => EmbedReport::where('status', 'reviewed')->count(),
                'fixed' => EmbedReport::where('status', 'fixed')->count(),
                'dismissed' => EmbedReport::where('status', 'dismissed')->count(),
                'movies' => EmbedReport::where('content_type', 'movie')->count(),
                'episodes' => EmbedReport::where('content_type', 'episode')->count(),
                'not_working' => EmbedReport::where('report_type', 'not_working')->count(),
                'wrong_content' => EmbedReport::where('report_type', 'wrong_content')->count(),
                'poor_quality' => EmbedReport::where('report_type', 'poor_quality')->count(),
                'broken_link' => EmbedReport::where('report_type', 'broken_link')->count(),
            ];

            // Convert reports to array to avoid serialization issues
            $reportsArray = $reports->getCollection()->map(function ($report) {
                try {
                    $reportArray = $report->toArray();
                    
                    // Add content information safely
                    if (isset($report->content) && $report->content) {
                        try {
                            if ($report->content_type === 'movie') {
                                $reportArray['content'] = [
                                    'id' => $report->content->id ?? null,
                                    'title' => $report->content->title ?? null,
                                    'poster_path' => $report->content->poster_path ?? null,
                                ];
                            } elseif ($report->content_type === 'episode') {
                                $contentData = [
                                    'id' => $report->content->id ?? null,
                                    'name' => $report->content->name ?? null,
                                    'episode_number' => $report->content->episode_number ?? null,
                                ];
                                if (isset($report->content->season) && $report->content->season) {
                                    $contentData['season'] = [
                                        'id' => $report->content->season->id ?? null,
                                        'season_number' => $report->content->season->season_number ?? null,
                                    ];
                                    if (isset($report->content->season->tvShow) && $report->content->season->tvShow) {
                                        $contentData['season']['tv_show'] = [
                                            'id' => $report->content->season->tvShow->id ?? null,
                                            'name' => $report->content->season->tvShow->name ?? null,
                                        ];
                                    }
                                }
                                $reportArray['content'] = $contentData;
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to serialize content for embed report', [
                                'report_id' => $report->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    // Add embed information safely
                    if (isset($report->embed) && $report->embed) {
                        try {
                            $reportArray['embed'] = [
                                'id' => $report->embed->id ?? null,
                                'server_type' => $report->embed->server_type ?? null,
                            ];
                        } catch (\Exception $e) {
                            \Log::warning('Failed to serialize embed for embed report', [
                                'report_id' => $report->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    // Add processedBy information safely
                    if (isset($report->processedBy) && $report->processedBy) {
                        try {
                            $reportArray['processed_by'] = [
                                'id' => $report->processedBy->id ?? null,
                                'name' => $report->processedBy->name ?? null,
                            ];
                        } catch (\Exception $e) {
                            \Log::warning('Failed to serialize processedBy for embed report', [
                                'report_id' => $report->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    return $reportArray;
                } catch (\Exception $e) {
                    \Log::error('Failed to serialize embed report', [
                        'report_id' => $report->id ?? null,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Return basic report data without relationships
                    return $report->toArray();
                }
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'reports' => $reportsArray,
                    'pagination' => [
                        'current_page' => $reports->currentPage(),
                        'last_page' => $reports->lastPage(),
                        'per_page' => $reports->perPage(),
                        'total' => $reports->total(),
                    ],
                    'stats' => $stats
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch embed reports: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get single embed report
     */
    public function show($id)
    {
        try {
            $report = EmbedReport::with(['processedBy'])->findOrFail($id);
            
            // Load content and embed relationships manually
            if ($report->content_type === 'movie') {
                $movie = \App\Models\Movie::find($report->content_id);
                $report->setRelation('content', $movie);
            } elseif ($report->content_type === 'episode') {
                // Load episode with season and TV show relationships
                $episode = \App\Models\Episode::with(['season.tvShow'])->find($report->content_id);
                $report->setRelation('content', $episode);
            }
            
            if ($report->embed_id) {
                if ($report->content_type === 'movie') {
                    $embed = \App\Models\MovieEmbed::find($report->embed_id);
                    $report->setRelation('embed', $embed);
                } elseif ($report->content_type === 'episode') {
                    $embed = \App\Models\EpisodeEmbed::find($report->embed_id);
                    $report->setRelation('embed', $embed);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'report' => $report
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch embed report: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Update embed report status
     */
    public function update(Request $request, $id)
    {
        $embedReport = EmbedReport::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,reviewed,fixed,dismissed',
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
        
        $oldStatus = $embedReport->status;
        $oldNotes = $embedReport->admin_notes;
        
        $embedReport->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'processed_at' => now(),
            'processed_by' => $adminId,
        ]);

        // Send email notification if email exists and (status changed or notes added/updated)
        if ($embedReport->email && 
            ($oldStatus !== $request->status || $oldNotes !== $request->admin_notes)) {
            try {
                // Reload relationships before sending email
                if ($embedReport->content_type === 'movie') {
                    $movie = \App\Models\Movie::find($embedReport->content_id);
                    $embedReport->setRelation('content', $movie);
                } elseif ($embedReport->content_type === 'episode') {
                    $episode = \App\Models\Episode::with(['season.tvShow'])->find($embedReport->content_id);
                    $embedReport->setRelation('content', $episode);
                }
                
                Mail::to($embedReport->email)->send(
                    new EmbedReportNotification($embedReport)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send embed report notification: ' . $e->getMessage());
                // Don't fail the request if email fails
            }
        }

        // Reload relationships manually
        $embedReport->load('processedBy');
        if ($embedReport->content_type === 'movie') {
            $movie = \App\Models\Movie::find($embedReport->content_id);
            $embedReport->setRelation('content', $movie);
        } elseif ($embedReport->content_type === 'episode') {
            // Load episode with season and TV show relationships
            $episode = \App\Models\Episode::with(['season.tvShow'])->find($embedReport->content_id);
            $embedReport->setRelation('content', $episode);
        }
        if ($embedReport->embed_id) {
            if ($embedReport->content_type === 'movie') {
                $embed = \App\Models\MovieEmbed::find($embedReport->embed_id);
                $embedReport->setRelation('embed', $embed);
            } elseif ($embedReport->content_type === 'episode') {
                $embed = \App\Models\EpisodeEmbed::find($embedReport->embed_id);
                $embedReport->setRelation('embed', $embed);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Embed report updated successfully',
            'data' => [
                'report' => $embedReport
            ]
        ], 200);
    }

    /**
     * Delete embed report
     */
    public function destroy($id)
    {
        $embedReport = EmbedReport::findOrFail($id);
        $embedReport->delete();

        return response()->json([
            'success' => true,
            'message' => 'Embed report deleted successfully'
        ], 200);
    }

    /**
     * Bulk update reports
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:embed_reports,id',
            'status' => 'required|in:pending,reviewed,fixed,dismissed',
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
        
        // Get reports before update to check for changes
        $reports = EmbedReport::whereIn('id', $request->ids)->get();
        
        $updated = EmbedReport::whereIn('id', $request->ids)->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'processed_at' => now(),
            'processed_by' => $adminId,
        ]);

        // Send email notifications for updated reports
        foreach ($reports as $report) {
            if ($report->email) {
                try {
                    // Reload the report with fresh data
                    $freshReport = EmbedReport::find($report->id);
                    
                    // Load relationships
                    if ($freshReport->content_type === 'movie') {
                        $movie = \App\Models\Movie::find($freshReport->content_id);
                        $freshReport->setRelation('content', $movie);
                    } elseif ($freshReport->content_type === 'episode') {
                        $episode = \App\Models\Episode::with(['season.tvShow'])->find($freshReport->content_id);
                        $freshReport->setRelation('content', $episode);
                    }
                    
                    Mail::to($freshReport->email)->send(
                        new EmbedReportNotification($freshReport)
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send embed report notification: ' . $e->getMessage());
                    // Continue with other reports even if one fails
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} embed report(s) successfully",
            'data' => [
                'updated_count' => $updated
            ]
        ], 200);
    }
}

