<?php

namespace App\Http\Controllers\PNL;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NonRegulerController extends Controller
{
    public function pkIndex()
    {
        return view('pnl.nonreguler.pajak-keluaran.index');
    }

    public function pmIndex()
    {
        return view('pnl.nonreguler.pajak-masukan.index');
    }
}
