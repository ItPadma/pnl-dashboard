@extends('layouts.master')

@section('title', 'Nett Invoice - Reguler | PNL')

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
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Nett Invoice</h3>
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
                <div class="col-md-3 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_tipe">TIPE</label>
                        <select class="form-select" id="filter_tipe">
                            <option value="all" selected>--ALL--</option>
                            <option value="pkp">PKP</option>
                            <option value="pkpnppn">PKP (Non-PPN)</option>
                            <option value="npkp">Non-PKP</option>
                            <option value="npkpnppn">Non-PKP (Non-PPN)</option>
                            <option value="retur">Retur</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-sm-12">
                    <button class="btn btn-primary mt-3" id="btn-apply-filter">
                        <i class="fas fa-check"></i> Apply Filter
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

            {{-- Results Table --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="tbl-container">
                                <table class="table table-sm table-bordered table-hover" id="table-nett-invoice">
                                    <thead>
                                        <tr>
                                            <th>Kode Pelanggan</th>
                                            <th>Nama Pelanggan</th>
                                            <th>No Invoice</th>
                                            <th>Nilai Invoice</th>
                                            <th>Nett Invoice</th>
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

    {{-- Modal Pilih Retur --}}
    <div class="modal fade" id="modal-retur" tabindex="-1" aria-labelledby="modalReturLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReturLabel">Pilih Invoice Retur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Invoice: <strong id="selected-invoice"></strong></p>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all-retur"></th>
                                <th>Kode Pelanggan</th>
                                <th>Nama Pelanggan</th>
                                <th>No Invoice Retur</th>
                                <th>Nilai Retur</th>
                            </tr>
                        </thead>
                        <tbody id="retur-tbody">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-process-nett">Proses Nett</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('pnl.reguler.pajak-keluaran.nett-invoice.script')
@endsection
