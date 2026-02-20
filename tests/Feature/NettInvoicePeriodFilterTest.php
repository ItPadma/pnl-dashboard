<?php

use App\Models\NettInvoiceDetail;
use App\Models\NettInvoiceHeader;
use App\Models\PajakKeluaranDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $this->withoutMiddleware();

    $user = User::create([
        'name' => 'Tester Nett Invoice',
        'email' => 'tester-nett@example.com',
        'password' => 'password',
        'role' => 'admin',
        'depo' => 'all',
    ]);

    $this->actingAs($user);

    Auth::shouldReceive('user')->andReturn($user);
});

it('filters non pkp list by reporting period netting status', function () {
    /** @var \Tests\TestCase $this */
    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-001',
        'nama_customer_sistem' => 'Customer A',
        'no_invoice' => 'INV-SAME-PERIOD',
        'tgl_faktur_pajak' => '2026-02-20',
        'dpp' => 100000,
        'ppn' => 11000,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => 1,
        'hargatotal_sblm_ppn' => 100000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
    ]);

    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-002',
        'nama_customer_sistem' => 'Customer B',
        'no_invoice' => 'INV-OTHER-PERIOD',
        'tgl_faktur_pajak' => '2026-02-21',
        'dpp' => 120000,
        'ppn' => 13200,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => 1,
        'hargatotal_sblm_ppn' => 120000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
    ]);

    NettInvoiceHeader::create([
        'id_transaksi' => 'NETT-1',
        'pt' => 'PT01',
        'principal' => 'BR01',
        'depo' => 'DEPO1',
        'no_invoice' => 'INV-SAME-PERIOD',
        'invoice_value_original' => 111000,
        'invoice_value_nett' => 100000,
        'mp_bulan' => '02',
        'mp_tahun' => '2026',
        'is_checked' => true,
        'is_downloaded' => false,
        'status' => 'netted',
        'created_at' => now(),
    ]);

    NettInvoiceHeader::create([
        'id_transaksi' => 'NETT-2',
        'pt' => 'PT01',
        'principal' => 'BR01',
        'depo' => 'DEPO1',
        'no_invoice' => 'INV-OTHER-PERIOD',
        'invoice_value_original' => 133200,
        'invoice_value_nett' => 120000,
        'mp_bulan' => '01',
        'mp_tahun' => '2026',
        'is_checked' => true,
        'is_downloaded' => false,
        'status' => 'netted',
        'created_at' => now(),
    ]);

    $response = $this->postJson(route('pnl.reguler.nett-invoice.npkp-list'), [
        'pt' => ['all'],
        'brand' => ['all'],
        'depo' => ['all'],
        'periode' => '01/02/2026 - 15/03/2026',
    ]);

    $response->assertOk();

    $invoices = collect($response->json('data'))->pluck('no_invoice')->all();

    expect($invoices)->not->toContain('INV-SAME-PERIOD')
        ->and($invoices)->toContain('INV-OTHER-PERIOD');
});

it('stores reporting month and year from modal period when processing nett', function () {
    /** @var \Tests\TestCase $this */
    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-NONPKP',
        'nama_customer_sistem' => 'Customer Non PKP',
        'no_invoice' => 'INV-NPKP-001',
        'tgl_faktur_pajak' => '2026-02-22',
        'dpp' => 100000,
        'ppn' => 11000,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => 1,
        'hargatotal_sblm_ppn' => 100000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-001',
        'satuan' => 'PCS',
    ]);

    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-RETUR',
        'nama_customer_sistem' => 'Customer Retur',
        'no_invoice' => 'INV-RETUR-001',
        'tgl_faktur_pajak' => '2026-02-10',
        'dpp' => -50000,
        'ppn' => -5500,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => -1,
        'hargatotal_sblm_ppn' => -50000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-RET-001',
        'satuan' => 'PCS',
    ]);

    $response = $this->postJson(route('pnl.reguler.nett-invoice.process'), [
        'npkp_invoices' => ['INV-NPKP-001'],
        'retur_invoices' => ['INV-RETUR-001'],
        'periode' => '01/02/2026 - 15/03/2026',
    ]);

    $response->assertOk()->assertJsonPath('status', true);

    $this->assertDatabaseHas('nett_invoice_headers', [
        'no_invoice' => 'INV-NPKP-001',
        'mp_bulan' => '02',
        'mp_tahun' => '2026',
    ]);

    $this->assertDatabaseHas('nett_invoice_details', [
        'no_invoice_retur' => 'INV-RETUR-001',
        'mp_bulan' => '02',
        'mp_tahun' => '2026',
    ]);
});

it('rejects reversed period range validation', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->postJson(route('pnl.reguler.nett-invoice.npkp-list'), [
        'pt' => ['all'],
        'brand' => ['all'],
        'depo' => ['all'],
        'periode' => '16/03/2026 - 15/03/2026',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['periode']);
});

it('uses current end month when cutoff day is above 15', function () {
    /** @var \Tests\TestCase $this */
    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-NONPKP-2',
        'nama_customer_sistem' => 'Customer Non PKP 2',
        'no_invoice' => 'INV-NPKP-002',
        'tgl_faktur_pajak' => '2026-03-20',
        'dpp' => 100000,
        'ppn' => 11000,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => 1,
        'hargatotal_sblm_ppn' => 100000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-002',
        'satuan' => 'PCS',
    ]);

    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-RETUR-2',
        'nama_customer_sistem' => 'Customer Retur 2',
        'no_invoice' => 'INV-RETUR-002',
        'tgl_faktur_pajak' => '2026-03-18',
        'dpp' => -50000,
        'ppn' => -5500,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => -1,
        'hargatotal_sblm_ppn' => -50000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-RET-002',
        'satuan' => 'PCS',
    ]);

    $response = $this->postJson(route('pnl.reguler.nett-invoice.process'), [
        'npkp_invoices' => ['INV-NPKP-002'],
        'retur_invoices' => ['INV-RETUR-002'],
        'periode' => '01/03/2026 - 16/03/2026',
    ]);

    $response->assertOk()->assertJsonPath('status', true);

    $this->assertDatabaseHas('nett_invoice_headers', [
        'no_invoice' => 'INV-NPKP-002',
        'mp_bulan' => '03',
        'mp_tahun' => '2026',
    ]);
});

it('rejects process nett when selected non-pkp invoice is outside selected period', function () {
    /** @var \Tests\TestCase $this */
    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-NONPKP-3',
        'nama_customer_sistem' => 'Customer Non PKP 3',
        'no_invoice' => 'INV-NPKP-OUTSIDE',
        'tgl_faktur_pajak' => '2026-04-20',
        'dpp' => 100000,
        'ppn' => 11000,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => 1,
        'hargatotal_sblm_ppn' => 100000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-003',
        'satuan' => 'PCS',
    ]);

    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-RETUR-3',
        'nama_customer_sistem' => 'Customer Retur 3',
        'no_invoice' => 'INV-RETUR-003',
        'tgl_faktur_pajak' => '2026-03-10',
        'dpp' => -50000,
        'ppn' => -5500,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => -1,
        'hargatotal_sblm_ppn' => -50000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-RET-003',
        'satuan' => 'PCS',
    ]);

    $response = $this->postJson(route('pnl.reguler.nett-invoice.process'), [
        'npkp_invoices' => ['INV-NPKP-OUTSIDE'],
        'retur_invoices' => ['INV-RETUR-003'],
        'periode' => '01/03/2026 - 31/03/2026',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['npkp_invoices']);
});

it('returns initial and remaining retur values for partially used retur invoices in getData', function () {
    /** @var \Tests\TestCase $this */
    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-RETUR-PARTIAL',
        'nama_customer_sistem' => 'Customer Retur Partial',
        'no_invoice' => 'INV-RETUR-PARTIAL-001',
        'tgl_faktur_pajak' => '2026-02-10',
        'dpp' => -100000,
        'ppn' => -11000,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => -1,
        'hargatotal_sblm_ppn' => -100000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'has_moved' => 'n',
        'moved_to' => null,
    ]);

    NettInvoiceDetail::create([
        'id_transaksi' => 'NETT-PARTIAL-001',
        'pt' => 'PT01',
        'principal' => 'BR01',
        'depo' => 'DEPO1',
        'no_invoice_retur' => 'INV-RETUR-PARTIAL-001',
        'invoice_retur_value' => 111000,
        'remaining_value' => 45000,
        'mp_bulan' => '02',
        'mp_tahun' => '2026',
        'is_checked' => true,
        'is_downloaded' => false,
        'status' => 'partial',
        'created_at' => now(),
    ]);

    $response = $this->postJson(route('pnl.reguler.nett-invoice.data'), [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'pt' => ['all'],
        'brand' => ['all'],
        'depo' => ['all'],
        'periode' => '01/02/2026 - 28/02/2026',
    ]);

    $response->assertOk();

    $record = collect($response->json('data'))
        ->firstWhere('no_invoice', 'INV-RETUR-PARTIAL-001');

    expect($record)->not->toBeNull()
        ->and((float) $record['nilai_awal_retur'])->toBe(111000.0)
        ->and((float) $record['nilai_sisa_retur'])->toBe(45000.0)
        ->and((float) $record['nilai_retur'])->toBe(45000.0)
        ->and($record['is_partial'])->toBeTrue();
});

it('returns matching initial and remaining retur values for non-partial retur invoices in getData', function () {
    /** @var \Tests\TestCase $this */
    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-RETUR-FULL',
        'nama_customer_sistem' => 'Customer Retur Full',
        'no_invoice' => 'INV-RETUR-FULL-001',
        'tgl_faktur_pajak' => '2026-02-11',
        'dpp' => -200000,
        'ppn' => -22000,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => -2,
        'hargatotal_sblm_ppn' => -200000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'has_moved' => 'n',
        'moved_to' => null,
    ]);

    $response = $this->postJson(route('pnl.reguler.nett-invoice.data'), [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'pt' => ['all'],
        'brand' => ['all'],
        'depo' => ['all'],
        'periode' => '01/02/2026 - 28/02/2026',
    ]);

    $response->assertOk();

    $record = collect($response->json('data'))
        ->firstWhere('no_invoice', 'INV-RETUR-FULL-001');

    expect($record)->not->toBeNull()
        ->and((float) $record['nilai_awal_retur'])->toBe(222000.0)
        ->and((float) $record['nilai_sisa_retur'])->toBe(222000.0)
        ->and((float) $record['nilai_retur'])->toBe(222000.0)
        ->and($record['is_partial'])->toBeFalse();
});

it('returns backward-compatible alias fields in legacy retur-list endpoint', function () {
    /** @var \Tests\TestCase $this */
    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-RETUR-LEGACY',
        'nama_customer_sistem' => 'Customer Retur Legacy',
        'no_invoice' => 'INV-RETUR-LEGACY-001',
        'tgl_faktur_pajak' => '2026-02-12',
        'dpp' => -150000,
        'ppn' => -16500,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => -1,
        'hargatotal_sblm_ppn' => -150000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'has_moved' => 'n',
        'moved_to' => null,
    ]);

    NettInvoiceDetail::create([
        'id_transaksi' => 'NETT-LEGACY-001',
        'pt' => 'PT01',
        'principal' => 'BR01',
        'depo' => 'DEPO1',
        'no_invoice_retur' => 'INV-RETUR-LEGACY-001',
        'invoice_retur_value' => 166500,
        'remaining_value' => 66500,
        'mp_bulan' => '02',
        'mp_tahun' => '2026',
        'is_checked' => true,
        'is_downloaded' => false,
        'status' => 'partial',
        'created_at' => now(),
    ]);

    $response = $this->postJson(route('pnl.reguler.nett-invoice.retur-list'), [
        'pt' => ['all'],
        'brand' => ['all'],
        'depo' => ['all'],
        'periode' => '01/02/2026 - 28/02/2026',
    ]);

    $response->assertOk()->assertJsonPath('status', true);

    $record = collect($response->json('data'))
        ->firstWhere('no_invoice', 'INV-RETUR-LEGACY-001');

    expect($record)->not->toBeNull()
        ->and((float) $record['nilai_awal_retur'])->toBe(166500.0)
        ->and((float) $record['nilai_sisa_retur'])->toBe(66500.0)
        ->and((float) $record['nilai_retur'])->toBe(66500.0);
});

it('process nett uses remaining retur value when partial retur detail exists', function () {
    /** @var \Tests\TestCase $this */
    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-NONPKP-PARTIAL',
        'nama_customer_sistem' => 'Customer Non PKP Partial',
        'no_invoice' => 'INV-NPKP-PARTIAL-001',
        'tgl_faktur_pajak' => '2026-02-20',
        'dpp' => 100000,
        'ppn' => 11000,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => 1,
        'hargatotal_sblm_ppn' => 100000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-NPKP-PARTIAL',
        'satuan' => 'PCS',
    ]);

    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-RETUR-PARTIAL-2',
        'nama_customer_sistem' => 'Customer Retur Partial 2',
        'no_invoice' => 'INV-RETUR-PARTIAL-002',
        'tgl_faktur_pajak' => '2026-02-10',
        'dpp' => -100000,
        'ppn' => -11000,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => -1,
        'hargatotal_sblm_ppn' => -100000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-RET-PARTIAL',
        'satuan' => 'PCS',
        'has_moved' => 'n',
        'moved_to' => null,
    ]);

    NettInvoiceDetail::create([
        'id_transaksi' => 'NETT-OLD-PARTIAL-001',
        'pt' => 'PT01',
        'principal' => 'BR01',
        'depo' => 'DEPO1',
        'no_invoice_retur' => 'INV-RETUR-PARTIAL-002',
        'invoice_retur_value' => 111000,
        'remaining_value' => 30000,
        'mp_bulan' => '02',
        'mp_tahun' => '2026',
        'is_checked' => true,
        'is_downloaded' => false,
        'status' => 'partial',
        'created_at' => now()->subDay(),
    ]);

    $response = $this->postJson(route('pnl.reguler.nett-invoice.process'), [
        'npkp_invoices' => ['INV-NPKP-PARTIAL-001'],
        'retur_invoices' => ['INV-RETUR-PARTIAL-002'],
        'periode' => '01/02/2026 - 28/02/2026',
    ]);

    $response->assertOk()->assertJsonPath('status', true);

    $this->assertDatabaseHas('nett_invoice_histories', [
        'no_invoice_npkp' => 'INV-NPKP-PARTIAL-001',
        'no_invoice_retur' => 'INV-RETUR-PARTIAL-002',
        'nilai_retur_used' => 30000,
        'remaining_value' => 0,
    ]);

    $this->assertDatabaseHas('nett_invoice_details', [
        'no_invoice_retur' => 'INV-RETUR-PARTIAL-002',
        'remaining_value' => 0,
        'status' => 'used',
    ]);
});

it('rejects process nett when selected retur invoice is outside selected period', function () {
    /** @var \Tests\TestCase $this */
    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-NONPKP-RETUR-OUTSIDE',
        'nama_customer_sistem' => 'Customer Non PKP Retur Outside',
        'no_invoice' => 'INV-NPKP-RETUR-OUTSIDE-001',
        'tgl_faktur_pajak' => '2026-03-15',
        'dpp' => 100000,
        'ppn' => 11000,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => 1,
        'hargatotal_sblm_ppn' => 100000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-NPKP-OUTSIDE-RET',
        'satuan' => 'PCS',
    ]);

    PajakKeluaranDetail::create([
        'customer_id' => 'CUST-RETUR-OUTSIDE',
        'nama_customer_sistem' => 'Customer Retur Outside',
        'no_invoice' => 'INV-RETUR-OUTSIDE-001',
        'tgl_faktur_pajak' => '2026-04-10',
        'dpp' => -50000,
        'ppn' => -5500,
        'is_downloaded' => false,
        'tipe_ppn' => 'PPN',
        'qty_pcs' => -1,
        'hargatotal_sblm_ppn' => -50000,
        'company' => 'PT01',
        'brand' => 'BR01',
        'depo' => 'DEPO1',
        'kode_produk' => 'BRG-RET-OUTSIDE-001',
        'satuan' => 'PCS',
        'has_moved' => 'n',
        'moved_to' => null,
    ]);

    $response = $this->postJson(route('pnl.reguler.nett-invoice.process'), [
        'npkp_invoices' => ['INV-NPKP-RETUR-OUTSIDE-001'],
        'retur_invoices' => ['INV-RETUR-OUTSIDE-001'],
        'periode' => '01/03/2026 - 31/03/2026',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['retur_invoices']);
});
