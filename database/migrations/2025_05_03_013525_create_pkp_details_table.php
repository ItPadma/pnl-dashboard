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
        Schema::create('pkp_details', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->nullable();
            $table->string('no_do')->nullable();
            $table->string('kode_produk')->nullable();
            $table->integer('qty_pcs')->nullable();
            $table->decimal('hargasatuan_sblm_ppn', 15, 2)->nullable();
            $table->decimal('disc', 15, 2)->nullable();
            $table->decimal('hargatotal_sblm_ppn', 15, 2)->nullable();
            $table->decimal('dpp', 15, 2)->nullable();
            $table->decimal('ppn', 15, 2)->nullable();
            $table->date('tgl_faktur_pajak')->nullable();
            $table->string('depo')->nullable();
            $table->string('area')->nullable();
            $table->string('nama_produk')->nullable();
            $table->string('npwp_customer')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('nama_customer_sistem')->nullable();
            $table->text('alamat_sistem')->nullable();
            $table->string('type_pajak')->nullable();
            $table->string('satuan')->nullable();
            $table->string('nama_sesuai_npwp')->nullable();
            $table->text('alamat_npwp_lengkap')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('no_fp')->nullable();
            $table->string('brand')->nullable();
            $table->string('type_jual')->nullable();
            $table->string('kode_jenis_fp')->nullable();
            $table->string('fp_normal_pengganti')->nullable();
            $table->string('nik')->nullable();
            $table->decimal('dpp_lain', 15, 2)->nullable();
            $table->string('id_tku_pembeli')->nullable();
            $table->string('barang_jasa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pkp_details');
    }
};
