<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ToolsController extends Controller
{
    public function __invoke(): View
    {
        return view('tools.index');
    }
}
