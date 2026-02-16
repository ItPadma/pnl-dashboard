@extends('layouts.master')

@section('title', 'PNL - Referensi')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Referensi</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="/">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Master Data</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Referensi</a>
                    </li>
                </ul>
            </div>

            <ul class="nav nav-pills ref-tabs" id="referensiTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-tipe" data-bs-toggle="pill" data-bs-target="#pane-tipe" type="button" role="tab" aria-controls="pane-tipe" aria-selected="true">Tipe</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-kode-transaksi" data-bs-toggle="pill" data-bs-target="#pane-kode-transaksi" type="button" role="tab" aria-controls="pane-kode-transaksi" aria-selected="false">Kode Transaksi</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-keterangan-tambahan" data-bs-toggle="pill" data-bs-target="#pane-keterangan-tambahan" type="button" role="tab" aria-controls="pane-keterangan-tambahan" aria-selected="false">Keterangan Tambahan</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-id-pembeli" data-bs-toggle="pill" data-bs-target="#pane-id-pembeli" type="button" role="tab" aria-controls="pane-id-pembeli" aria-selected="false">ID Pembeli</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-satuan-ukur" data-bs-toggle="pill" data-bs-target="#pane-satuan-ukur" type="button" role="tab" aria-controls="pane-satuan-ukur" aria-selected="false">Satuan Ukur</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-kode-negara" data-bs-toggle="pill" data-bs-target="#pane-kode-negara" type="button" role="tab" aria-controls="pane-kode-negara" aria-selected="false">Kode Negara</button>
                </li>
            </ul>

            <div class="tab-content mt-4" id="referensiTabContent">
                <div class="tab-pane fade show active" id="pane-tipe" role="tabpanel" aria-labelledby="tab-tipe"
                    data-store-url="{{ route('pnl.master-data.store.referensi', ['type' => 'tipe']) }}"
                    data-update-url="{{ route('pnl.master-data.update.referensi', ['type' => 'tipe', 'id' => '__ID__']) }}"
                    data-toggle-url="{{ route('pnl.master-data.toggle.referensi', ['type' => 'tipe', 'id' => '__ID__']) }}">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Import</p>
                                        <h4 class="card-title mb-0">Referensi Tipe</h4>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary">XLSX</span>
                                </div>
                                <div class="card-body">
                                    @include('layouts.alert')
                                    <form action="{{ route('pnl.master-data.import.referensi', ['type' => 'tipe']) }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        @method('post')
                                        <div class="form-group">
                                            <label for="file-tipe" class="form-label">Upload File</label>
                                            <input type="file" name="file" class="form-control" id="file-tipe" accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required @if (! $canEdit) disabled @endif>
                                            <small class="text-muted">Gunakan template resmi agar format konsisten.</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif><i class="fas fa-file-import fa-fw"></i> Import</button>
                                            <a href="{{ asset('assets/TemplateMasterRefTipe.xlsx') }}" class="btn btn-success"><i class="fas fa-download fa-fw"></i> Download Template</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm mt-4 ref-card">
                                <div class="card-header ref-card-header">
                                    <p class="text-uppercase text-muted mb-1 small">Detail</p>
                                    <h4 class="card-title mb-0">Lihat & Edit</h4>
                                </div>
                                <div class="card-body">
                                    <form id="form-tipe" method="post" action="#">
                                        @csrf
                                        <input type="hidden" name="_method" id="tipe-method" value="post">
                                        <input type="hidden" name="id" id="tipe-id">
                                        <div class="form-group">
                                            <label for="tipe-kode" class="form-label">Kode</label>
                                            <input type="text" name="kode" id="tipe-kode" class="form-control" required @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="form-group">
                                            <label for="tipe-keterangan" class="form-label">Keterangan</label>
                                            <input type="text" name="keterangan" id="tipe-keterangan" class="form-control" @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif>
                                                <i class="fas fa-save fa-fw"></i> Simpan
                                            </button>
                                            <button type="button" class="btn btn-light" data-reset="tipe">Reset</button>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        <p class="text-muted mb-0 small">Pilih data pada tabel untuk menampilkan detail di sini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Data</p>
                                        <h4 class="card-title mb-0">Referensi Tipe</h4>
                                    </div>
                                    <span class="badge bg-dark-subtle text-dark">{{ $refTipe->count() }} Data</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive ref-table-compact">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small">Tampilkan</span>
                                                <select id="tipe-page-length" class="form-select form-select-sm" style="width: auto;">
                                                    <option value="5">5</option>
                                                    <option value="8" selected>8</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                </select>
                                                <span class="text-muted small">baris</span>
                                            </div>
                                            <div class="text-muted small">Gunakan pencarian untuk mempercepat.</div>
                                        </div>
                                        <table class="table table-striped table-hover" id="table-tipe">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Keterangan</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($refTipe as $item)
                                                    <tr data-id="{{ $item->id }}"
                                                        data-kode='@json($item->kode)'
                                                        data-keterangan='@json($item->keterangan)'>
                                                        <td class="fw-semibold">{{ $item->kode }}</td>
                                                        <td>
                                                            {{ $item->keterangan ?? '-' }}
                                                            @if ($item->is_active === false)
                                                                <span class="badge bg-secondary-subtle text-secondary ms-1">Nonaktif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-outline-primary ref-show" data-ref="tipe">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @if ($item->is_active ?? true)
                                                                <button type="button" class="btn btn-sm btn-outline-danger ref-toggle" data-ref="tipe" data-active="0" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-success ref-toggle" data-ref="tipe" data-active="1" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-kode-transaksi" role="tabpanel" aria-labelledby="tab-kode-transaksi"
                    data-store-url="{{ route('pnl.master-data.store.referensi', ['type' => 'kode-transaksi']) }}"
                    data-update-url="{{ route('pnl.master-data.update.referensi', ['type' => 'kode-transaksi', 'id' => '__ID__']) }}"
                    data-toggle-url="{{ route('pnl.master-data.toggle.referensi', ['type' => 'kode-transaksi', 'id' => '__ID__']) }}">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Import</p>
                                        <h4 class="card-title mb-0">Referensi Kode Transaksi</h4>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary">XLSX</span>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('pnl.master-data.import.referensi', ['type' => 'kode-transaksi']) }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        @method('post')
                                        <div class="form-group">
                                            <label for="file-kode-transaksi" class="form-label">Upload File</label>
                                            <input type="file" name="file" class="form-control" id="file-kode-transaksi" accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required @if (! $canEdit) disabled @endif>
                                            <small class="text-muted">Gunakan template resmi agar format konsisten.</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif><i class="fas fa-file-import fa-fw"></i> Import</button>
                                            <a href="{{ asset('assets/TemplateMasterRefKodeTransaksi.xlsx') }}" class="btn btn-success"><i class="fas fa-download fa-fw"></i> Download Template</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm mt-4 ref-card">
                                <div class="card-header ref-card-header">
                                    <p class="text-uppercase text-muted mb-1 small">Detail</p>
                                    <h4 class="card-title mb-0">Lihat & Edit</h4>
                                </div>
                                <div class="card-body">
                                    <form id="form-kode-transaksi" method="post" action="#">
                                        @csrf
                                        <input type="hidden" name="_method" id="kode-transaksi-method" value="post">
                                        <input type="hidden" name="id" id="kode-transaksi-id">
                                        <div class="form-group">
                                            <label for="kode-transaksi-kode" class="form-label">Kode</label>
                                            <input type="text" name="kode" id="kode-transaksi-kode" class="form-control" required @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="form-group">
                                            <label for="kode-transaksi-keterangan" class="form-label">Keterangan</label>
                                            <input type="text" name="keterangan" id="kode-transaksi-keterangan" class="form-control" @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif>
                                                <i class="fas fa-save fa-fw"></i> Simpan
                                            </button>
                                            <button type="button" class="btn btn-light" data-reset="kode-transaksi">Reset</button>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        <p class="text-muted mb-0 small">Pilih data pada tabel untuk menampilkan detail di sini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Data</p>
                                        <h4 class="card-title mb-0">Referensi Kode Transaksi</h4>
                                    </div>
                                    <span class="badge bg-dark-subtle text-dark">{{ $refKodeTransaksi->count() }} Data</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive ref-table-compact">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small">Tampilkan</span>
                                                <select id="kode-transaksi-page-length" class="form-select form-select-sm" style="width: auto;">
                                                    <option value="5">5</option>
                                                    <option value="8" selected>8</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                </select>
                                                <span class="text-muted small">baris</span>
                                            </div>
                                            <div class="text-muted small">Gunakan pencarian untuk mempercepat.</div>
                                        </div>
                                        <table class="table table-striped table-hover" id="table-kode-transaksi">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Keterangan</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($refKodeTransaksi as $item)
                                                    <tr data-id="{{ $item->id }}"
                                                        data-kode='@json($item->kode)'
                                                        data-keterangan='@json($item->keterangan)'>
                                                        <td class="fw-semibold">{{ $item->kode }}</td>
                                                        <td>
                                                            {{ $item->keterangan ?? '-' }}
                                                            @if ($item->is_active === false)
                                                                <span class="badge bg-secondary-subtle text-secondary ms-1">Nonaktif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-outline-primary ref-show" data-ref="kode-transaksi">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @if ($item->is_active ?? true)
                                                                <button type="button" class="btn btn-sm btn-outline-danger ref-toggle" data-ref="kode-transaksi" data-active="0" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-success ref-toggle" data-ref="kode-transaksi" data-active="1" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-keterangan-tambahan" role="tabpanel" aria-labelledby="tab-keterangan-tambahan"
                    data-store-url="{{ route('pnl.master-data.store.referensi', ['type' => 'keterangan-tambahan']) }}"
                    data-update-url="{{ route('pnl.master-data.update.referensi', ['type' => 'keterangan-tambahan', 'id' => '__ID__']) }}"
                    data-toggle-url="{{ route('pnl.master-data.toggle.referensi', ['type' => 'keterangan-tambahan', 'id' => '__ID__']) }}">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Import</p>
                                        <h4 class="card-title mb-0">Referensi Keterangan Tambahan</h4>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary">XLSX</span>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('pnl.master-data.import.referensi', ['type' => 'keterangan-tambahan']) }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        @method('post')
                                        <div class="form-group">
                                            <label for="file-keterangan-tambahan" class="form-label">Upload File</label>
                                            <input type="file" name="file" class="form-control" id="file-keterangan-tambahan" accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required @if (! $canEdit) disabled @endif>
                                            <small class="text-muted">Gunakan template resmi agar format konsisten.</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif><i class="fas fa-file-import fa-fw"></i> Import</button>
                                            <a href="{{ asset('assets/TemplateMasterRefKeteranganTambahan.xlsx') }}" class="btn btn-success"><i class="fas fa-download fa-fw"></i> Download Template</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm mt-4 ref-card">
                                <div class="card-header ref-card-header">
                                    <p class="text-uppercase text-muted mb-1 small">Detail</p>
                                    <h4 class="card-title mb-0">Lihat & Edit</h4>
                                </div>
                                <div class="card-body">
                                    <form id="form-keterangan-tambahan" method="post" action="#">
                                        @csrf
                                        <input type="hidden" name="_method" id="keterangan-tambahan-method" value="post">
                                        <input type="hidden" name="id" id="keterangan-tambahan-id">
                                        <div class="form-group">
                                            <label for="keterangan-tambahan-kode" class="form-label">Kode</label>
                                            <input type="text" name="kode" id="keterangan-tambahan-kode" class="form-control" required @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="form-group">
                                            <label for="keterangan-tambahan-kode-transaksi" class="form-label">Kode Transaksi</label>
                                            <select name="kode_transaksi_id" id="keterangan-tambahan-kode-transaksi" class="form-select" @if (! $canEdit) disabled @endif>
                                                <option value="">Pilih Kode Transaksi</option>
                                                @foreach ($refKodeTransaksi as $kodeTransaksi)
                                                    <option value="{{ $kodeTransaksi->kode }}">{{ $kodeTransaksi->kode }} - {{ $kodeTransaksi->keterangan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="keterangan-tambahan-keterangan" class="form-label">Keterangan</label>
                                            <input type="text" name="keterangan" id="keterangan-tambahan-keterangan" class="form-control" @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif>
                                                <i class="fas fa-save fa-fw"></i> Simpan
                                            </button>
                                            <button type="button" class="btn btn-light" data-reset="keterangan-tambahan">Reset</button>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        <p class="text-muted mb-0 small">Pilih data pada tabel untuk menampilkan detail di sini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Data</p>
                                        <h4 class="card-title mb-0">Referensi Keterangan Tambahan</h4>
                                    </div>
                                    <span class="badge bg-dark-subtle text-dark">{{ $refKeteranganTambahan->count() }} Data</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive ref-table-compact">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small">Tampilkan</span>
                                                <select id="keterangan-tambahan-page-length" class="form-select form-select-sm" style="width: auto;">
                                                    <option value="5">5</option>
                                                    <option value="8" selected>8</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                </select>
                                                <span class="text-muted small">baris</span>
                                            </div>
                                            <div class="text-muted small">Gunakan pencarian untuk mempercepat.</div>
                                        </div>
                                        <table class="table table-striped table-hover" id="table-keterangan-tambahan">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Kode Transaksi</th>
                                                    <th>Keterangan</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($refKeteranganTambahan as $item)
                                                    <tr data-id="{{ $item->id }}"
                                                        data-kode='@json($item->kode)'
                                                        data-kode-transaksi-id='@json($item->kode_transaksi_id)'
                                                        data-keterangan='@json($item->keterangan)'>
                                                        <td class="fw-semibold">{{ $item->kode }}</td>
                                                        <td>
                                                            {{ $item->kodeTransaksi?->kode ?? '-' }}
                                                        </td>
                                                        <td>
                                                            {{ $item->keterangan ?? '-' }}
                                                            @if ($item->is_active === false)
                                                                <span class="badge bg-secondary-subtle text-secondary ms-1">Nonaktif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-outline-primary ref-show" data-ref="keterangan-tambahan">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @if ($item->is_active ?? true)
                                                                <button type="button" class="btn btn-sm btn-outline-danger ref-toggle" data-ref="keterangan-tambahan" data-active="0" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-success ref-toggle" data-ref="keterangan-tambahan" data-active="1" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">Belum ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-id-pembeli" role="tabpanel" aria-labelledby="tab-id-pembeli"
                    data-store-url="{{ route('pnl.master-data.store.referensi', ['type' => 'id-pembeli']) }}"
                    data-update-url="{{ route('pnl.master-data.update.referensi', ['type' => 'id-pembeli', 'id' => '__ID__']) }}"
                    data-toggle-url="{{ route('pnl.master-data.toggle.referensi', ['type' => 'id-pembeli', 'id' => '__ID__']) }}">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Import</p>
                                        <h4 class="card-title mb-0">Referensi ID Pembeli</h4>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary">XLSX</span>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('pnl.master-data.import.referensi', ['type' => 'id-pembeli']) }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        @method('post')
                                        <div class="form-group">
                                            <label for="file-id-pembeli" class="form-label">Upload File</label>
                                            <input type="file" name="file" class="form-control" id="file-id-pembeli" accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required @if (! $canEdit) disabled @endif>
                                            <small class="text-muted">Gunakan template resmi agar format konsisten.</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif><i class="fas fa-file-import fa-fw"></i> Import</button>
                                            <a href="{{ asset('assets/TemplateMasterRefIdPembeli.xlsx') }}" class="btn btn-success"><i class="fas fa-download fa-fw"></i> Download Template</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm mt-4 ref-card">
                                <div class="card-header ref-card-header">
                                    <p class="text-uppercase text-muted mb-1 small">Detail</p>
                                    <h4 class="card-title mb-0">Lihat & Edit</h4>
                                </div>
                                <div class="card-body">
                                    <form id="form-id-pembeli" method="post" action="#">
                                        @csrf
                                        <input type="hidden" name="_method" id="id-pembeli-method" value="post">
                                        <input type="hidden" name="id" id="id-pembeli-id">
                                        <div class="form-group">
                                            <label for="id-pembeli-kode" class="form-label">Kode</label>
                                            <input type="text" name="kode" id="id-pembeli-kode" class="form-control" required @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="form-group">
                                            <label for="id-pembeli-keterangan" class="form-label">Keterangan</label>
                                            <input type="text" name="keterangan" id="id-pembeli-keterangan" class="form-control" @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif>
                                                <i class="fas fa-save fa-fw"></i> Simpan
                                            </button>
                                            <button type="button" class="btn btn-light" data-reset="id-pembeli">Reset</button>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        <p class="text-muted mb-0 small">Pilih data pada tabel untuk menampilkan detail di sini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Data</p>
                                        <h4 class="card-title mb-0">Referensi ID Pembeli</h4>
                                    </div>
                                    <span class="badge bg-dark-subtle text-dark">{{ $refIdPembeli->count() }} Data</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive ref-table-compact">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small">Tampilkan</span>
                                                <select id="id-pembeli-page-length" class="form-select form-select-sm" style="width: auto;">
                                                    <option value="5">5</option>
                                                    <option value="8" selected>8</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                </select>
                                                <span class="text-muted small">baris</span>
                                            </div>
                                            <div class="text-muted small">Gunakan pencarian untuk mempercepat.</div>
                                        </div>
                                        <table class="table table-striped table-hover" id="table-id-pembeli">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Keterangan</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($refIdPembeli as $item)
                                                    <tr data-id="{{ $item->id }}"
                                                        data-kode='@json($item->kode)'
                                                        data-keterangan='@json($item->keterangan)'>
                                                        <td class="fw-semibold">{{ $item->kode }}</td>
                                                        <td>
                                                            {{ $item->keterangan ?? '-' }}
                                                            @if ($item->is_active === false)
                                                                <span class="badge bg-secondary-subtle text-secondary ms-1">Nonaktif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-outline-primary ref-show" data-ref="id-pembeli">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @if ($item->is_active ?? true)
                                                                <button type="button" class="btn btn-sm btn-outline-danger ref-toggle" data-ref="id-pembeli" data-active="0" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-success ref-toggle" data-ref="id-pembeli" data-active="1" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-satuan-ukur" role="tabpanel" aria-labelledby="tab-satuan-ukur"
                    data-store-url="{{ route('pnl.master-data.store.referensi', ['type' => 'satuan-ukur']) }}"
                    data-update-url="{{ route('pnl.master-data.update.referensi', ['type' => 'satuan-ukur', 'id' => '__ID__']) }}"
                    data-toggle-url="{{ route('pnl.master-data.toggle.referensi', ['type' => 'satuan-ukur', 'id' => '__ID__']) }}">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Import</p>
                                        <h4 class="card-title mb-0">Referensi Satuan Ukur</h4>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary">XLSX</span>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('pnl.master-data.import.referensi', ['type' => 'satuan-ukur']) }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        @method('post')
                                        <div class="form-group">
                                            <label for="file-satuan-ukur" class="form-label">Upload File</label>
                                            <input type="file" name="file" class="form-control" id="file-satuan-ukur" accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required @if (! $canEdit) disabled @endif>
                                            <small class="text-muted">Gunakan template resmi agar format konsisten.</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif><i class="fas fa-file-import fa-fw"></i> Import</button>
                                            <a href="{{ asset('assets/TemplateMasterRefSatuanUkur.xlsx') }}" class="btn btn-success"><i class="fas fa-download fa-fw"></i> Download Template</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm mt-4 ref-card">
                                <div class="card-header ref-card-header">
                                    <p class="text-uppercase text-muted mb-1 small">Detail</p>
                                    <h4 class="card-title mb-0">Lihat & Edit</h4>
                                </div>
                                <div class="card-body">
                                    <form id="form-satuan-ukur" method="post" action="#">
                                        @csrf
                                        <input type="hidden" name="_method" id="satuan-ukur-method" value="post">
                                        <input type="hidden" name="id" id="satuan-ukur-id">
                                        <div class="form-group">
                                            <label for="satuan-ukur-kode" class="form-label">Kode</label>
                                            <input type="text" name="kode" id="satuan-ukur-kode" class="form-control" required @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="form-group">
                                            <label for="satuan-ukur-keterangan" class="form-label">Keterangan</label>
                                            <input type="text" name="keterangan" id="satuan-ukur-keterangan" class="form-control" @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif>
                                                <i class="fas fa-save fa-fw"></i> Simpan
                                            </button>
                                            <button type="button" class="btn btn-light" data-reset="satuan-ukur">Reset</button>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        <p class="text-muted mb-0 small">Pilih data pada tabel untuk menampilkan detail di sini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Data</p>
                                        <h4 class="card-title mb-0">Referensi Satuan Ukur</h4>
                                    </div>
                                    <span class="badge bg-dark-subtle text-dark">{{ $refSatuanUkur->count() }} Data</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive ref-table-compact">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small">Tampilkan</span>
                                                <select id="satuan-ukur-page-length" class="form-select form-select-sm" style="width: auto;">
                                                    <option value="5">5</option>
                                                    <option value="8" selected>8</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                </select>
                                                <span class="text-muted small">baris</span>
                                            </div>
                                            <div class="text-muted small">Gunakan pencarian untuk mempercepat.</div>
                                        </div>
                                        <table class="table table-striped table-hover" id="table-satuan-ukur">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Keterangan</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($refSatuanUkur as $item)
                                                    <tr data-id="{{ $item->id }}"
                                                        data-kode='@json($item->kode)'
                                                        data-keterangan='@json($item->keterangan)'>
                                                        <td class="fw-semibold">{{ $item->kode }}</td>
                                                        <td>
                                                            {{ $item->keterangan ?? '-' }}
                                                            @if ($item->is_active === false)
                                                                <span class="badge bg-secondary-subtle text-secondary ms-1">Nonaktif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-outline-primary ref-show" data-ref="satuan-ukur">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @if ($item->is_active ?? true)
                                                                <button type="button" class="btn btn-sm btn-outline-danger ref-toggle" data-ref="satuan-ukur" data-active="0" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-success ref-toggle" data-ref="satuan-ukur" data-active="1" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-kode-negara" role="tabpanel" aria-labelledby="tab-kode-negara"
                    data-store-url="{{ route('pnl.master-data.store.referensi', ['type' => 'kode-negara']) }}"
                    data-update-url="{{ route('pnl.master-data.update.referensi', ['type' => 'kode-negara', 'id' => '__ID__']) }}"
                    data-toggle-url="{{ route('pnl.master-data.toggle.referensi', ['type' => 'kode-negara', 'id' => '__ID__']) }}">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Import</p>
                                        <h4 class="card-title mb-0">Referensi Kode Negara</h4>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary">XLSX</span>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('pnl.master-data.import.referensi', ['type' => 'kode-negara']) }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        @method('post')
                                        <div class="form-group">
                                            <label for="file-kode-negara" class="form-label">Upload File</label>
                                            <input type="file" name="file" class="form-control" id="file-kode-negara" accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required @if (! $canEdit) disabled @endif>
                                            <small class="text-muted">Gunakan template resmi agar format konsisten.</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif><i class="fas fa-file-import fa-fw"></i> Import</button>
                                            <a href="{{ asset('assets/TemplateMasterRefKodeNegara.xlsx') }}" class="btn btn-success"><i class="fas fa-download fa-fw"></i> Download Template</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm mt-4 ref-card">
                                <div class="card-header ref-card-header">
                                    <p class="text-uppercase text-muted mb-1 small">Detail</p>
                                    <h4 class="card-title mb-0">Lihat & Edit</h4>
                                </div>
                                <div class="card-body">
                                    <form id="form-kode-negara" method="post" action="#">
                                        @csrf
                                        <input type="hidden" name="_method" id="kode-negara-method" value="post">
                                        <input type="hidden" name="id" id="kode-negara-id">
                                        <div class="form-group">
                                            <label for="kode-negara-kode" class="form-label">Kode</label>
                                            <input type="text" name="kode" id="kode-negara-kode" class="form-control" required @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="form-group">
                                            <label for="kode-negara-keterangan" class="form-label">Keterangan</label>
                                            <input type="text" name="keterangan" id="kode-negara-keterangan" class="form-control" @if (! $canEdit) disabled @endif>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif>
                                                <i class="fas fa-save fa-fw"></i> Simpan
                                            </button>
                                            <button type="button" class="btn btn-light" data-reset="kode-negara">Reset</button>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        <p class="text-muted mb-0 small">Pilih data pada tabel untuk menampilkan detail di sini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card shadow-sm ref-card">
                                <div class="card-header ref-card-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase text-muted mb-1 small">Data</p>
                                        <h4 class="card-title mb-0">Referensi Kode Negara</h4>
                                    </div>
                                    <span class="badge bg-dark-subtle text-dark">{{ $refKodeNegara->count() }} Data</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive ref-table-compact">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small">Tampilkan</span>
                                                <select id="kode-negara-page-length" class="form-select form-select-sm" style="width: auto;">
                                                    <option value="5">5</option>
                                                    <option value="8" selected>8</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                </select>
                                                <span class="text-muted small">baris</span>
                                            </div>
                                            <div class="text-muted small">Gunakan pencarian untuk mempercepat.</div>
                                        </div>
                                        <table class="table table-striped table-hover" id="table-kode-negara">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Keterangan</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($refKodeNegara as $item)
                                                    <tr data-id="{{ $item->id }}"
                                                        data-kode='@json($item->kode)'
                                                        data-keterangan='@json($item->keterangan)'>
                                                        <td class="fw-semibold">{{ $item->kode }}</td>
                                                        <td>
                                                            {{ $item->keterangan ?? '-' }}
                                                            @if ($item->is_active === false)
                                                                <span class="badge bg-secondary-subtle text-secondary ms-1">Nonaktif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-outline-primary ref-show" data-ref="kode-negara">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @if ($item->is_active ?? true)
                                                                <button type="button" class="btn btn-sm btn-outline-danger ref-toggle" data-ref="kode-negara" data-active="0" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-success ref-toggle" data-ref="kode-negara" data-active="1" @if (! $canEdit) disabled @endif>
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <style>
        .ref-tabs .nav-link {
            border-radius: 999px;
            padding: 0.45rem 1.1rem;
            font-weight: 600;
        }
        .ref-card {
            border: 1px solid rgba(17, 24, 39, 0.08);
            border-radius: 16px;
            overflow: hidden;
        }
        .ref-card-header {
            background: linear-gradient(135deg, rgba(14, 116, 144, 0.12), rgba(37, 99, 235, 0.06));
            border-bottom: 1px solid rgba(17, 24, 39, 0.08);
        }
        .ref-card .form-label {
            font-weight: 600;
        }
        .ref-card .table thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .ref-table-compact .table td,
        .ref-table-compact .table th {
            padding: 0.35rem 0.5rem;
            vertical-align: middle;
            line-height: 1.15;
        }
        .ref-table-compact .table td {
            font-size: 0.82rem;
        }
        .ref-table-compact .table th {
            font-size: 0.74rem;
        }
        .ref-table-compact .badge {
            font-size: 0.62rem;
        }
        .ref-table-compact .btn {
            padding: 0.18rem 0.35rem;
        }
        .ref-table-compact .dataTables_info,
        .ref-table-compact .dataTables_paginate {
            font-size: 0.8rem;
            margin-top: 0.75rem;
        }
        .ref-card .btn-outline-primary,
        .ref-card .btn-outline-danger {
            border-radius: 10px;
        }
    </style>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const safeParse = (value) => {
                try {
                    const parsed = JSON.parse(value);
                    return parsed ?? '';
                } catch (error) {
                    return '';
                }
            };

            const configs = {
                tipe: {
                    paneId: 'pane-tipe',
                    tableId: 'table-tipe',
                    formId: 'form-tipe',
                    resetKey: 'tipe',
                    fields: {
                        id: 'tipe-id',
                        kode: 'tipe-kode',
                        keterangan: 'tipe-keterangan',
                    },
                },
                'kode-transaksi': {
                    paneId: 'pane-kode-transaksi',
                    tableId: 'table-kode-transaksi',
                    formId: 'form-kode-transaksi',
                    resetKey: 'kode-transaksi',
                    fields: {
                        id: 'kode-transaksi-id',
                        kode: 'kode-transaksi-kode',
                        keterangan: 'kode-transaksi-keterangan',
                    },
                },
                'keterangan-tambahan': {
                    paneId: 'pane-keterangan-tambahan',
                    tableId: 'table-keterangan-tambahan',
                    formId: 'form-keterangan-tambahan',
                    resetKey: 'keterangan-tambahan',
                    fields: {
                        id: 'keterangan-tambahan-id',
                        kode: 'keterangan-tambahan-kode',
                        kode_transaksi_id: 'keterangan-tambahan-kode-transaksi',
                        keterangan: 'keterangan-tambahan-keterangan',
                    },
                },
                'id-pembeli': {
                    paneId: 'pane-id-pembeli',
                    tableId: 'table-id-pembeli',
                    formId: 'form-id-pembeli',
                    resetKey: 'id-pembeli',
                    fields: {
                        id: 'id-pembeli-id',
                        kode: 'id-pembeli-kode',
                        keterangan: 'id-pembeli-keterangan',
                    },
                },
                'satuan-ukur': {
                    paneId: 'pane-satuan-ukur',
                    tableId: 'table-satuan-ukur',
                    formId: 'form-satuan-ukur',
                    resetKey: 'satuan-ukur',
                    fields: {
                        id: 'satuan-ukur-id',
                        kode: 'satuan-ukur-kode',
                        keterangan: 'satuan-ukur-keterangan',
                    },
                },
                'kode-negara': {
                    paneId: 'pane-kode-negara',
                    tableId: 'table-kode-negara',
                    formId: 'form-kode-negara',
                    resetKey: 'kode-negara',
                    fields: {
                        id: 'kode-negara-id',
                        kode: 'kode-negara-kode',
                        keterangan: 'kode-negara-keterangan',
                    },
                },
            };

            const bindHandlers = (configKey) => {
                const config = configs[configKey];
                if (!config) {
                    return;
                }

                const pane = document.getElementById(config.paneId);
                const table = document.getElementById(config.tableId);
                const form = document.getElementById(config.formId);
                if (!pane || !table || !form) {
                    return;
                }

                const updateUrlTemplate = pane.dataset.updateUrl || '';
                const toggleUrlTemplate = pane.dataset.toggleUrl || '';
                const storeUrl = pane.dataset.storeUrl || '';
                const methodInput = document.getElementById(`${config.resetKey}-method`);

                const setCreateMode = () => {
                    const idInput = document.getElementById(config.fields.id);
                    if (idInput) {
                        idInput.value = '';
                    }
                    if (methodInput) {
                        methodInput.value = 'post';
                    }
                    form.action = storeUrl || '#';
                };

                const fillForm = (row) => {
                    const fields = config.fields;
                    const idInput = document.getElementById(fields.id);
                    idInput.value = row.dataset.id || '';

                    if (fields.kode) {
                        const kodeInput = document.getElementById(fields.kode);
                        kodeInput.value = row.dataset.kode ? safeParse(row.dataset.kode) : '';
                    }
                    if (fields.keterangan) {
                        const ketInput = document.getElementById(fields.keterangan);
                        ketInput.value = row.dataset.keterangan ? safeParse(row.dataset.keterangan) : '';
                    }
                    if (fields.kode_transaksi_id) {
                        const kodeTransaksiInput = document.getElementById(fields.kode_transaksi_id);
                        kodeTransaksiInput.value = row.dataset.kodeTransaksiId ? safeParse(row.dataset.kodeTransaksiId) : '';
                    }

                    if (row.dataset.id && updateUrlTemplate) {
                        if (methodInput) {
                            methodInput.value = 'put';
                        }
                        form.action = updateUrlTemplate.replace('__ID__', row.dataset.id);
                    }
                };

                const setActiveRow = (row) => {
                    row.classList.add('table-active');
                    row.closest('tbody')?.querySelectorAll('tr').forEach((item) => {
                        if (item !== row) {
                            item.classList.remove('table-active');
                        }
                    });
                };

                $(table).off('click', '.ref-show');
                $(table).off('click', '.ref-toggle');

                $(table).on('click', '.ref-show', function () {
                    const row = this.closest('tr');
                    if (row) {
                        fillForm(row);
                        setActiveRow(row);
                    }
                });

                $(table).on('click', '.ref-toggle', function () {
                    const row = this.closest('tr');
                    if (!row) {
                        return;
                    }
                    const id = row.dataset.id;
                    if (!id) {
                        return;
                    }
                    if (!toggleUrlTemplate) {
                        alert('Toggle URL tidak tersedia.');
                        return;
                    }

                    const nextState = this.dataset.active === '1';
                    const label = nextState ? 'Aktifkan' : 'Nonaktifkan';
                    const tone = nextState ? 'success' : 'warning';

                    const submitToggle = () => {
                        const toggleForm = document.createElement('form');
                        toggleForm.method = 'post';
                        toggleForm.action = toggleUrlTemplate.replace('__ID__', id);

                        const token = document.createElement('input');
                        token.type = 'hidden';
                        token.name = '_token';
                        token.value = '{{ csrf_token() }}';

                        const method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'patch';

                        const isActive = document.createElement('input');
                        isActive.type = 'hidden';
                        isActive.name = 'is_active';
                        isActive.value = nextState ? '1' : '0';

                        toggleForm.appendChild(token);
                        toggleForm.appendChild(method);
                        toggleForm.appendChild(isActive);
                        document.body.appendChild(toggleForm);
                        toggleForm.submit();
                    };

                    if (typeof swal !== 'function') {
                        if (confirm(`Yakin ${label.toLowerCase()} data referensi ini?`)) {
                            submitToggle();
                        }
                        return;
                    }

                    swal({
                        title: `${label} data referensi?`,
                        text: nextState
                            ? 'Data akan digunakan kembali.'
                            : 'Data tidak akan digunakan untuk proses berikutnya.',
                        icon: tone,
                        buttons: {
                            cancel: {
                                text: 'Batal',
                                visible: true,
                                className: 'btn btn-light',
                                closeModal: true,
                            },
                            confirm: {
                                text: label,
                                value: true,
                                visible: true,
                                className: nextState ? 'btn btn-success' : 'btn btn-warning',
                                closeModal: true,
                            },
                        },
                    }).then((confirmed) => {
                        if (!confirmed) {
                            return;
                        }
                        submitToggle();
                    });
                });

                const resetButton = document.querySelector(`[data-reset="${config.resetKey}"]`);
                if (resetButton) {
                    resetButton.addEventListener('click', function () {
                        form.reset();
                        setCreateMode();
                        table.querySelectorAll('tbody tr').forEach((row) => {
                            row.classList.remove('table-active');
                        });
                    });
                }

                setCreateMode();

                const pageLengthSelect = document.getElementById(`${config.resetKey}-page-length`);
                if (window.jQuery && $.fn.DataTable) {
                    const dataTable = $(table).DataTable({
                        pageLength: parseInt(pageLengthSelect?.value || '8', 10),
                        lengthChange: false,
                        ordering: true,
                        info: true,
                        language: {
                            search: 'Cari:',
                            paginate: {
                                previous: 'Prev',
                                next: 'Next'
                            }
                        }
                    });

                    if (pageLengthSelect) {
                        pageLengthSelect.addEventListener('change', function () {
                            dataTable.page.len(parseInt(this.value, 10)).draw();
                        });
                    }
                }
            };

            Object.keys(configs).forEach((key) => bindHandlers(key));
        });
    </script>
@endsection
