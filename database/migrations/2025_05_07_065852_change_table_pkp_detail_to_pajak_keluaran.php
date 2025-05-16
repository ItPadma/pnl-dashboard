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
        // alter table pkp_detail
        // add column is_checked and is_downloaded boolean default 0
        Schema::rename('pkp_details', 'pajak_keluaran_details');
        Schema::table('pajak_keluaran_details', function (Blueprint $table) {
            $table->boolean('is_checked')->default(0)->after('barang_jasa')->nullable();
            $table->boolean('is_downloaded')->default(0)->after('is_checked')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop column is_checked and is_downloaded
        Schema::table('pajak_keluaran_details', function (Blueprint $table) {
            $table->dropColumn('is_checked');
            $table->dropColumn('is_downloaded');
        });
        Schema::rename('pajak_keluaran_details', 'pkp_details');
    }
};
