<?php

namespace App\Http\Controllers\PNL;

use App\Http\Controllers\Controller;

class NettInvoiceController extends Controller
{
    public function index()
    {
        return view('pnl.reguler.pajak-keluaran.nett-invoice.index');
    }
}
