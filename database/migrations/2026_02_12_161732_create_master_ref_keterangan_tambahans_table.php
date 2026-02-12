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
        Schema::create('master_ref_keterangan_tambahan', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->string('kode_transaksi_id')->nullable();
            $table->foreign('kode_transaksi_id')
                ->references('kode')
                ->on('master_ref_kode_transaksi')
                ->onUpdate('cascade');
            $table->string('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_ref_keterangan_tambahan');
    }
};
