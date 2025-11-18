<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Country;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Http\Request;

class UtilsApiController extends Controller
{
    public function all(Request $request)
    {
        try {
            $genres = Genre::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'tmdb_id']);
            
            $countries = Country::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'slug']);
            
            $categories = Category::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description']);
            
            $languages = Language::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'native_name']);

            // Generate years from 2024 down to 1950 (or earlier if needed)
            $currentYear = (int) date('Y');
            $years = [];
            for ($year = $currentYear; $year >= 1950; $year--) {
                $years[] = $year;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'genres' => $genres,
                    'countries' => $countries,
                    'categories' => $categories,
                    'languages' => $languages,
                    'years' => $years,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching utility data: ' . $e->getMessage()
            ], 500);
        }
    }
}

