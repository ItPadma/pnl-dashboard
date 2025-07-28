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
        Schema::create('pajak_masukan_coretax', function (Blueprint $table) {
            $table->id();
            $table->string('npwp_penjual')->nullable();
            $table->string('nama_penjual')->nullable();
            $table->string('nomor_faktur_pajak')->nullable();
            $table->date('tanggal_faktur_pajak')->nullable();
            $table->string('masa_pajak')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('masa_pajak_pengkreditkan')->nullable();
            $table->integer('tahun_pajak_pengkreditan')->nullable();
            $table->string('status_faktur')->nullable();
            $table->decimal('harga_jual_dpp', 30, 4)->nullable();
            $table->decimal('dpp_nilai_lain', 30, 4)->nullable();
            $table->decimal('ppn', 30, 4)->nullable();
            $table->decimal('ppnbm', 30, 4)->nullable();
            $table->string('perekam')->nullable();
            $table->string('nomor_sp2d')->nullable();
            $table->boolean('valid')->nullable();
            $table->boolean('dilaporkan')->nullable();
            $table->boolean('dilaporkan_oleh_penjual')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajak_masukan_coretax');
    }
};
