<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Performance indexes for RegulerController optimization.
     * These indexes target the most common query patterns:
     * - PKP filtering (customer_id IN/NOT IN with tipe_ppn conditions)
     * - Depo/Company/Brand access filtering
     * - Invoice grouping operations
     * - Status counting (is_checked, is_downloaded)
     *
     * NOTE: This migration uses SQL Server specific syntax.
     * It will be skipped for other database drivers (e.g., SQLite for testing).
     */
    public function up(): void
    {
        // Skip for non-SQL Server databases (e.g., SQLite for testing)
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        // 1. Index untuk PKP filtering (paling kritis)
        // Digunakan di hampir semua query untuk IN/NOT IN customer_id
        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes WHERE name = 'idx_pajak_keluaran_pkp_filter' AND object_id = OBJECT_ID('pajak_keluaran_details')
            )
            CREATE NONCLUSTERED INDEX idx_pajak_keluaran_pkp_filter
            ON pajak_keluaran_details (customer_id, tipe_ppn, has_moved, moved_to)
            INCLUDE (qty_pcs, hargatotal_sblm_ppn, tgl_faktur_pajak, nik_digits)
        ");

        // 2. Index untuk master_pkp lookup (digunakan di subquery JOIN)
        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes WHERE name = 'idx_master_pkp_lookup' AND object_id = OBJECT_ID('master_pkp')
            )
            CREATE NONCLUSTERED INDEX idx_master_pkp_lookup
            ON master_pkp (IDPelanggan, is_active)
            INCLUDE (NamaPKP, AlamatPKP, NoPKP, TypePajak)
        ");

        // 3. Index untuk depo/company/brand access filtering
        // Mempercepat filtering berdasarkan akses user
        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes WHERE name = 'idx_pajak_keluaran_access_filter' AND object_id = OBJECT_ID('pajak_keluaran_details')
            )
            CREATE NONCLUSTERED INDEX idx_pajak_keluaran_access_filter
            ON pajak_keluaran_details (depo, company, brand, tgl_faktur_pajak)
            INCLUDE (has_moved, moved_to, customer_id)
        ");

        // 4. Index untuk invoice grouping operations
        // Mempercepat query yang GROUP BY no_invoice
        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes WHERE name = 'idx_pajak_keluaran_invoice' AND object_id = OBJECT_ID('pajak_keluaran_details')
            )
            CREATE NONCLUSTERED INDEX idx_pajak_keluaran_invoice
            ON pajak_keluaran_details (no_invoice)
            INCLUDE (customer_id, tgl_faktur_pajak, has_moved, moved_to, tipe_ppn, qty_pcs, hargatotal_sblm_ppn, dpp, ppn, nik_digits)
        ");

        // 5. Index untuk status counting (is_checked, is_downloaded)
        // Mempercepat count() queries yang sering digunakan
        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes WHERE name = 'idx_pajak_keluaran_status' AND object_id = OBJECT_ID('pajak_keluaran_details')
            )
            CREATE NONCLUSTERED INDEX idx_pajak_keluaran_status
            ON pajak_keluaran_details (is_checked, is_downloaded, has_moved, moved_to)
            INCLUDE (company, brand, depo, tgl_faktur_pajak, customer_id, tipe_ppn, qty_pcs, hargatotal_sblm_ppn)
        ");

        // 6. Covering index untuk retur queries
        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes WHERE name = 'idx_pajak_keluaran_retur' AND object_id = OBJECT_ID('pajak_keluaran_details')
            )
            CREATE NONCLUSTERED INDEX idx_pajak_keluaran_retur
            ON pajak_keluaran_details (qty_pcs, hargatotal_sblm_ppn, has_moved, moved_to)
            INCLUDE (customer_id, tipe_ppn, tgl_faktur_pajak, nik_digits)
        ");

        // 7. Index untuk nett_invoice_header lookup
        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes WHERE name = 'idx_nett_invoice_header_lookup' AND object_id = OBJECT_ID('nett_invoice_headers')
            )
            CREATE NONCLUSTERED INDEX idx_nett_invoice_header_lookup
            ON nett_invoice_headers (no_invoice)
            INCLUDE (invoice_value_nett)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip for non-SQL Server databases
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS idx_nett_invoice_header_lookup ON nett_invoice_headers');
        DB::statement('DROP INDEX IF EXISTS idx_pajak_keluaran_retur ON pajak_keluaran_details');
        DB::statement('DROP INDEX IF EXISTS idx_pajak_keluaran_status ON pajak_keluaran_details');
        DB::statement('DROP INDEX IF EXISTS idx_pajak_keluaran_invoice ON pajak_keluaran_details');
        DB::statement('DROP INDEX IF EXISTS idx_pajak_keluaran_access_filter ON pajak_keluaran_details');
        DB::statement('DROP INDEX IF EXISTS idx_master_pkp_lookup ON master_pkp');
        DB::statement('DROP INDEX IF EXISTS idx_pajak_keluaran_pkp_filter ON pajak_keluaran_details');
    }
};
