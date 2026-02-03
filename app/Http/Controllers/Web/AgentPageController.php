<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AgentPageController extends Controller
{
    public function index()
    {
        return view('agents.index');
    }
}
