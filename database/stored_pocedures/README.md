# Stored Procedures

This folder contains optimized SQL Server stored procedures for the Pajak application.

## Overview

The stored procedures are designed to replace PHP-based data synchronization logic with highly optimized SQL Server operations that run significantly faster and provide better transaction control.

## Available Stored Procedures

### 1. `sp_SyncPajakKeluaranDetailFromLive.sql`

**Purpose:** Synchronizes pajak keluaran detail data from live database (bosnet_live) to `pajak_keluaran_details` table.

**Best For:** Data volumes under 100,000 rows or maintenance window operations.

**Key Features:**
- Single MERGE statement for atomic UPSERT operations
- CTE-based bonus pre-calculation (eliminates repeated subqueries)
- XML-based string splitting (SQL Server 2012 compatible)
- Full rollback support on errors
- NOLOCK hints on source tables

**Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| @pt | NVARCHAR(MAX) | Company filter (comma-separated or 'all') |
| @brand | NVARCHAR(MAX) | Brand filter (comma-separated or 'all') |
| @depo | NVARCHAR(100) | Depo/Workplace filter ('all' for no filter) |
| @start | DATE | Start date (YYYY-MM-DD) |
| @end | DATE | End date (YYYY-MM-DD) |
| @tipe | NVARCHAR(MAX) | Additional type filter (optional) |
| @user_depo | NVARCHAR(MAX) | User's depo access (pipe-separated) |
| @rows_affected | INT OUTPUT | Number of rows affected |
| @error_message | NVARCHAR(MAX) OUTPUT | Error message if any |

**Return Values:**
- `0` = Success
- `1` = No data found
- `-1` = Error occurred

---

### 2. `sp_SyncPajakKeluaranDetailFromLive_Batch.sql`

**Purpose:** Batch processing version for very large datasets (>100K rows).

**Best For:** Large data volumes or operations during business hours with concurrent users.

**Key Features:**
- Configurable batch size (100 - 50,000 rows)
- Progress tracking with debug mode
- Partial commit support (each batch commits independently)
- Resume capability on failure
- All optimizations from the standard version

**Additional Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| @batch_size | INT | Rows per batch (default: 5000) |
| @debug_mode | BIT | Print progress messages (0/1) |
| @total_rows_affected | INT OUTPUT | Total rows affected |
| @batches_processed | INT OUTPUT | Number of batches completed |

**Batch Size Recommendations:**
| Scenario | Recommended Size |
|----------|------------------|
| Low activity, fast storage | 10,000 - 20,000 |
| Normal activity | 5,000 - 10,000 |
| High concurrency | 2,000 - 5,000 |
| Limited tempdb | 1,000 - 2,000 |

---

## Helper Functions

### `fn_SplitString`

A table-valued function for splitting delimited strings, compatible with SQL Server 2012 (which lacks `STRING_SPLIT`).

**Usage:**
```sql
SELECT Value FROM dbo.fn_SplitString('A,B,C', ',');
```

---

## Installation

1. Connect to your target SQL Server database
2. Execute the scripts in order:
   - First: `sp_SyncPajakKeluaranDetailFromLive.sql` (includes helper function)
   - Optional: `sp_SyncPajakKeluaranDetailFromLive_Batch.sql`

3. Create recommended indexes on `pajak_keluaran_details`:

```sql
-- Primary composite index for MERGE operation (CRITICAL)
CREATE UNIQUE NONCLUSTERED INDEX IX_PajakKeluaranDetails_InvoiceDoProduct
ON pajak_keluaran_details (no_invoice, no_do, kode_produk)
WITH (FILLFACTOR = 90);

-- Index for date range queries
CREATE NONCLUSTERED INDEX IX_PajakKeluaranDetails_TglFaktur
ON pajak_keluaran_details (tgl_faktur_pajak)
INCLUDE (company, brand, depo);

-- Index for status-based queries
CREATE NONCLUSTERED INDEX IX_PajakKeluaranDetails_Status
ON pajak_keluaran_details (is_checked, is_downloaded)
INCLUDE (no_invoice, no_do);
```

---

## Performance Comparison

| Metric | Original PHP Code | Stored Procedure |
|--------|-------------------|------------------|
| Approach | Row-by-row (RBAR) | Set-based MERGE |
| Bonus calculation | 3 subqueries per row | 1 CTE total |
| Date filtering | FORMAT() (non-SARGable) | Direct comparison (SARGable) |
| Transaction scope | Single large transaction | Configurable batches |

**Expected Performance Improvement:**
- 10,000 rows: ~10-50x faster
- 100,000 rows: ~50-100x faster

---

## Usage Examples

### Basic Sync (All Data)

```sql
DECLARE @rows INT, @error NVARCHAR(MAX), @result INT;
EXEC @result = sp_SyncPajakKeluaranDetailFromLive
    @pt = 'all',
    @brand = 'all',
    @depo = 'all',
    @start = '2024-01-01',
    @end = '2024-01-31',
    @tipe = '',
    @user_depo = 'all',
    @rows_affected = @rows OUTPUT,
    @error_message = @error OUTPUT;

SELECT @result AS ReturnCode, @rows AS RowsAffected, @error AS ErrorMessage;
```

### Filtered Sync

```sql
DECLARE @rows INT, @error NVARCHAR(MAX), @result INT;
EXEC @result = sp_SyncPajakKeluaranDetailFromLive
    @pt = 'COMPANY_A,COMPANY_B',
    @brand = 'BRAND_X',
    @depo = 'DEPO_001',
    @start = '2024-01-01',
    @end = '2024-01-31',
    @tipe = '',
    @user_depo = 'all',
    @rows_affected = @rows OUTPUT,
    @error_message = @error OUTPUT;

SELECT @result AS ReturnCode, @rows AS RowsAffected, @error AS ErrorMessage;
```

### Batch Processing with Debug

```sql
DECLARE @rows INT, @batches INT, @error NVARCHAR(MAX), @result INT;
EXEC @result = sp_SyncPajakKeluaranDetailFromLive_Batch
    @pt = 'all',
    @brand = 'all',
    @depo = 'all',
    @start = '2024-01-01',
    @end = '2024-12-31',
    @tipe = '',
    @user_depo = 'all',
    @batch_size = 10000,
    @debug_mode = 1,
    @total_rows_affected = @rows OUTPUT,
    @batches_processed = @batches OUTPUT,
    @error_message = @error OUTPUT;

SELECT @result AS ReturnCode, @rows AS TotalRowsAffected,
       @batches AS BatchesProcessed, @error AS ErrorMessage;
```

---

## Integration with Laravel

To call these stored procedures from Laravel:

```php
use Illuminate\Support\Facades\DB;

// Standard version
$result = DB::connection('sqlsrv')->statement("
    DECLARE @rows INT, @error NVARCHAR(MAX);
    EXEC sp_SyncPajakKeluaranDetailFromLive
        @pt = ?,
        @brand = ?,
        @depo = ?,
        @start = ?,
        @end = ?,
        @tipe = ?,
        @user_depo = ?,
        @rows_affected = @rows OUTPUT,
        @error_message = @error OUTPUT;
    SELECT @rows AS rows_affected, @error AS error_message;
", [$pt, $brand, $depo, $start, $end, $tipe, $userDepo]);

// Or using raw query with output parameters
$pdo = DB::connection('sqlsrv')->getPdo();
$stmt = $pdo->prepare("
    DECLARE @rows INT, @error NVARCHAR(MAX), @result INT;
    EXEC @result = sp_SyncPajakKeluaranDetailFromLive
        @pt = :pt,
        @brand = :brand,
        @depo = :depo,
        @start = :start,
        @end = :end,
        @tipe = :tipe,
        @user_depo = :user_depo,
        @rows_affected = @rows OUTPUT,
        @error_message = @error OUTPUT;
    SELECT @result AS return_code, @rows AS rows_affected, @error AS error_message;
");
$stmt->execute([
    ':pt' => $pt,
    ':brand' => $brand,
    ':depo' => $depo,
    ':start' => $start,
    ':end' => $end,
    ':tipe' => $tipe,
    ':user_depo' => $userDepo,
]);
$result = $stmt->fetch(\PDO::FETCH_ASSOC);
```

---

## Monitoring

```sql
-- Check for long-running executions
SELECT * FROM sys.dm_exec_requests
WHERE command = 'MERGE' AND wait_time > 5000;

-- Check tempdb usage
SELECT * FROM sys.dm_db_file_space_usage;

-- Check index usage
SELECT * FROM sys.dm_db_index_usage_stats
WHERE object_id = OBJECT_ID('pajak_keluaran_details');
```

---

## Troubleshooting

### Error: "String or binary data would be truncated"
Check that column sizes in staging table match target table. Adjust NVARCHAR lengths as needed.

### Error: "Deadlock victim"
Use the batch version with smaller batch sizes and consider adding delays between batches.

### Slow Performance
1. Ensure indexes are created on `pajak_keluaran_details`
2. Check execution plan for table scans
3. Verify statistics are up to date: `UPDATE STATISTICS pajak_keluaran_details`

---

## Compatibility

- **SQL Server Version:** 2012 and above
- **Dependencies:** 
  - `padma_live` linked server/database for source data
  - `pajak_keluaran_details` table in target database