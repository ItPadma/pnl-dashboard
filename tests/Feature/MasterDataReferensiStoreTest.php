<?php

use App\Models\AccessGroup;
use App\Models\MasterRefKodeTransaksi;
use App\Models\MasterRefTipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

function fakeReferensiEditorUser(): object
{
    return new class
    {
        public int $id = 1;

        public function canAccessMenu($menu, int $requiredLevel = 1): bool
        {
            return $menu === 'master-data-referensi'
                && $requiredLevel === AccessGroup::LEVEL_READ_WRITE;
        }
    };
}

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $this->withoutMiddleware();

    Auth::shouldReceive('user')
        ->andReturn(fakeReferensiEditorUser());
});

it('can store tipe referensi manually', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->post(route('pnl.master-data.store.referensi', ['type' => 'tipe']), [
        'kode' => 'TP-001',
        'keterangan' => 'Tipe manual',
    ]);

    $response
        ->assertRedirect(route('pnl.master-data.index.referensi'))
        ->assertSessionHas('success', 'Data referensi berhasil ditambahkan.');

    $this->assertDatabaseHas('master_ref_tipe', [
        'kode' => 'TP-001',
        'keterangan' => 'Tipe manual',
        'is_active' => true,
    ]);
});

it('can store keterangan tambahan referensi manually with kode transaksi relation', function () {
    /** @var \Tests\TestCase $this */
    MasterRefKodeTransaksi::create([
        'kode' => 'KT-001',
        'keterangan' => 'Kode transaksi uji',
        'is_active' => true,
    ]);

    $response = $this->post(route('pnl.master-data.store.referensi', ['type' => 'keterangan-tambahan']), [
        'kode' => 'KET-001',
        'kode_transaksi_id' => 'KT-001',
        'keterangan' => 'Keterangan tambahan manual',
    ]);

    $response
        ->assertRedirect(route('pnl.master-data.index.referensi'))
        ->assertSessionHas('success', 'Data referensi berhasil ditambahkan.');

    $this->assertDatabaseHas('master_ref_keterangan_tambahan', [
        'kode' => 'KET-001',
        'kode_transaksi_id' => 'KT-001',
        'keterangan' => 'Keterangan tambahan manual',
        'is_active' => true,
    ]);
});

it('validates unique kode for referensi store', function () {
    /** @var \Tests\TestCase $this */
    MasterRefTipe::create([
        'kode' => 'DUP-001',
        'keterangan' => 'Sudah ada',
        'is_active' => true,
    ]);

    $response = $this->from(route('pnl.master-data.index.referensi'))
        ->post(route('pnl.master-data.store.referensi', ['type' => 'tipe']), [
            'kode' => 'DUP-001',
            'keterangan' => 'Duplikat',
        ]);

    $response
        ->assertRedirect(route('pnl.master-data.index.referensi'))
        ->assertSessionHasErrors(['kode']);

    expect(MasterRefTipe::where('kode', 'DUP-001')->count())->toBe(1);
});
