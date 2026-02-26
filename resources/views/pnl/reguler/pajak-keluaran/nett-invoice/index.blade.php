@extends('layouts.master')

@section('title', 'PNL - Nett Invoice - Reguler')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
    <style>
        .tbl-container {
            overflow-x: auto;
            width: 100%;
        }

        table.dataTable thead th {
            white-space: nowrap;
        }

        div.dataTables_processing {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        .btn-pilih-invoice {
            display: none;
        }

        .selected-count-badge {
            font-size: 0.85rem;
        }

        /* ── Modal styling ── */
        #modal-npkp .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 12px 48px rgba(0, 0, 0, .18);
            overflow: hidden;
        }

        #modal-npkp .modal-header {
            background: linear-gradient(135deg, #1a73e8, #1557b0);
            color: #fff;
            border-bottom: none;
            padding: 18px 24px;
        }

        #modal-npkp .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        #modal-npkp .modal-body {
            max-height: 70vh;
            overflow-y: auto;
            padding: 20px 24px;
        }

        #modal-npkp .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 14px 24px;
        }

        .retur-summary-card {
            background: linear-gradient(135deg, #f0f7ff, #e8f0fe);
            border: 1px solid #d2e3fc;
            border-radius: 10px;
            padding: 0;
            overflow: hidden;
        }

        .retur-summary-card .card-header {
            background: rgba(26, 115, 232, .08);
            padding: 10px 16px;
            border-bottom: 1px solid #d2e3fc;
        }

        .retur-summary-card .card-header h6 {
            color: #1a73e8;
            font-weight: 600;
        }

        .retur-summary-card .card-body {
            padding: 12px 16px;
        }

        .retur-summary-table th {
            font-size: .8rem;
            text-transform: uppercase;
            color: #5f6368;
            background-color: transparent;
            letter-spacing: .03em;
        }

        .retur-summary-table td {
            font-size: .85rem;
        }

        .total-row td {
            font-size: .95rem;
            color: #1a73e8;
        }

        /* Green highlight for matching customers */
        .npkp-match-row {
            background-color: #e6f4ea !important;
        }

        .npkp-match-row:hover {
            background-color: #ceead6 !important;
        }

        .npkp-match-badge {
            font-size: .7rem;
            vertical-align: middle;
            margin-left: 4px;
        }

        .npkp-filter-bar {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 16px;
        }

        .npkp-section-title {
            font-size: .9rem;
            font-weight: 600;
            color: #3c4043;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .npkp-section-title i {
            color: #1a73e8;
        }

        /* Netting preview panel */
        .netting-preview {
            background: linear-gradient(135deg, #fff8e1, #fff3cd);
            border: 1px solid #ffd54f;
            border-radius: 10px;
            padding: 16px;
            margin-top: 16px;
            display: none;
        }

        .netting-preview.active {
            display: block;
            animation: fadeSlideIn .3s ease;
        }

        .netting-preview h6 {
            color: #e65100;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .netting-detail-table th {
            font-size: .78rem;
            text-transform: uppercase;
            color: #5f6368;
            white-space: nowrap;
        }

        .netting-detail-table td {
            font-size: .85rem;
        }

        .nett-result-positive {
            color: #1e8e3e;
            font-weight: 700;
        }

        .nett-result-negative {
            color: #d93025;
            font-weight: 700;
        }

        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Empty state */
        .npkp-empty-state {
            text-align: center;
            padding: 32px 16px;
            color: #80868b;
        }

        .npkp-empty-state i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #dadce0;
        }

        /* Highlight for dates with data */
        .daterangepicker td.has-data {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
            border-radius: 4px;
        }

        .daterangepicker td.has-data:hover {
            background-color: #c3e6cb;
        }

        .daterangepicker td.active.has-data {
            background-color: #357ebd;
            color: #fff;
        }

        /* Compact table mode for Retur + History */
        .compact-table-card .card-header {
            padding: .55rem .85rem;
        }

        .compact-table-card .card-title {
            font-size: .98rem;
            margin-bottom: 0;
        }

        .compact-table-card .card-body {
            padding: .6rem .75rem;
        }

        .compact-table {
            font-size: .82rem;
            margin-bottom: 0;
        }

        .compact-table thead th {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .02em;
            padding: .35rem .45rem;
            line-height: 1.15;
            vertical-align: middle;
        }

        .compact-table tbody td {
            padding: .3rem .45rem;
            line-height: 1.2;
            vertical-align: middle;
        }

        .compact-table .btn.btn-sm {
            font-size: .72rem;
            padding: .2rem .4rem;
            line-height: 1.2;
        }

        .compact-table input[type='checkbox'] {
            transform: scale(.9);
        }

        #table-nett-invoice_wrapper .dataTables_length,
        #table-nett-invoice_wrapper .dataTables_filter,
        #table-nett-history_wrapper .dataTables_length,
        #table-nett-history_wrapper .dataTables_filter {
            margin-bottom: .35rem;
            font-size: .75rem;
        }

        #table-nett-invoice_wrapper .dataTables_filter input,
        #table-nett-history_wrapper .dataTables_filter input {
            min-height: 32px;
            padding: .2rem .45rem;
            font-size: .75rem;
        }

        #table-nett-invoice_wrapper .dataTables_length select,
        #table-nett-history_wrapper .dataTables_length select {
            min-height: 32px;
            padding: .1rem 1.4rem .1rem .35rem;
            font-size: .75rem;
        }

        #table-nett-invoice_wrapper .dataTables_info,
        #table-nett-history_wrapper .dataTables_info,
        #table-nett-invoice_wrapper .dataTables_paginate,
        #table-nett-history_wrapper .dataTables_paginate {
            margin-top: .4rem;
            font-size: .75rem;
        }

        #table-nett-invoice_wrapper .pagination,
        #table-nett-history_wrapper .pagination {
            margin-bottom: 0;
        }

        #table-nett-invoice_wrapper .page-link,
        #table-nett-history_wrapper .page-link {
            padding: .2rem .45rem;
            font-size: .72rem;
        }

        @media (max-width: 992px) {
            .compact-table {
                font-size: .85rem;
            }

            .compact-table thead th,
            .compact-table tbody td {
                padding: .45rem .5rem;
            }

            #table-nett-invoice_wrapper .dataTables_filter input,
            #table-nett-history_wrapper .dataTables_filter input,
            #table-nett-invoice_wrapper .dataTables_length select,
            #table-nett-history_wrapper .dataTables_length select {
                min-height: 34px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Nett Invoice Non-PKP</h3>
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
                        <a href="#">Reguler</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Nett Invoice</a>
                    </li>
                </ul>
            </div>

            {{-- Filter Section --}}
            <div class="row mb-3">
                <div class="col-md-3 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_pt">PT <div class="spinner-border spinner-border-sm" id="sp-filter-pt"
                                role="status"><span class="visually-hidden">Loading...</span></div></label>
                        <select class="form-select" id="filter_pt" multiple="multiple">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_brand">BRAND <div class="spinner-border spinner-border-sm" id="sp-filter-brand"
                                role="status"><span class="visually-hidden">Loading...</span></div></label>
                        <select class="form-select" id="filter_brand" multiple="multiple">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_depo">DEPO <div class="spinner-border spinner-border-sm" id="sp-filter-depo"
                                role="status"><span class="visually-hidden">Loading...</span></div></label>
                        <select class="form-select" id="filter_depo" multiple="multiple">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_periode">PERIODE</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="filter_periode" id="filter_periode"
                                placeholder="Pilih Periode" aria-label="Pilih Periode" value="{{ date('d/m/Y') }}" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12 d-flex align-items-center gap-2">
                    <button class="btn btn-primary" id="btn-apply-filter">
                        <i class="fas fa-check"></i> Apply Filter
                    </button>
                    <button class="btn btn-warning btn-pilih-invoice" id="btn-pilih-invoice">
                        <i class="fas fa-exchange-alt"></i> Pilih Invoice Non-PKP
                        <span class="badge bg-light text-dark selected-count-badge ms-1" id="selected-count">0</span>
                    </button>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="row mb-3">
                <div class="col-md-12">
                    <button class="btn btn-success btn-sm" id="btn-export-xlsx">
                        <i class="fas fa-file-excel"></i> Export XLSX
                    </button>
                    <button class="btn btn-info btn-sm" id="btn-export-csv">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                </div>
            </div>

            {{-- Retur Invoice Table --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card compact-table-card">
                        <div class="card-header">
                            <h4 class="card-title">Daftar Invoice Retur</h4>
                        </div>
                        <div class="card-body">
                            <div class="tbl-container">
                                <table class="table table-sm table-bordered table-hover compact-table" id="table-nett-invoice">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-retur"></th>
                                            <th>Kode Pelanggan</th>
                                            <th>Nama Pelanggan</th>
                                            <th>No Invoice Retur</th>
                                            <th>Tanggal</th>
                                            <th>NILAI AWAL RETUR</th>
                                            <th>NILAI SISA RETUR</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Nett History Table --}}
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card compact-table-card">
                        <div class="card-header">
                            <h4 class="card-title">Histori Proses Nett</h4>
                        </div>
                        <div class="card-body">
                            <div class="tbl-container">
                                <table class="table table-sm table-bordered table-hover compact-table" id="table-nett-history">
                                    <thead>
                                        <tr>
                                            <th>ID Transaksi</th>
                                            <th>No Invoice Non-PKP</th>
                                            <th>No Invoice Retur</th>
                                            <th>Nilai Invoice Non-PKP</th>
                                            <th>Nilai Retur Digunakan</th>
                                            <th>Nilai Nett</th>
                                            <th>Sisa Retur</th>
                                            <th>Status</th>
                                            <th>Dibuat Oleh</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Invoice --}}
    <div class="modal fade" id="modal-detail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailLabel">Detail Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Kode Produk</th>
                                <th>Qty (PCS)</th>
                                <th>DPP</th>
                                <th>PPN</th>
                                <th>Disc</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items-tbody">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pilih Invoice Non-PKP --}}
    <div class="modal fade" id="modal-npkp" tabindex="-1" aria-labelledby="modalNpkpLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="modalNpkpLabel">
                            <i class="fas fa-exchange-alt me-2"></i>Netting Invoice
                        </h5>
                        <small class="text-white-50">Pilih invoice Non-PKP untuk proses netting</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Selected Retur Summary --}}
                    <div class="retur-summary-card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-receipt me-1"></i> Invoice Retur yang Dipilih</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless retur-summary-table mb-0">
                                <thead>
                                    <tr>
                                        <th>No Invoice Retur</th>
                                        <th>Kode Pelanggan</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Tanggal</th>
                                        <th class="text-end">Nilai Sisa Retur</th>
                                    </tr>
                                </thead>
                                <tbody id="selected-retur-summary">
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="4" class="text-end fw-bold border-top">Total Nilai Sisa Retur:</td>
                                        <td id="total-retur-value" class="text-end fw-bold border-top">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Non-PKP Filter (periode dari modal, PT/Brand/Depo dari halaman utama) --}}
                    <div class="npkp-filter-bar">
                        <div class="row align-items-end">
                            <div class="col-md-5">
                                <label for="modal_filter_periode" class="form-label mb-1"
                                    style="font-size:.85rem;color:#5f6368;">
                                    <i class="fas fa-calendar-alt me-1"></i>PERIODE
                                </label>
                                <input type="text" class="form-control form-control-sm" id="modal_filter_periode"
                                    placeholder="Pilih Periode" value="{{ date('d/m/Y') }}" />
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary btn-sm" id="btn-filter-npkp">
                                    <i class="fas fa-search me-1"></i> Tampilkan Invoice Non-PKP
                                </button>
                            </div>
                            <div class="col-md-3 text-end d-flex align-items-end justify-content-end">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    PT, Brand, dan Depo mengikuti filter halaman utama
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Non-PKP Invoice List --}}
                    <div class="npkp-section-title">
                        <i class="fas fa-file-invoice"></i> Daftar Invoice Non-PKP
                    </div>

                    <div id="npkp-loading" class="text-center py-4" style="display:none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat invoice Non-PKP...</p>
                    </div>

                    <div id="npkp-empty" class="npkp-empty-state">
                        <i class="fas fa-inbox d-block"></i>
                        <p class="mb-0">Klik <strong>"Tampilkan Invoice Non-PKP"</strong> untuk memuat daftar</p>
                    </div>

                    <div id="npkp-table-wrapper" style="display:none;">
                        <div class="input-group input-group-sm mb-2" style="max-width:360px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="npkp-search"
                                placeholder="Cari kode pelanggan, nama, atau no invoice...">
                        </div>
                        <div class="tbl-container" style="max-height:320px; overflow-y:auto;">
                            <table class="table table-sm table-bordered table-hover mb-0" id="table-npkp">
                                <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                                    <tr>
                                        <th style="width:40px;">Pilih</th>
                                        <th>Kode Pelanggan</th>
                                        <th>Nama Pelanggan</th>
                                        <th>No Invoice</th>
                                        <th>Tanggal</th>
                                        <th class="text-end">Nilai Invoice</th>
                                    </tr>
                                </thead>
                                <tbody id="npkp-tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Netting Preview --}}
                    <div class="netting-preview" id="netting-preview">
                        <h6><i class="fas fa-calculator me-1"></i> Simulasi Netting</h6>
                        <div class="tbl-container">
                            <table class="table table-sm table-bordered netting-detail-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No Invoice Non-PKP</th>
                                        <th class="text-end">Nilai Asli</th>
                                        <th class="text-end">Total Retur Digunakan</th>
                                        <th class="text-end">Nilai Setelah Nett</th>
                                    </tr>
                                </thead>
                                <tbody id="netting-preview-tbody">
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td>Total</td>
                                        <td class="text-end" id="preview-total-original">Rp 0</td>
                                        <td class="text-end" id="preview-total-retur-used">Rp 0</td>
                                        <td class="text-end" id="preview-total-nett">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Sisa Retur Tidak Terpakai:</td>
                                        <td class="text-end fw-bold" id="preview-retur-remaining">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-process-nett" disabled>
                        <i class="fas fa-cog me-1"></i> Proses Nett
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('pnl.reguler.pajak-keluaran.nett-invoice.script')
@endsection
