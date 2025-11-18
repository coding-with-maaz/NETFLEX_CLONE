<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt dynamically
     */
    public function index()
    {
        $sitemapUrl = url('/sitemap.xml');
        
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "\n";
        $robots .= "Sitemap: {$sitemapUrl}\n";
        
        return response($robots, 200)
            ->header('Content-Type', 'text/plain');
    }
}

