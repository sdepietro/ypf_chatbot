<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Config;

class ChatPageController extends Controller
{
    public function index()
    {
        $chatType = Config::getValue('chat-type', 'simple');

        if ($chatType === 'advanced') {
            return view('chat.advanced');
        }

        return view('chat.index');
    }
}
