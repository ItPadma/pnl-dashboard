/*
================================================================================
Stored Procedure: sp_SyncPajakKeluaranDetailFromLive_Batch
Description: Batch processing version for synchronizing large pajak keluaran
             detail data from live database with configurable batch size.

             This version is optimized for very large datasets (>100K rows)
             where single MERGE might cause tempdb pressure or long locks.

Version: 1.0
Created: 2024
Compatible: SQL Server 2012+ (No STRING_SPLIT dependency)

Key Features:
1. Configurable batch size for chunked processing
2. Progress tracking with row counts per batch
3. Partial commit support (each batch commits independently)
4. Resume capability on failure (already processed rows stay committed)
5. All optimizations from the standard version

Parameters:
    @pt          - Company filter (comma-separated for multiple, 'all' for no filter)
    @brand       - Brand filter (comma-separated for multiple, 'all' for no filter)
    @depo        - Depo/Workplace filter ('all' for no filter)
    @start       - Start date (YYYY-MM-DD format)
    @end         - End date (YYYY-MM-DD format)
    @tipe        - Additional type filter (optional)
    @user_depo   - Current user's depo access (pipe-separated)
    @batch_size  - Number of rows per batch (default: 5000)
    @debug_mode  - Print progress messages (default: 0)

Returns:
    0 = Success (all batches completed)
    1 = No data found
    -1 = Error occurred (partial data may have been committed)
================================================================================
*/

-- Ensure helper function exists (same as standard version)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[fn_SplitString]') AND type in (N'TF', N'IF'))
BEGIN
    EXEC('
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

        SET @InputString = REPLACE(@InputString, ''&'', ''&amp;'');
        SET @InputString = REPLACE(@InputString, ''<'', ''&lt;'');
        SET @InputString = REPLACE(@InputString, ''>'', ''&gt;'');
        SET @InputString = REPLACE(@InputString, ''"'', ''&quot;'');
        SET @InputString = REPLACE(@InputString, '''''''', ''&apos;'');

        SET @XML = CAST(''<item>'' + REPLACE(@InputString, @Delimiter, ''</item><item>'') + ''</item>'' AS XML);

        INSERT INTO @Result (Value)
        SELECT LTRIM(RTRIM(T.c.value(''.'', ''NVARCHAR(500)'')))
        FROM @XML.nodes(''/item'') AS T(c)
        WHERE LEN(LTRIM(RTRIM(T.c.value(''.'', ''NVARCHAR(500)'')))) > 0;

        RETURN;
    END
    ')
END
GO

IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[sp_SyncPajakKeluaranDetailFromLive_Batch]') AND type in (N'P', N'PC'))
    DROP PROCEDURE [dbo].[sp_SyncPajakKeluaranDetailFromLive_Batch]
GO

CREATE PROCEDURE [dbo].[sp_SyncPajakKeluaranDetailFromLive_Batch]
    @pt NVARCHAR(MAX) = 'all',
    @brand NVARCHAR(MAX) = 'all',
    @depo NVARCHAR(100) = 'all',
    @start DATE,
    @end DATE,
    @tipe NVARCHAR(MAX) = '',
    @user_depo NVARCHAR(MAX) = 'all',
    @batch_size INT = 5000,
    @debug_mode BIT = 0,
    @total_rows_affected INT OUTPUT,
    @batches_processed INT OUTPUT,
    @error_message NVARCHAR(MAX) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    -- Initialize output parameters
    SET @total_rows_affected = 0;
    SET @batches_processed = 0;
    SET @error_message = NULL;

    -- Validate batch size
    IF @batch_size < 100
        SET @batch_size = 100;
    IF @batch_size > 50000
        SET @batch_size = 50000;

    -- ==========================================================================
    -- STEP 1: Parse filter parameters into table variables
    -- ==========================================================================

    DECLARE @FilterPT TABLE (szCategory_9 NVARCHAR(100));
    DECLARE @FilterBrand TABLE (szCategory_1 NVARCHAR(100));
    DECLARE @FilterDepo TABLE (szWorkplaceId NVARCHAR(50));

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

    -- Parse Depo filter
    IF @depo <> 'all'
    BEGIN
        INSERT INTO @FilterDepo (szWorkplaceId)
        VALUES (@depo);
        SET @HasDepoFilter = 1;
    END
    ELSE IF @user_depo <> 'all' AND CHARINDEX('all', @user_depo) = 0
    BEGIN
        INSERT INTO @FilterDepo (szWorkplaceId)
        SELECT Value FROM dbo.fn_SplitString(REPLACE(@user_depo, '|', ','), ',');

        IF EXISTS (SELECT 1 FROM @FilterDepo)
            SET @HasDepoFilter = 1;
    END

    -- ==========================================================================
    -- STEP 2: Create staging table with row numbers for batch processing
    -- ==========================================================================

    CREATE TABLE #StagingData (
        row_num INT IDENTITY(1,1) PRIMARY KEY,
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

    -- Create index for batch key lookup
    CREATE NONCLUSTERED INDEX IX_Staging_CompositeKey
    ON #StagingData (no_invoice, no_do, kode_produk);

    BEGIN TRY
        -- ======================================================================
        -- STEP 3: Load all data from live database into staging
        -- ======================================================================

        IF @debug_mode = 1
            PRINT 'Loading data from live database...';

        ;WITH BonusAmounts AS (
            SELECT
                c.szfDoId,
                c.szProductId,
                c.szOrderItemTypeId,
                SUM(ISNULL(c.decBonusAmount, 0)) AS TotalBonus
            FROM [padma_live].[dbo].[BOS_SD_FDoItemBonusSource] c WITH (NOLOCK)
            GROUP BY c.szfDoId, c.szProductId, c.szOrderItemTypeId
        ),
        FilteredDeliveries AS (
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
                AND (
                    @HasDepoFilter = 0
                    OR a.[szWorkplaceId] IN (SELECT szWorkplaceId FROM @FilterDepo)
                )
        )
        INSERT INTO #StagingData (
            no_invoice, no_do, kode_produk, qty_pcs, hargasatuan_sblm_ppn,
            disc, hargatotal_sblm_ppn, dpp, ppn, tgl_faktur_pajak,
            depo, area, nama_produk, npwp_customer, customer_id,
            nama_customer_sistem, alamat_sistem, type_pajak, satuan,
            nama_sesuai_npwp, alamat_npwp_lengkap, no_telepon, no_fp,
            brand, type_jual, kode_jenis_fp, fp_normal_pengganti, nik,
            dpp_lain, id_tku_pembeli, barang_jasa, is_checked, is_downloaded,
            tipe_ppn, company, status
        )
        SELECT
            fd.[szFInvoiceId],
            fd.[szDoId],
            b.szProductId,
            CAST(b.decUomQty AS INT),
            b.decPrice / 1.11,
            ISNULL(ba.TotalBonus, 0) / 1.11,
            b.decDpp,
            b.decDpp - (ISNULL(ba.TotalBonus, 0) / 1.11),
            (b.decDpp - (ISNULL(ba.TotalBonus, 0) / 1.11)) * 11.0 / 100.0,
            fd.[dtmDelivery],
            f.[szName],
            '',
            e.[szName],
            tax.[szNPWP],
            fd.[szCustId],
            LTRIM(RTRIM(
                REPLACE(REPLACE(REPLACE(REPLACE(d.[szName], CHAR(13), ''), CHAR(10), ''), CHAR(9), ''), CHAR(11), '')
            )),
            LTRIM(RTRIM(
                REPLACE(REPLACE(REPLACE(REPLACE(fd.[DeliveryszAddress_1], CHAR(13), ''), CHAR(10), ''), CHAR(9), ''), CHAR(11), '')
            )),
            0,
            'PCS',
            taxinv.[szCustTaxNm],
            ISNULL(taxinv.[TaxszAddress_1], '') +
                CASE WHEN taxinv.[TaxszDistrict] IS NOT NULL THEN ', ' + taxinv.[TaxszDistrict] ELSE '' END +
                CASE WHEN taxinv.[TaxszAddress_2] IS NOT NULL THEN ', ' + taxinv.[TaxszAddress_2] ELSE '' END +
                CASE WHEN taxinv.[TaxszCity] IS NOT NULL THEN ', ' + taxinv.[TaxszCity] ELSE '' END +
                CASE WHEN taxinv.[TaxszZipCode] IS NOT NULL THEN ', ' + taxinv.[TaxszZipCode] ELSE '' END,
            taxinv.[TaxszPhoneNo_1],
            0,
            d.[szCategory_1],
            CASE WHEN fd.bcash = 0 THEN 'CREDIT' ELSE 'CASH' END,
            0,
            0,
            tax.[szNoKTP],
            (b.decDpp - (ISNULL(ba.TotalBonus, 0) / 1.11)) * 11.0 / 12.0,
            ISNULL(tax.[szNoKTP], '') + '000000',
            'barang_jasa',
            0,
            0,
            e.szTaxTypeId,
            e.szCategory_9,
            ''
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
            (@HasPTFilter = 0 OR e.szCategory_9 IN (SELECT szCategory_9 FROM @FilterPT))
            AND (@HasBrandFilter = 0 OR e.szCategory_1 IN (SELECT szCategory_1 FROM @FilterBrand))
        ORDER BY fd.[dtmDelivery], fd.[szDoId], b.szProductId;

        -- Get total row count
        DECLARE @total_rows INT = (SELECT COUNT(*) FROM #StagingData);

        IF @debug_mode = 1
            PRINT 'Loaded ' + CAST(@total_rows AS VARCHAR) + ' rows into staging table.';

        -- Check if any data was found
        IF @total_rows = 0
        BEGIN
            SET @error_message = 'No data found from live database for the specified criteria.';
            DROP TABLE #StagingData;
            RETURN 1;
        END

        -- ======================================================================
        -- STEP 4: Process data in batches
        -- ======================================================================

        DECLARE @current_batch INT = 0;
        DECLARE @batch_start INT = 1;
        DECLARE @batch_end INT;
        DECLARE @batch_rows_affected INT;
        DECLARE @total_batches INT = CEILING(CAST(@total_rows AS FLOAT) / @batch_size);

        IF @debug_mode = 1
            PRINT 'Starting batch processing: ' + CAST(@total_batches AS VARCHAR) + ' batches of ' + CAST(@batch_size AS VARCHAR) + ' rows';

        WHILE @batch_start <= @total_rows
        BEGIN
            SET @current_batch = @current_batch + 1;
            SET @batch_end = @batch_start + @batch_size - 1;

            IF @debug_mode = 1
                PRINT 'Processing batch ' + CAST(@current_batch AS VARCHAR) + ' of ' + CAST(@total_batches AS VARCHAR) +
                      ' (rows ' + CAST(@batch_start AS VARCHAR) + ' to ' + CAST(@batch_end AS VARCHAR) + ')...';

            BEGIN TRY
                BEGIN TRANSACTION;

                -- MERGE for current batch
                MERGE INTO [dbo].[pajak_keluaran_details] WITH (HOLDLOCK) AS target
                USING (
                    SELECT * FROM #StagingData
                    WHERE row_num >= @batch_start AND row_num <= @batch_end
                ) AS source
                ON (
                    target.no_invoice = source.no_invoice
                    AND target.no_do = source.no_do
                    AND target.kode_produk = source.kode_produk
                )

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

                SET @batch_rows_affected = @@ROWCOUNT;
                SET @total_rows_affected = @total_rows_affected + @batch_rows_affected;

                COMMIT TRANSACTION;

                SET @batches_processed = @batches_processed + 1;

                IF @debug_mode = 1
                    PRINT 'Batch ' + CAST(@current_batch AS VARCHAR) + ' completed. Rows affected: ' + CAST(@batch_rows_affected AS VARCHAR);

            END TRY
            BEGIN CATCH
                IF @@TRANCOUNT > 0
                    ROLLBACK TRANSACTION;

                SET @error_message = 'Error in batch ' + CAST(@current_batch AS VARCHAR) +
                                    ': ' + ERROR_MESSAGE() +
                                    ' (Line: ' + CAST(ERROR_LINE() AS VARCHAR) + ')';

                IF @debug_mode = 1
                    PRINT @error_message;

                -- Continue to next batch or abort based on error severity
                -- For now, we abort on first error
                DROP TABLE #StagingData;
                RETURN -1;
            END CATCH

            -- Move to next batch
            SET @batch_start = @batch_end + 1;

            -- Optional: Add small delay between batches to reduce lock contention
            -- WAITFOR DELAY '00:00:00.100';
        END

        IF @debug_mode = 1
            PRINT 'Batch processing completed. Total rows affected: ' + CAST(@total_rows_affected AS VARCHAR);

        -- Cleanup
        DROP TABLE #StagingData;

        RETURN 0;

    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        SET @error_message = 'Error ' + CAST(ERROR_NUMBER() AS NVARCHAR(10)) + ': ' + ERROR_MESSAGE() +
                            ' (Line: ' + CAST(ERROR_LINE() AS NVARCHAR(10)) + ')';

        IF OBJECT_ID('tempdb..#StagingData') IS NOT NULL
            DROP TABLE #StagingData;

        RETURN -1;

    END CATCH
END
GO

/*
================================================================================
USAGE EXAMPLES:
================================================================================

-- Example 1: Sync with default batch size (5000)
DECLARE @rows INT, @batches INT, @error NVARCHAR(MAX), @result INT;
EXEC @result = sp_SyncPajakKeluaranDetailFromLive_Batch
    @pt = 'all',
    @brand = 'all',
    @depo = 'all',
    @start = '2024-01-01',
    @end = '2024-01-31',
    @tipe = '',
    @user_depo = 'all',
    @batch_size = 5000,
    @debug_mode = 0,
    @total_rows_affected = @rows OUTPUT,
    @batches_processed = @batches OUTPUT,
    @error_message = @error OUTPUT;

SELECT @result AS ReturnCode, @rows AS TotalRowsAffected,
       @batches AS BatchesProcessed, @error AS ErrorMessage;

-- Example 2: Sync with larger batches and debug mode
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

-- Example 3: Sync with smaller batches for busy system
DECLARE @rows INT, @batches INT, @error NVARCHAR(MAX), @result INT;
EXEC @result = sp_SyncPajakKeluaranDetailFromLive_Batch
    @pt = 'COMPANY_A',
    @brand = 'all',
    @depo = 'all',
    @start = '2024-01-01',
    @end = '2024-01-31',
    @tipe = '',
    @user_depo = 'all',
    @batch_size = 2000,
    @debug_mode = 1,
    @total_rows_affected = @rows OUTPUT,
    @batches_processed = @batches OUTPUT,
    @error_message = @error OUTPUT;

SELECT @result AS ReturnCode, @rows AS TotalRowsAffected,
       @batches AS BatchesProcessed, @error AS ErrorMessage;

================================================================================
WHEN TO USE THIS VERSION VS STANDARD VERSION:
================================================================================

Use sp_SyncPajakKeluaranDetailFromLive (Standard):
- Data volume < 100,000 rows
- During maintenance windows with minimal concurrent activity
- When you need all-or-nothing transaction behavior
- Single database server with sufficient tempdb space

Use sp_SyncPajakKeluaranDetailFromLive_Batch (This version):
- Data volume > 100,000 rows
- During business hours with concurrent users
- When partial success is acceptable
- Limited tempdb space or high tempdb contention
- When you need progress visibility during long operations

================================================================================
BATCH SIZE RECOMMENDATIONS:
================================================================================

| Scenario                      | Recommended Batch Size |
|-------------------------------|------------------------|
| Low activity, fast storage    | 10,000 - 20,000       |
| Normal activity               | 5,000 - 10,000        |
| High concurrency              | 2,000 - 5,000         |
| Limited tempdb                | 1,000 - 2,000         |
| Testing/debugging             | 500 - 1,000           |

================================================================================
*/
