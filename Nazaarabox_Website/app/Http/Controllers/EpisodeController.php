<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    public function today()
    {
        return view('episodes.today');
    }
}

