<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add remaining_value to nett_invoice_details
        Schema::table('nett_invoice_details', function (Blueprint $table) {
            $table->decimal('remaining_value', 20, 2)->default(0)->after('invoice_retur_value');
        });

        // Create nett_invoice_histories table
        Schema::create('nett_invoice_histories', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaksi')->nullable();
            $table->string('no_invoice_npkp');
            $table->string('no_invoice_retur');
            $table->decimal('nilai_invoice_npkp', 20, 2)->default(0);
            $table->decimal('nilai_retur_used', 20, 2)->default(0);
            $table->decimal('nilai_nett', 20, 2)->default(0);
            $table->decimal('remaining_value', 20, 2)->default(0);
            $table->string('status')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nett_invoice_histories');

        Schema::table('nett_invoice_details', function (Blueprint $table) {
            $table->dropColumn('remaining_value');
        });
    }
};
