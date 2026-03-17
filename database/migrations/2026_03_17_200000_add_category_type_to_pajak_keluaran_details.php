<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a persisted computed column `category_type` that classifies each
     * row into pkp / pkpnppn / npkp / npkpnppn / retur / moved based on
     * existing column values and a subquery against master_pkp.
     *
     * Only runs on SQL Server; skipped for SQLite (testing).
     */
    public function up(): void
    {
        // Only run on SQL Server — skip for SQLite (testing)
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::statement("
            ALTER TABLE pajak_keluaran_details
            ADD category_type AS (
                CASE
                    WHEN has_moved = 'y' THEN moved_to
                    WHEN tipe_ppn = 'PPN' AND qty_pcs > 0 AND has_moved = 'n'
                         AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                    THEN 'pkp'
                    WHEN tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n'
                         AND customer_id IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                    THEN 'pkpnppn'
                    WHEN tipe_ppn = 'PPN' AND has_moved = 'n'
                         AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                         AND (hargatotal_sblm_ppn > 0 OR hargatotal_sblm_ppn <= -1000000)
                    THEN 'npkp'
                    WHEN tipe_ppn = 'NON-PPN' AND qty_pcs > 0 AND has_moved = 'n'
                         AND customer_id NOT IN (SELECT IDPelanggan FROM master_pkp WHERE is_active = 1)
                    THEN 'npkpnppn'
                    WHEN qty_pcs < 0 AND has_moved = 'n' THEN 'retur'
                    ELSE NULL
                END
            ) PERSISTED
        ");

        // Add index on category_type for query performance
        Schema::table('pajak_keluaran_details', function (Blueprint $table) {
            $table->index('category_type', 'idx_pajak_keluaran_category_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run on SQL Server — skip for SQLite (testing)
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        Schema::table('pajak_keluaran_details', function (Blueprint $table) {
            $table->dropIndex('idx_pajak_keluaran_category_type');
        });

        DB::statement('ALTER TABLE pajak_keluaran_details DROP COLUMN category_type');
    }
};
