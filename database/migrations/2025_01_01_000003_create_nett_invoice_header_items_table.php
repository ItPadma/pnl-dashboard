<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nett_invoice_header_items', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaksi')->nullable();
            $table->string('no_invoice');
            $table->string('kode_barang')->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('harga_satuan', 20, 2)->default(0);
            $table->decimal('harga_total', 20, 2)->default(0);
            $table->timestamp('created_at')->nullable();

            $table->foreign('no_invoice')
                ->references('no_invoice')
                ->on('nett_invoice_headers')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nett_invoice_header_items');
    }
};
