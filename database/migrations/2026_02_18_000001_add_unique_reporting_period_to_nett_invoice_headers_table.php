<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nett_invoice_headers', function (Blueprint $table) {
            $table->index(['mp_tahun', 'mp_bulan', 'no_invoice'], 'nett_invoice_headers_reporting_period_idx');
        });
    }

    public function down(): void
    {
        Schema::table('nett_invoice_headers', function (Blueprint $table) {
            $table->dropIndex('nett_invoice_headers_reporting_period_idx');
        });
    }
};
