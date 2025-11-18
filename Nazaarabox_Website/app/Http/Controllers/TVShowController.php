<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TVShowController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->all();
        return view('tvshows.index', compact('filters'));
    }

    public function show($id)
    {
        return view('tvshows.show', compact('id'));
    }

    public function popular()
    {
        return view('tvshows.popular');
    }

    public function topRated()
    {
        return view('tvshows.top-rated');
    }
}

