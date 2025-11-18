<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->all();
        return view('movies.index', compact('filters'));
    }

    public function show($id)
    {
        return view('movies.show', compact('id'));
    }

    public function trending()
    {
        return view('movies.trending');
    }

    public function topRated()
    {
        return view('movies.top-rated');
    }

    public function today()
    {
        return view('movies.today');
    }
}

