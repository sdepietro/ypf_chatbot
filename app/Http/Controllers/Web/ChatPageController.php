<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ChatPageController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }
}
