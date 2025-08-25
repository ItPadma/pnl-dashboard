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
        broadcast(new UserEvent('info', 'Welcome', 'Selamat datang, '.Auth::user()->name, Auth::user()->id));
        return view('pnl.dashboard.index');
    }
}
