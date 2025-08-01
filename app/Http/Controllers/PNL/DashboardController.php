<?php

namespace App\Http\Controllers\PNL;

use App\Events\UserEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pnl.dashboard.index');
    }
}
