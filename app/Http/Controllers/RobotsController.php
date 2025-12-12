<?php

namespace App\Http\Controllers;

class RobotsController extends Controller
{
    public function index()
    {
        $content = view('robots')->render();

        return response($content)
            ->header('Content-Type', 'text/plain');
    }
}
