<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContentRequestController extends Controller
{
    /**
     * Display the content request page
     */
    public function index()
    {
        return view('request.index');
    }
}

