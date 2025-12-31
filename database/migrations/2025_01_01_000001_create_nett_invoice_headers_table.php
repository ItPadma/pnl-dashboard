<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nett_invoice_headers', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaksi')->nullable();
            $table->string('pt')->nullable();
            $table->string('principal')->nullable();
            $table->string('depo')->nullable();
            $table->string('no_invoice')->unique();
            $table->decimal('invoice_value_original', 20, 2)->default(0);
            $table->decimal('invoice_value_nett', 20, 2)->default(0);
            $table->string('mp_bulan', 2)->nullable();
            $table->string('mp_tahun', 4)->nullable();
            $table->boolean('is_checked')->default(false);
            $table->boolean('is_downloaded')->default(false);
            $table->string('status')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nett_invoice_headers');
    }
};
