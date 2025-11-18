<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function show($id, $name = null)
    {
        return view('genre.show', compact('id', 'name'));
    }
}

