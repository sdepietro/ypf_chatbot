<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ConfigPageController extends Controller
{
    public function index()
    {
        return view('configs.index');
    }
}
