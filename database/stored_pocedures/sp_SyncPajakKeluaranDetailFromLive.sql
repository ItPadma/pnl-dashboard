/*
================================================================================
Stored Procedure: sp_SyncPajakKeluaranDetailFromLive
Description: Synchronizes pajak keluaran detail data from live database (bosnet_live)
             to pajak_keluaran_details table with UPSERT logic.

Version: 1.1
Created: 2024
Compatible: SQL Server 2012+ (No STRING_SPLIT dependency)

Optimization Strategies:
1. MERGE statement for single-pass UPSERT (instead of row-by-row EXISTS check)
2. CTE to pre-calculate bonus amounts (avoiding repeated correlated subqueries)
3. Temp table staging for better query plan optimization
4. SET-based operations instead of RBAR (Row-By-Agonizing-Row)
5. NOLOCK hints on source tables to prevent blocking
6. Proper TRY-CATCH with explicit transaction control for rollback support
7. XACT_ABORT ON for automatic rollback on errors
8. Custom XML-based string split for SQL Server 2012 compatibility

Parameters:
    @pt         - Company filter (comma-separated for multiple, 'all' for no filter)
    @brand      - Brand filter (comma-separated for multiple, 'all' for no filter)
    @depo       - Depo/Workplace filter ('all' for no filter, pipe-separated for multiple)
    @start      - Start date (YYYY-MM-DD format)
    @end        - End date (YYYY-MM-DD format)
    @tipe       - Additional type filter (optional, pass empty string if not needed)
    @user_depo  - Current user's depo access (pipe-separated, can include 'all')

Returns:
    0 = Success
    1 = No data found
    -1 = Error occurred
================================================================================
*/

-- First, create a helper function for splitting strings (SQL Server 2012 compatible)
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[fn_SplitString]') AND type in (N'TF', N'IF'))
    DROP FUNCTION [dbo].[fn_SplitString]
GO

CREATE FUNCTION [dbo].[fn_SplitString]
(
    @InputString NVARCHAR(MAX),
    @Delimiter NVARCHAR(10)
)
RETURNS @Result TABLE (Value NVARCHAR(500))
AS
BEGIN
    IF @InputString IS NULL OR LEN(@InputString) = 0
        RETURN;

    DECLARE @XML XML;

    -- Handle special XML characters and convert to XML for parsing
    SET @InputString = REPLACE(@InputString, '&', '&amp;');
    SET @InputString = REPLACE(@InputString, '<', '&lt;');
    SET @InputString = REPLACE(@InputString, '>', '&gt;');
    SET @InputString = REPLACE(@InputString, '"', '&quot;');
    SET @InputString = REPLACE(@InputString, '''', '&apos;');

    SET @XML = CAST('<item>' + REPLACE(@InputString, @Delimiter, '</item><item>') + '</item>' AS XML);

    INSERT INTO @Result (Value)
    SELECT LTRIM(RTRIM(T.c.value('.', 'NVARCHAR(500)')))
    FROM @XML.nodes('/item') AS T(c)
    WHERE LEN(LTRIM(RTRIM(T.c.value('.', 'NVARCHAR(500)')))) > 0;

    RETURN;
END
GO

-- Now create the main stored procedure
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[sp_SyncPajakKeluaranDetailFromLive]') AND type in (N'P', N'PC'))
    DROP PROCEDURE [dbo].[sp_SyncPajakKeluaranDetailFromLive]
GO

CREATE PROCEDURE [dbo].[sp_SyncPajakKeluaranDetailFromLive]
    @pt NVARCHAR(MAX) = 'all',
    @brand NVARCHAR(MAX) = 'all',
    @depo NVARCHAR(100) = 'all',
    @start DATE,
    @end DATE,
    @tipe NVARCHAR(MAX) = '',
    @user_depo NVARCHAR(MAX) = 'all',
    @rows_affected INT OUTPUT,
    @error_message NVARCHAR(MAX) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    -- Initialize output parameters
    SET @rows_affected = 0;
    SET @error_message = NULL;

    -- ==========================================================================
    -- STEP 1: Parse filter parameters into table variables for efficient JOINs
    -- Using XML-based string split (SQL Server 2012 compatible)
    -- ==========================================================================

    -- Table variable for PT (company) filter
    DECLARE @FilterPT TABLE (szCategory_9 NVARCHAR(100));

    -- Table variable for Brand filter
    DECLARE @FilterBrand TABLE (szCategory_1 NVARCHAR(100));

    -- Table variable for Depo filter
    DECLARE @FilterDepo TABLE (szWorkplaceId NVARCHAR(50));

    -- Flag variables for filter existence
    DECLARE @HasPTFilter BIT = 0;
    DECLARE @HasBrandFilter BIT = 0;
    DECLARE @HasDepoFilter BIT = 0;

    -- Parse PT filter
    IF @pt <> 'all' AND @pt IS NOT NULL AND LEN(@pt) > 0
    BEGIN
        INSERT INTO @FilterPT (szCategory_9)
        SELECT Value FROM dbo.fn_SplitString(@pt, ',');

        IF EXISTS (SELECT 1 FROM @FilterPT)
            SET @HasPTFilter = 1;
    END

    -- Parse Brand filter
    IF @brand <> 'all' AND @brand IS NOT NULL AND LEN(@brand) > 0
    BEGIN
        INSERT INTO @FilterBrand (szCategory_1)
        SELECT Value FROM dbo.fn_SplitString(@brand, ',');

        IF EXISTS (SELECT 1 FROM @FilterBrand)
            SET @HasBrandFilter = 1;
    END

    -- Parse Depo filter (handle both single value and pipe-separated user_depo)
    IF @depo <> 'all'
    BEGIN
        INSERT INTO @FilterDepo (szWorkplaceId)
        VALUES (@depo);
        SET @HasDepoFilter = 1;
    END
    ELSE IF @user_depo <> 'all' AND CHARINDEX('all', @user_depo) = 0
    BEGIN
        -- Replace pipe with comma for parsing
        INSERT INTO @FilterDepo (szWorkplaceId)
        SELECT Value FROM dbo.fn_SplitString(REPLACE(@user_depo, '|', ','), ',');

        IF EXISTS (SELECT 1 FROM @FilterDepo)
            SET @HasDepoFilter = 1;
    END

    -- ==========================================================================
    -- STEP 2: Create temp table for staging data from live database
    -- ==========================================================================

    CREATE TABLE #StagingData (
        no_invoice NVARCHAR(50),
        no_do NVARCHAR(50),
        kode_produk NVARCHAR(50),
        qty_pcs INT,
        hargasatuan_sblm_ppn DECIMAL(18,4),
        disc DECIMAL(18,4),
        hargatotal_sblm_ppn DECIMAL(18,4),
        dpp DECIMAL(18,4),
        ppn DECIMAL(18,4),
        tgl_faktur_pajak DATE,
        depo NVARCHAR(100),
        area NVARCHAR(100),
        nama_produk NVARCHAR(255),
        npwp_customer NVARCHAR(50),
        customer_id NVARCHAR(50),
        nama_customer_sistem NVARCHAR(255),
        alamat_sistem NVARCHAR(500),
        type_pajak INT,
        satuan NVARCHAR(20),
        nama_sesuai_npwp NVARCHAR(255),
        alamat_npwp_lengkap NVARCHAR(500),
        no_telepon NVARCHAR(50),
        no_fp INT,
        brand NVARCHAR(50),
        type_jual NVARCHAR(20),
        kode_jenis_fp INT,
        fp_normal_pengganti INT,
        nik NVARCHAR(50),
        dpp_lain DECIMAL(18,4),
        id_tku_pembeli NVARCHAR(100),
        barang_jasa NVARCHAR(50),
        is_checked INT,
        is_downloaded INT,
        tipe_ppn NVARCHAR(50),
        company NVARCHAR(50),
        status NVARCHAR(50)
    );

    -- Create index on staging table for optimal MERGE performance
    CREATE CLUSTERED INDEX IX_Staging_Key ON #StagingData (no_invoice, no_do, kode_produk);

    BEGIN TRY
        -- ======================================================================
        -- STEP 3: Fetch data from live database with optimized query
        -- Using CTE to pre-calculate bonus amounts (avoiding repeated subqueries)
        -- ======================================================================

        ;WITH BonusAmounts AS (
            -- Pre-calculate all bonus amounts in one pass
            -- This avoids repeated correlated subqueries in the main SELECT
            SELECT
                c.szfDoId,
                c.szProductId,
                c.szOrderItemTypeId,
                SUM(ISNULL(c.decBonusAmount, 0)) AS TotalBonus
            FROM [padma_live].[dbo].[BOS_SD_FDoItemBonusSource] c WITH (NOLOCK)
            GROUP BY c.szfDoId, c.szProductId, c.szOrderItemTypeId
        ),
        FilteredDeliveries AS (
            -- First filter the main table to reduce data volume early
            -- This pushes predicates down for better performance
            SELECT
                a.[szFInvoiceId],
                a.[szDoId],
                a.[dtmDelivery],
                a.[szCustId],
                a.[szSalesId],
                a.[szWorkplaceId],
                a.[DeliveryszAddress_1],
                a.[bcash]
            FROM [padma_live].[dbo].[BOS_SD_FDo] a WITH (NOLOCK)
            WHERE a.dtmDelivery >= @start
                AND a.dtmDelivery < DATEADD(DAY, 1, @end)
                AND a.bApplied = '1'
                AND a.bVoid = '0'
                AND a.[szWorkplaceId] NOT IN ('777')
                -- Apply depo filter if exists
                AND (
                    @HasDepoFilter = 0
                    OR a.[szWorkplaceId] IN (SELECT szWorkplaceId FROM @FilterDepo)
                )
        )
        INSERT INTO #StagingData
        SELECT
            fd.[szFInvoiceId] AS 'no_invoice',
            fd.[szDoId] AS 'no_do',
            b.szProductId AS 'kode_produk',
            CAST(b.decUomQty AS INT) AS 'qty_pcs',
            b.decPrice / 1.11 AS 'hargasatuan_sblm_ppn',
            ISNULL(ba.TotalBonus, 0) / 1.11 AS 'disc',
            b.decDpp AS 'hargatotal_sblm_ppn',
            b.decDpp - (ISNULL(ba.TotalBonus, 0) / 1.11) AS 'dpp',
            (b.decDpp - (ISNULL(ba.TotalBonus, 0) / 1.11)) * 11.0 / 100.0 AS 'ppn',
            fd.[dtmDelivery] AS 'tgl_faktur_pajak',
            f.[szName] AS 'depo',
            '' AS 'area',
            e.[szName] AS 'nama_produk',
            tax.[szNPWP] AS 'npwp_customer',
            fd.[szCustId] AS 'customer_id',
            LTRIM(RTRIM(
                REPLACE(REPLACE(REPLACE(REPLACE(d.[szName], CHAR(13), ''), CHAR(10), ''), CHAR(9), ''), CHAR(11), '')
            )) AS 'nama_customer_sistem',
            LTRIM(RTRIM(
                REPLACE(REPLACE(REPLACE(REPLACE(fd.[DeliveryszAddress_1], CHAR(13), ''), CHAR(10), ''), CHAR(9), ''), CHAR(11), '')
            )) AS 'alamat_sistem',
            0 AS 'type_pajak',
            'PCS' AS 'satuan',
            taxinv.[szCustTaxNm] AS 'nama_sesuai_npwp',
            ISNULL(taxinv.[TaxszAddress_1], '') +
                CASE WHEN taxinv.[TaxszDistrict] IS NOT NULL THEN ', ' + taxinv.[TaxszDistrict] ELSE '' END +
                CASE WHEN taxinv.[TaxszAddress_2] IS NOT NULL THEN ', ' + taxinv.[TaxszAddress_2] ELSE '' END +
                CASE WHEN taxinv.[TaxszCity] IS NOT NULL THEN ', ' + taxinv.[TaxszCity] ELSE '' END +
                CASE WHEN taxinv.[TaxszZipCode] IS NOT NULL THEN ', ' + taxinv.[TaxszZipCode] ELSE '' END
            AS 'alamat_npwp_lengkap',
            taxinv.[TaxszPhoneNo_1] AS 'no_telepon',
            0 AS 'no_fp',
            d.[szCategory_1] AS 'brand',
            CASE WHEN fd.bcash = 0 THEN 'CREDIT' ELSE 'CASH' END AS 'type_jual',
            0 AS 'kode_jenis_fp',
            0 AS 'fp_normal_pengganti',
            tax.[szNoKTP] AS 'nik',
            (b.decDpp - (ISNULL(ba.TotalBonus, 0) / 1.11)) * 11.0 / 12.0 AS 'dpp_lain',
            ISNULL(tax.[szNoKTP], '') + '000000' AS 'id_tku_pembeli',
            'barang_jasa' AS 'barang_jasa',
            0 AS 'is_checked',
            0 AS 'is_downloaded',
            e.szTaxTypeId AS 'tipe_ppn',
            e.szCategory_9 AS 'company',
            '' AS 'status'
        FROM FilteredDeliveries fd
        INNER JOIN [padma_live].[dbo].[BOS_SD_FDoItem] b WITH (NOLOCK)
            ON fd.[szDoId] = b.[szDoId] AND b.szProductId <> ''
        LEFT JOIN BonusAmounts ba
            ON ba.szfDoId = fd.szDoId
            AND ba.szProductId = b.szProductId
            AND ba.szOrderItemTypeId = b.szOrderItemTypeId
        LEFT JOIN [padma_live].[dbo].[BOS_AR_Customer] d WITH (NOLOCK)
            ON fd.[szCustId] = d.[szCustId]
        LEFT JOIN [padma_live].[dbo].[BOS_INV_Product] e WITH (NOLOCK)
            ON b.szProductId = e.szProductId
        LEFT JOIN [padma_live].[dbo].[BOS_GL_Workplace] f WITH (NOLOCK)
            ON fd.szWorkplaceId = f.szWorkplaceId
        LEFT JOIN [padma_live].[dbo].[BOS_TIN_CustTaxIndConfig] tax WITH (NOLOCK)
            ON fd.[szCustId] = tax.szCustId
        LEFT JOIN [padma_live].[dbo].[BOS_AR_FCustChgInvAddress] taxinv WITH (NOLOCK)
            ON fd.[szCustId] = taxinv.szCustId
        WHERE
            -- Apply PT filter if exists
            (@HasPTFilter = 0 OR e.szCategory_9 IN (SELECT szCategory_9 FROM @FilterPT))
            -- Apply Brand filter if exists
            AND (@HasBrandFilter = 0 OR e.szCategory_1 IN (SELECT szCategory_1 FROM @FilterBrand));

        -- Check if any data was found
        IF NOT EXISTS (SELECT 1 FROM #StagingData)
        BEGIN
            SET @error_message = 'No data found from live database for the specified criteria.';
            DROP TABLE #StagingData;
            RETURN 1; -- No data found
        END

        -- ======================================================================
        -- STEP 4: Execute MERGE operation with explicit transaction
        -- Single atomic operation for UPSERT with rollback support
        -- ======================================================================

        BEGIN TRANSACTION;

        MERGE INTO [dbo].[pajak_keluaran_details] WITH (HOLDLOCK) AS target
        USING #StagingData AS source
        ON (
            target.no_invoice = source.no_invoice
            AND target.no_do = source.no_do
            AND target.kode_produk = source.kode_produk
        )

        -- UPDATE existing records (preserve is_checked and is_downloaded)
        WHEN MATCHED THEN
            UPDATE SET
                target.qty_pcs = source.qty_pcs,
                target.hargasatuan_sblm_ppn = source.hargasatuan_sblm_ppn,
                target.disc = source.disc,
                target.hargatotal_sblm_ppn = source.hargatotal_sblm_ppn,
                target.dpp = source.dpp,
                target.ppn = source.ppn,
                target.tgl_faktur_pajak = source.tgl_faktur_pajak,
                target.depo = source.depo,
                target.area = source.area,
                target.nama_produk = source.nama_produk,
                target.npwp_customer = source.npwp_customer,
                target.customer_id = source.customer_id,
                target.nama_customer_sistem = source.nama_customer_sistem,
                target.alamat_sistem = source.alamat_sistem,
                target.type_pajak = source.type_pajak,
                target.satuan = source.satuan,
                target.nama_sesuai_npwp = source.nama_sesuai_npwp,
                target.alamat_npwp_lengkap = source.alamat_npwp_lengkap,
                target.no_telepon = source.no_telepon,
                target.no_fp = source.no_fp,
                target.brand = source.brand,
                target.type_jual = source.type_jual,
                target.kode_jenis_fp = source.kode_jenis_fp,
                target.fp_normal_pengganti = source.fp_normal_pengganti,
                target.nik = source.nik,
                target.dpp_lain = source.dpp_lain,
                target.id_tku_pembeli = source.id_tku_pembeli,
                target.barang_jasa = source.barang_jasa,
                target.tipe_ppn = source.tipe_ppn,
                target.company = source.company,
                target.status = source.status,
                target.updated_at = GETDATE()
                -- Note: is_checked and is_downloaded are NOT updated (preserved)

        -- INSERT new records
        WHEN NOT MATCHED BY TARGET THEN
            INSERT (
                no_invoice, no_do, kode_produk, qty_pcs, hargasatuan_sblm_ppn,
                disc, hargatotal_sblm_ppn, dpp, ppn, tgl_faktur_pajak,
                depo, area, nama_produk, npwp_customer, customer_id,
                nama_customer_sistem, alamat_sistem, type_pajak, satuan,
                nama_sesuai_npwp, alamat_npwp_lengkap, no_telepon, no_fp,
                brand, type_jual, kode_jenis_fp, fp_normal_pengganti, nik,
                dpp_lain, id_tku_pembeli, barang_jasa, is_checked, is_downloaded,
                tipe_ppn, company, status, created_at, updated_at
            )
            VALUES (
                source.no_invoice, source.no_do, source.kode_produk, source.qty_pcs,
                source.hargasatuan_sblm_ppn, source.disc, source.hargatotal_sblm_ppn,
                source.dpp, source.ppn, source.tgl_faktur_pajak, source.depo,
                source.area, source.nama_produk, source.npwp_customer, source.customer_id,
                source.nama_customer_sistem, source.alamat_sistem, source.type_pajak,
                source.satuan, source.nama_sesuai_npwp, source.alamat_npwp_lengkap,
                source.no_telepon, source.no_fp, source.brand, source.type_jual,
                source.kode_jenis_fp, source.fp_normal_pengganti, source.nik,
                source.dpp_lain, source.id_tku_pembeli, source.barang_jasa,
                source.is_checked, source.is_downloaded, source.tipe_ppn,
                source.company, source.status, GETDATE(), GETDATE()
            );

        SET @rows_affected = @@ROWCOUNT;

        COMMIT TRANSACTION;

        -- Cleanup
        DROP TABLE #StagingData;

        RETURN 0; -- Success

    END TRY
    BEGIN CATCH
        -- Rollback transaction if active
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        -- Capture error details
        SET @error_message = 'Error ' + CAST(ERROR_NUMBER() AS NVARCHAR(10)) + ': ' + ERROR_MESSAGE() +
                            ' (Line: ' + CAST(ERROR_LINE() AS NVARCHAR(10)) + ')';

        -- Cleanup temp table if exists
        IF OBJECT_ID('tempdb..#StagingData') IS NOT NULL
            DROP TABLE #StagingData;

        RETURN -1; -- Error

    END CATCH
END
GO

/*
================================================================================
USAGE EXAMPLES:
================================================================================

-- Example 1: Sync all data for a date range
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

-- Example 2: Sync specific company and brand
DECLARE @rows INT, @error NVARCHAR(MAX), @result INT;
EXEC @result = sp_SyncPajakKeluaranDetailFromLive
    @pt = 'COMPANY_A,COMPANY_B',
    @brand = 'BRAND_X',
    @depo = 'all',
    @start = '2024-01-01',
    @end = '2024-01-31',
    @tipe = '',
    @user_depo = 'all',
    @rows_affected = @rows OUTPUT,
    @error_message = @error OUTPUT;

SELECT @result AS ReturnCode, @rows AS RowsAffected, @error AS ErrorMessage;

-- Example 3: Sync specific depo
DECLARE @rows INT, @error NVARCHAR(MAX), @result INT;
EXEC @result = sp_SyncPajakKeluaranDetailFromLive
    @pt = 'all',
    @brand = 'all',
    @depo = 'DEPO_001',
    @start = '2024-01-01',
    @end = '2024-01-31',
    @tipe = '',
    @user_depo = 'all',
    @rows_affected = @rows OUTPUT,
    @error_message = @error OUTPUT;

SELECT @result AS ReturnCode, @rows AS RowsAffected, @error AS ErrorMessage;

-- Example 4: Sync with user depo restriction (pipe-separated)
DECLARE @rows INT, @error NVARCHAR(MAX), @result INT;
EXEC @result = sp_SyncPajakKeluaranDetailFromLive
    @pt = 'all',
    @brand = 'all',
    @depo = 'all',
    @start = '2024-01-01',
    @end = '2024-01-31',
    @tipe = '',
    @user_depo = 'DEPO_001|DEPO_002|DEPO_003',
    @rows_affected = @rows OUTPUT,
    @error_message = @error OUTPUT;

SELECT @result AS ReturnCode, @rows AS RowsAffected, @error AS ErrorMessage;

================================================================================
INDEX RECOMMENDATIONS FOR OPTIMAL PERFORMANCE:
================================================================================

-- 1. Primary composite index for MERGE operation (CRITICAL)
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_PajakKeluaranDetails_InvoiceDoProduct')
BEGIN
    CREATE UNIQUE NONCLUSTERED INDEX IX_PajakKeluaranDetails_InvoiceDoProduct
    ON pajak_keluaran_details (no_invoice, no_do, kode_produk)
    WITH (FILLFACTOR = 90);
END
GO

-- 2. Index for date range queries
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_PajakKeluaranDetails_TglFaktur')
BEGIN
    CREATE NONCLUSTERED INDEX IX_PajakKeluaranDetails_TglFaktur
    ON pajak_keluaran_details (tgl_faktur_pajak)
    INCLUDE (company, brand, depo);
END
GO

-- 3. Index for status-based queries
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_PajakKeluaranDetails_Status')
BEGIN
    CREATE NONCLUSTERED INDEX IX_PajakKeluaranDetails_Status
    ON pajak_keluaran_details (is_checked, is_downloaded)
    INCLUDE (no_invoice, no_do);
END
GO

================================================================================
PERFORMANCE OPTIMIZATION NOTES:
================================================================================

1. KEY DIFFERENCES FROM ORIGINAL PHP CODE:
   - Original: Row-by-row EXISTS check + UPDATE/INSERT (N+1 problem)
   - Optimized: Single MERGE statement (set-based, single pass)

   - Original: Repeated correlated subquery for bonus calculation (3x per row)
   - Optimized: CTE pre-calculates all bonuses in one pass (1x total)

   - Original: FORMAT() function for date filtering (function on column = no index use)
   - Optimized: Direct date comparison (SARGable, can use indexes)

2. EXPECTED PERFORMANCE IMPROVEMENT:
   - For 10,000 rows: ~10-50x faster
   - For 100,000 rows: ~50-100x faster
   - Reduced blocking on source database (NOLOCK hints)
   - Reduced transaction log growth (single MERGE vs multiple statements)

3. ROLLBACK BEHAVIOR:
   - XACT_ABORT ON ensures automatic rollback on any error
   - Explicit TRY-CATCH for granular error handling
   - HOLDLOCK on MERGE prevents race conditions in concurrent scenarios

4. FOR VERY LARGE DATASETS (>500K rows), consider:
   - Adding @batch_size parameter with chunked processing
   - Using TABLOCK hint during maintenance windows
   - Running during off-peak hours

5. MONITORING QUERIES:

   -- Check for long-running executions
   SELECT * FROM sys.dm_exec_requests
   WHERE command = 'MERGE' AND wait_time > 5000;

   -- Check tempdb usage
   SELECT * FROM sys.dm_db_file_space_usage;

   -- Check index usage after running
   SELECT * FROM sys.dm_db_index_usage_stats
   WHERE object_id = OBJECT_ID('pajak_keluaran_details');

================================================================================
*/
