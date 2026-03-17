<?php

/**
 * Tests for PajakKeluaran export classes.
 *
 * NOTE: These tests require the Laravel container (app() helper) because the
 * export classes use MasterDataCacheService in their constructors.
 * Run with: php artisan test --filter PajakKeluaranExportTest
 */

use App\Exports\PajakKeluaranDetailExport;
use App\Exports\PajakKeluaranTemplateExport;
use App\Exports\Sheets\StreamingDetailFakturSheet;
use App\Exports\Sheets\StreamingFakturSheet;
use App\Models\PajakKeluaranDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware();

    $user = User::create([
        'name' => 'Tester Export',
        'email' => 'tester-export@example.com',
        'password' => 'password',
        'role' => 'admin',
        'depo' => 'all',
    ]);

    $this->actingAs($user);

    Auth::shouldReceive('user')->andReturn($user);
});

// =============================================================================
// PajakKeluaranDetailExport
// =============================================================================

describe('PajakKeluaranDetailExport', function () {

    it('has correct interfaces implemented', function () {
        $export = new PajakKeluaranDetailExport('pkp');

        expect($export)->toBeInstanceOf(\Maatwebsite\Excel\Concerns\FromQuery::class);
        expect($export)->toBeInstanceOf(\Maatwebsite\Excel\Concerns\WithChunkReading::class);
        expect($export)->toBeInstanceOf(\Maatwebsite\Excel\Concerns\WithHeadings::class);
        expect($export)->toBeInstanceOf(\Maatwebsite\Excel\Concerns\WithMapping::class);
    });

    it('returns chunk size of 1000', function () {
        $export = new PajakKeluaranDetailExport('pkp');

        expect($export->chunkSize())->toBe(1000);
    });

    it('normalizes single tipe to array', function () {
        $export = new PajakKeluaranDetailExport('retur');

        $reflection = new ReflectionClass($export);
        $property = $reflection->getProperty('tipe');
        $property->setAccessible(true);

        expect($property->getValue($export))->toBe(['retur']);
    });

    it('normalizes all tipe to full list', function () {
        $export = new PajakKeluaranDetailExport('all');

        $reflection = new ReflectionClass($export);
        $property = $reflection->getProperty('tipe');
        $property->setAccessible(true);

        expect($property->getValue($export))->toBe([
            'pkp', 'pkpnppn', 'npkp', 'npkpnppn', 'retur', 'nonstandar', 'pembatalan', 'koreksi', 'pending',
        ]);
    });

    it('headings excludes internal columns', function () {
        $export = new PajakKeluaranDetailExport('pkp');

        $headings = $export->headings();

        expect($headings)->not->toContain('id');
        expect($headings)->not->toContain('is_checked');
        expect($headings)->not->toContain('is_downloaded');
        expect($headings)->not->toContain('created_at');
        expect($headings)->not->toContain('updated_at');
    });

    it('headings includes expected business columns', function () {
        $export = new PajakKeluaranDetailExport('pkp');

        $headings = $export->headings();

        expect($headings)->toContain('no_invoice');
        expect($headings)->toContain('nik');
        expect($headings)->toContain('kode_produk');
        expect($headings)->toContain('nama_produk');
        expect($headings)->toContain('dpp');
        expect($headings)->toContain('ppn');
    });

    it('map prefixes nik and kode_produk with apostrophe', function () {
        $export = new PajakKeluaranDetailExport('pkp');

        $record = PajakKeluaranDetail::create([
            'nik' => '123456789012345',
            'kode_produk' => 'PROD001',
            'no_invoice' => 'INV-001',
        ]);

        $mapped = $export->map($record);

        expect($mapped['nik'])->toBe("'123456789012345");
        expect($mapped['kode_produk'])->toBe("'PROD001");
        expect($mapped['no_invoice'])->toBe('INV-001');
    });
});

// =============================================================================
// PajakKeluaranTemplateExport
// =============================================================================

describe('PajakKeluaranTemplateExport', function () {

    it('returns two sheets', function () {
        $export = new PajakKeluaranTemplateExport('pkp');

        $sheets = $export->sheets();

        expect($sheets)->toBeArray();
        expect(count($sheets))->toBe(2);
    });

    it('first sheet is StreamingFakturSheet', function () {
        $export = new PajakKeluaranTemplateExport('pkp');

        $sheets = $export->sheets();

        expect($sheets[0])->toBeInstanceOf(StreamingFakturSheet::class);
    });

    it('second sheet is StreamingDetailFakturSheet', function () {
        $export = new PajakKeluaranTemplateExport('pkp');

        $sheets = $export->sheets();

        expect($sheets[1])->toBeInstanceOf(StreamingDetailFakturSheet::class);
    });
});

// =============================================================================
// StreamingFakturSheet
// =============================================================================

describe('StreamingFakturSheet', function () {

    it('returns title Faktur', function () {
        $sheet = new StreamingFakturSheet(
            'pkp', [], [], [], null, null,
            '0027139484612000', '0027139484612000000000',
            [], [], [],
        );

        expect($sheet->title())->toBe('Faktur');
    });

    it('returns chunk size of 500', function () {
        $sheet = new StreamingFakturSheet(
            'pkp', [], [], [], null, null, [], [], [],
        );

        expect($sheet->chunkSize())->toBe(500);
    });

    it('headings returns 18 columns with correct order', function () {
        $sheet = new StreamingFakturSheet(
            'pkp', [], [], [], null, null, [], [], [],
        );

        $headings = $sheet->headings();

        expect(count($headings))->toBe(18);
        expect($headings[0])->toBe('Baris');
        expect($headings[1])->toBe('Tanggal Faktur');
        expect($headings[2])->toBe('Jenis Faktur');
        expect($headings[3])->toBe('Kode Transaksi');
        expect($headings[9])->toBe('Cap Fasilitas');
        expect($headings[10])->toBe('ID TKU Penjual');
        expect($headings[11])->toBe('NPWP/NIK Pembeli');
        expect($headings[17])->toBe('ID TKU Pembeli');
    });

    it('bindValue binds NPWP columns as explicit strings', function () {
        $sheet = new StreamingFakturSheet(
            'pkp', [], [], [], null, null,
            '0027139484612000', '0027139484612000000000',
            [], [], [],
        );

        $method = new ReflectionMethod($sheet, 'bindValue');
        $method->setAccessible(true);

        // Create a mock cell
        $cell = new class
        {
            private $column = '';

            private $value = '';

            public function getColumn()
            {
                return $this->column;
            }

            public function getValue()
            {
                return $this->value;
            }
        };

        // C column should return true (explicit string binding)
        $cell->column = 'C';
        $cell->value = '0027139484612000';
        $result = $method->invoke($sheet, $cell, '0027139484612000');
        expect($result)->toBeTrue();

        // K column should return true
        $cell->column = 'K';
        $cell->value = '12345';
        $result = $method->invoke($sheet, $cell, '12345');
        expect($result)->toBeTrue();

        // R column should return true
        $cell->column = 'R';
        $cell->value = '999999999';
        $result = $method->invoke($sheet, $cell, '999999');
        expect($result)->toBeTrue();

        // Non-target column should return false (default behavior)
        $cell->column = 'A';
        $cell->value = 'test';
        $result = $method->invoke($sheet, $cell, 'test');
        expect($result)->toBeFalse();
    });
});

// =============================================================================
// StreamingDetailFakturSheet
// =============================================================================

describe('StreamingDetailFakturSheet', function () {

    it('returns title DetailFaktur', function () {
        $sheet = new StreamingDetailFakturSheet(
            'pkp', [], [], [], null, null, [], [], [],
        );

        expect($sheet->title())->toBe('DetailFaktur');
    });

    it('returns chunk size of 1000', function () {
        $sheet = new StreamingDetailFakturSheet(
            'pkp', [], [], [], null, null, [], [], [],
        );

        expect($sheet->chunkSize())->toBe(1000);
    });

    it('headings returns 14 columns', function () {
        $sheet = new StreamingDetailFakturSheet(
            'pkp', [], [], [], null, null, [], [], [],
        );

        $headings = $sheet->headings();

        expect(count($headings))->toBe(14);
        expect($headings[0])->toBe('Baris');
        expect($headings[1])->toBe('Barang/Jasa');
        expect($headings[4])->toBe('Nama Satuan Ukur');
        expect($headings[13])->toBe('PPnBM');
    });

    it('map tracks invoice boundaries for baris numbering', function () {
        $sheet = new StreamingDetailFakturSheet(
            'pkp', [], [], [], null, null, [], [], [],
        );

        $method = new ReflectionMethod($sheet, 'map');
        $method->setAccessible(true);

        $row1 = new PajakKeluaranDetail([
            'no_invoice' => 'INV-A',
            'nama_produk' => 'P1',
        ]);
        $row2 = new PajakKeluaranDetail([
            'no_invoice' => 'INV-A',
            'nama_produk' => 'P2',
        ]);
        $row3 = new PajakKeluaranDetail([
            'no_invoice' => 'INV-B',
            'nama_produk' => 'P3',
        ]);

        $result1 = $method->invoke($sheet, $row1);
        $result2 = $method->invoke($sheet, $row2);
        $result3 = $method->invoke($sheet, $row3);

        expect($result1[0])->toBe(1);
        expect($result2[0])->toBe(1);
        expect($result3[0])->toBe(2);
    });

    it('map calculates dpp and ppn correctly', function () {
        $sheet = new StreamingDetailFakturSheet(
            'pkp', [], [], [], null, null, [], [], [],
        );

        $method = new ReflectionMethod($sheet, 'map');
        $method->setAccessible(true);

        $row = new PajakKeluaranDetail([
            'no_invoice' => 'INV-C',
            'nama_produk' => 'Test',
            'hargasatuan_sblm_ppn' => 10000,
            'qty_pcs' => 10,
            'disc' => 500,
            'satuan' => 'PCS',
            'barang_jasa' => 'A',
        ]);

        $result = $method->invoke($sheet, $row);

        expect($result[8])->toBe(99500.0);
        expect($result[9])->toBe(round(99500 * 11 / 12, 2));
    });

    it('mapSatuan returns default for empty value', function () {
        $sheet = new StreamingDetailFakturSheet(
            'pkp', [], [], [], null, null, [], [], [],
        );

        $method = new ReflectionMethod($sheet, 'mapSatuan');
        $method->setAccessible(true);

        expect($method->invoke($sheet, null))->toBe('UM.0033');
        expect($method->invoke($sheet, ''))->toBe('UM.0033');
    });

    it('mapSatuan returns mapped value for known satuan', function () {
        $sheet = new StreamingDetailFakturSheet(
            'pkp', [], [], [], null, null, [], [],
            ['KARTON' => 'UM.0001', 'PCS' => 'UM.0002'],
        );

        $method = new ReflectionMethod($sheet, 'mapSatuan');
        $method->setAccessible(true);

        expect($method->invoke($sheet, 'PCS'))->toBe('UM.0002');
        expect($method->invoke($sheet, 'pcs'))->toBe('UM.0002');
        expect($method->invoke($sheet, 'KARTON'))->toBe('UM.0001');
        expect($method->invoke($sheet, 'KG'))->toBe('UM.0033');
    });

    it('mapSatuan caches the map after first build', function () {
        $sheet = new StreamingDetailFakturSheet(
            'pkp', [], [], [], null, null, [], [],
            ['PCS' => 'UM.0002'],
        );

        $method = new ReflectionMethod($sheet, 'mapSatuan');
        $method->setAccessible(true);

        $result1 = $method->invoke($sheet, 'PCS');

        $prop = new ReflectionProperty($sheet, 'satuanMap');
        $prop->setAccessible(true);
        $cachedMap = $prop->getValue($sheet);

        expect($cachedMap)->not->toBeNull();
        expect($cachedMap['PCS'])->toBe('UM.0002');
        expect($result1)->toBe('UM.0002');
    });
});
