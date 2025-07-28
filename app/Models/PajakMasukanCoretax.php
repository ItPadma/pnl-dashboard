<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PajakMasukanCoretax extends Model
{
    protected $table = 'pajak_masukan_coretax';

    protected $fillable = [
        'npwp_penjual',
        'nama_penjual',
        'nomor_faktur_pajak',
        'tanggal_faktur_pajak',
        'masa_pajak',
        'tahun',
        'masa_pajak_pengkreditkan',
        'tahun_pajak_pengkreditan',
        'status_faktur',
        'harga_jual_dpp',
        'dpp_nilai_lain',
        'ppn',
        'ppnbm',
        'perekam',
        'nomor_sp2d',
        'valid',
        'dilaporkan',
        'dilaporkan_oleh_penjual'
    ];
}
