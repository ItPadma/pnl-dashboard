<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure computed column `nik_digits` really removes NPWP separators,
     * including dot (.) and dash (-), so length checks use digits-only value.
     */
    public function up(): void
    {
        DB::statement("
            IF EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = 'idx_nonstandar_scope'
                  AND object_id = OBJECT_ID('pajak_keluaran_details')
            )
            DROP INDEX idx_nonstandar_scope ON pajak_keluaran_details
        ");

        DB::statement("
            IF COL_LENGTH('pajak_keluaran_details', 'nik_digits') IS NOT NULL
            ALTER TABLE pajak_keluaran_details DROP COLUMN nik_digits
        ");

        DB::statement("
            ALTER TABLE pajak_keluaran_details
            ADD nik_digits AS (
                REPLACE(REPLACE(REPLACE(LTRIM(RTRIM(ISNULL(nik, ''))), '.', ''), '-', ''), ' ', '')
            ) PERSISTED
        ");

        DB::statement('
            CREATE NONCLUSTERED INDEX idx_nonstandar_scope
            ON pajak_keluaran_details (has_moved, moved_to, nik_digits)
            INCLUDE (customer_id, tipe_ppn, qty_pcs, hargatotal_sblm_ppn, tgl_faktur_pajak)
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_nonstandar_scope ON pajak_keluaran_details');

        DB::statement("
            IF COL_LENGTH('pajak_keluaran_details', 'nik_digits') IS NOT NULL
            ALTER TABLE pajak_keluaran_details DROP COLUMN nik_digits
        ");

        DB::statement("
            ALTER TABLE pajak_keluaran_details
            ADD nik_digits AS (
                REPLACE(REPLACE(LTRIM(RTRIM(ISNULL(nik, ''))), '-', ''), ' ', '')
            ) PERSISTED
        ");

        DB::statement('
            CREATE NONCLUSTERED INDEX idx_nonstandar_scope
            ON pajak_keluaran_details (has_moved, moved_to, nik_digits)
            INCLUDE (customer_id, tipe_ppn, qty_pcs, hargatotal_sblm_ppn, tgl_faktur_pajak)
        ');
    }
};
