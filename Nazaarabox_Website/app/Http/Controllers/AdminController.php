<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function management()
    {
        return view('admin.management');
    }

    public function movies()
    {
        return view('admin.movies.index');
    }

    public function movieCreate()
    {
        return view('admin.movies.create');
    }

    public function movieDetail($id)
    {
        return view('admin.movies.detail', compact('id'));
    }

    public function tvshows()
    {
        return view('admin.tvshows.index');
    }

    public function tvshowCreate()
    {
        return view('admin.tvshows.create');
    }

    public function tvshowDetail($id)
    {
        return view('admin.tvshows.detail', compact('id'));
    }

    public function tvshowSeasons($id)
    {
        return view('admin.tvshows.seasons', compact('id'));
    }

    public function featured()
    {
        return view('admin.featured');
    }

    public function requests()
    {
        return view('admin.requests');
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function comments()
    {
        return view('admin.comments');
    }

    public function ads()
    {
        return view('admin.ads');
    }
}
