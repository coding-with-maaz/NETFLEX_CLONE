<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\TVShow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    /**
     * Display the app download page
     */
    public function index()
    {
        // Fetch featured movies for the phone mockup
        $featuredMovies = Movie::where('status', 'active')
            ->where('is_featured', true)
            ->orderBy('view_count', 'desc')
            ->limit(6)
            ->get();
        
        // If no featured movies, get popular ones
        if ($featuredMovies->isEmpty()) {
            $featuredMovies = Movie::where('status', 'active')
                ->orderBy('view_count', 'desc')
                ->limit(6)
                ->get();
        }

        // Fetch popular TV shows
        $popularTVShows = TVShow::where('status', 'active')
            ->orderBy('view_count', 'desc')
            ->limit(6)
            ->get();

        return view('download', compact('featuredMovies', 'popularTVShows'));
    }

    /**
     * Download the Android APK file
     */
    public function downloadApk(): BinaryFileResponse
    {
        $apkPath = public_path('app-release.apk');
        
        if (!file_exists($apkPath)) {
            abort(404, 'APK file not found');
        }

        return response()->download($apkPath, 'nazaarabox.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}

