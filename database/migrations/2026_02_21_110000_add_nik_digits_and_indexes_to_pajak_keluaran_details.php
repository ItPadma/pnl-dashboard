<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a persisted computed column `nik_digits` that strips non-digit
     * characters from `nik`, and composite indexes to speed up Non Standar
     * tab queries in Pajak Keluaran.
     */
    public function up(): void
    {
        // 1. Add persisted computed column for nik digit extraction
        DB::statement("
            ALTER TABLE pajak_keluaran_details
            ADD nik_digits AS (
                REPLACE(REPLACE(LTRIM(RTRIM(ISNULL(nik, ''))), '-', ''), ' ', '')
            ) PERSISTED
        ");

        // 2. Composite index for Non Standar scope filtering
        //    Covers: has_moved, moved_to, nik_digits conditions
        //    Includes: columns needed for type categorization (avoids key lookups)
        DB::statement("
            CREATE NONCLUSTERED INDEX idx_nonstandar_scope
            ON pajak_keluaran_details (has_moved, moved_to, nik_digits)
            INCLUDE (customer_id, tipe_ppn, qty_pcs, hargatotal_sblm_ppn, tgl_faktur_pajak)
        ");

        // 3. Index for period-based filtering (used by all tabs)
        DB::statement("
            CREATE NONCLUSTERED INDEX idx_period_filter
            ON pajak_keluaran_details (tgl_faktur_pajak)
            INCLUDE (company, brand, depo, has_moved, moved_to)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_period_filter ON pajak_keluaran_details");
        DB::statement("DROP INDEX IF EXISTS idx_nonstandar_scope ON pajak_keluaran_details");
        DB::statement("ALTER TABLE pajak_keluaran_details DROP COLUMN IF EXISTS nik_digits");
    }
};
