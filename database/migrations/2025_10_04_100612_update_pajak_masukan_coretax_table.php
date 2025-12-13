<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pajak_masukan_coretax', function (Blueprint $table) {
            $table->string('kode_transaksi')->after('nama_penjual')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pajak_masukan_coretax', function (Blueprint $table) {
            $table->dropColumn('kode_transaksi');
        });
    }
};
