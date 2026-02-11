@extends('layouts.master')

@section('title', 'Pajak Keluaran (DB) - Reguler | PNL')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
    <style>
        /* ===== COMPACT TABLE CORE ===== */
        table.dataTable {
            font-size: 0.78rem !important;
            border-collapse: collapse !important;
        }

        table.dataTable thead th,
        table.dataTable tbody td {
            padding: 4px 6px !important;
            vertical-align: middle !important;
            white-space: nowrap;
        }

        table.dataTable thead th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background: linear-gradient(180deg, #f8f9fb 0%, #eef1f5 100%);
            border-bottom: 2px solid #c8cfd8 !important;
            color: #3a4a5c;
            font-weight: 700;
        }

        table.dataTable tbody tr {
            transition: background-color 0.15s ease;
        }

        table.dataTable tbody tr:hover {
            background-color: #edf2ff !important;
        }

        table.dataTable tbody tr.even {
            background-color: #fafbfc;
        }

        /* ===== FILTER INPUTS ===== */
        thead input.column-filter,
        thead input.form-control-sm {
            width: 100%;
            padding: 2px 4px !important;
            box-sizing: border-box;
            font-size: 0.72rem !important;
            height: 24px !important;
            border: 1px solid #d1d9e0;
            border-radius: 3px;
            background-color: #fff;
        }

        thead input.form-control-sm:focus {
            border-color: #1572e8;
            box-shadow: 0 0 0 2px rgba(21, 114, 232, 0.12);
            outline: none;
        }

        thead input.form-control-sm:disabled {
            background-color: #f0f2f5;
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* ===== PROCESSING OVERLAY ===== */
        div.dataTables_processing {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%);
            z-index: 9999;
            border: none;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        div.dataTables_processing>div {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: #495057;
        }

        .dataTables_processing i {
            color: #1572e8;
        }

        /* ===== TABLE CONTAINER ===== */
        .tbl-container {
            overflow-x: auto;
            width: 100%;
            border: 1px solid #e0e4e8;
            border-radius: 6px;
        }

        /* ===== PAGINATION & INFO ===== */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 3px 8px !important;
            font-size: 0.75rem !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            font-size: 0.78rem;
        }

        .dataTables_wrapper .dataTables_length select {
            padding: 2px 4px;
            font-size: 0.78rem;
        }

        /* ===== DATERANGEPICKER ===== */
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

        /* ===== EXPAND/COLLAPSE CONTROL ===== */
        td.dt-control {
            text-align: center;
            cursor: pointer;
            color: #1572e8;
            font-size: 13px;
            padding: 4px !important;
            width: 28px !important;
        }

        td.dt-control:hover {
            color: #0d5bb5;
        }

        tr.shown td.dt-control i:before {
            content: "\f056";
        }

        tr.shown {
            background-color: #e8f0fe !important;
        }

        tr.shown+tr>td {
            padding: 0 !important;
            border-left: 3px solid #1572e8;
        }

        /* ===== CHILD TABLE (PRODUCT DETAILS) ===== */
        table.child-details {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 0.75rem;
        }

        table.child-details thead {
            background: linear-gradient(180deg, #e8ecf1 0%, #dce2e9 100%);
        }

        table.child-details th {
            padding: 3px 6px;
            border: 1px solid #d1d5db;
            font-weight: 700;
            color: #4a5568;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table.child-details td {
            padding: 3px 6px;
            border: 1px solid #e5e7eb;
            color: #374151;
        }

        table.child-details tbody tr:nth-child(even) {
            background-color: #f7f8fa;
        }

        table.child-details tbody tr:hover {
            background-color: #e5edfa;
        }

        /* ===== CHECKBOX ===== */
        .row-checkbox-pkp,
        .row-checkbox-pkpnppn,
        .row-checkbox-npkp,
        .row-checkbox-npkpnppn,
        .row-checkbox-retur,
        #select-all-pkp,
        #select-all-pkpnppn,
        #select-all-npkp,
        #select-all-npkpnppn,
        #select-all-retur {
            width: 14px;
            height: 14px;
            cursor: pointer;
            accent-color: #1572e8;
        }

        /* ===== TABS ===== */
        .nav-tabs .nav-link {
            font-size: 0.8rem;
            padding: 6px 14px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            div.dataTables_processing {
                padding: 0.5rem 1rem;
                border-radius: 4px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Pajak Keluaran (DB)</h3>
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
                        <a href="#">Pajak Keluaran</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_pt">PT <div class="spinner-border spinner-border-sm " id="sp-filter-pt"
                                role="status"><span class="visually-hidden">Loading...</span></div></label>
                        <select class="form-select" id="filter_pt" multiple="multiple">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_brand">BRAND <div class="spinner-border spinner-border-sm " id="sp-filter-brand"
                                role="status"><span class="visually-hidden">Loading...</span></div></label>
                        <select class="form-select" id="filter_brand" multiple="multiple">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_depo">DEPO <div class="spinner-border spinner-border-sm " id="sp-filter-depo"
                                role="status"><span class="visually-hidden">Loading...</span></div></label>
                        <select class="form-select" id="filter_depo" multiple="multiple">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_periode">PERIODE</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="filter_periode" id="filter_periode"
                                placeholder="Pilih Periode" aria-label="Pilih Periode" value="{{ date('d/m/Y') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_chstatus">Status</label>
                        <select class="form-select" id="filter_chstatus">
                            <option value="all">--ALL--</option>
                            <option value="checked-ready2download">Checked</option>
                            <option value="checked-downloaded">Checked & Downloaded</option>
                            <option value="unchecked">Unchecked</option>
                        </select>
                    </div>
                </div>
                <div class="col-xm-12 col-sm-12 col-md-9 col-lg-9 mb-3">
                    <div class="input-group">
                        <label class="input-group-text" for="inputGroupFilter">Terapkan filter ke:</label>
                        <select class="form-select" id="inputGroupFilter" aria-label="Terapkan filter ke:">
                            <option value='all'>--ALL--</option>
                            <option value="pkp">PKP</option>
                            <option value="pkpnppn">PKP (Non-PPN)</option>
                            <option value="npkp">Non-PKP</option>
                            <option value="npkpnppn">Non-PKP (Non-PPN)</option>
                            <option value="retur">Retur</option>
                        </select>
                        <button class="btn btn-outline-primary" id="btn-apply-filter"><i class="fas fa-check"></i>
                            Filter</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="simple-tab-0" data-bs-toggle="tab" href="#tabpanel-pkp"
                                    role="tab" aria-controls="tabpanel-pkp" aria-selected="true">PKP</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-1" data-bs-toggle="tab" href="#tabpanel-pkpnppn"
                                    role="tab" aria-controls="tabpanel-pkpnppn" aria-selected="true">PKP
                                    (Non-PPN)</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-2" data-bs-toggle="tab" href="#tabpanel-nonpkp"
                                    role="tab" aria-controls="tabpanel-nonpkp" aria-selected="false">Non-PKP</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-3" data-bs-toggle="tab" href="#tabpanel-nonpkpnppn"
                                    role="tab" aria-controls="tabpanel-nonpkpnppn" aria-selected="false">Non-PKP
                                    (Non-PPN)</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-4" data-bs-toggle="tab" href="#tabpanel-retur"
                                    role="tab" aria-controls="tabpanel-retur" aria-selected="false">Retur</a>
                            </li>
                        </ul>
                        <div class="tab-content pt-3" id="tab-content">
                            <div class="tab-pane active" id="tabpanel-pkp" role="tabpanel"
                                aria-labelledby="tabpanel-pkp">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-pkp"></i>
                                                <div class="spinner-border spinner-border-sm spinner-counter-pkp"
                                                    role="status" style="display: none;"><span
                                                        class="visually-hidden">Loading...</span></div> Terdapat:
                                            </td>
                                            <td id="total_ready2download_pkp">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                                                    <button class="btn btn-sm btn-primary ms-3 btn-download"
                                                        id="btn-download-pkp" onclick="downloadCheckedData('pkp')" hidden>
                                                        <i class="fas fa-download"></i>
                                                        <div class="spinner-border spinner-border-sm " id="sp-pkp"
                                                            role="status" hidden><span
                                                                class="visually-hidden">Loading...</span></div> Download
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_pkp">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="tbl-container">
                                    <table class="table table-sm table-bordered table-hover table-fixed" id="table-pkp">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th><input type="checkbox" id="select-all-pkp"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>TGL FAKTUR PAJAK</th>
                                                <th>TOTAL HARGA</th>
                                                <th>TOTAL DISC</th>
                                                <th>TOTAL DPP</th>
                                                <th>TOTAL DPP LAIN</th>
                                                <th>TOTAL PPN</th>
                                                <th>ALAMAT</th>
                                                <th>TYPE PAJAK</th>
                                                <th>NAMA SESUAI NPWP</th>
                                                <th>ALAMAT NPWP</th>
                                                <th>NO TELEPON</th>
                                                <th>NO FP</th>
                                                <th>BRAND</th>
                                                <th>DEPO</th>
                                                <th>AREA</th>
                                                <th>TYPE JUAL</th>
                                                <th>KODE JENIS FP</th>
                                                <th>STATUS FP</th>
                                                <th>ID TKU PEMBELI</th>
                                                <th>JENIS</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th></th>
                                                <!-- Checkbox -->
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Customer ID" data-column="2"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="NIK" data-column="3"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Nama Customer" data-column="4"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="NPWP Customer" data-column="5"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No DO" data-column="6"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No Invoice" data-column="7"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Tgl Faktur Pajak" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Total Harga" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Total Disc" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Total DPP" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Total DPP Lain" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Total PPN" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Alamat" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Tipe Pajak" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Nama Sesuai NPWP" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Alamat NPWP" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No Telepon" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No FP" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Brand" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Depo" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Area" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Type Jual" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Kode Jenis FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Status FP" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="ID TKU Pembeli" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Jenis" data-column="27"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabpanel-pkpnppn" role="tabpanel"
                                aria-labelledby="tabpanel-pkpnppn">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-pkpnppn"></i>
                                                <div class="spinner-border spinner-border-sm spinner-counter-pkpnppn"
                                                    role="status" style="display: none;"><span
                                                        class="visually-hidden">Loading...</span></div> Terdapat:
                                            </td>
                                            <td id="total_ready2download_pkpnppn">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                                                    <button class="btn btn-sm btn-primary ms-3 btn-download"
                                                        id="btn-download-pkpnppn" onclick="downloadCheckedData('pkpnppn')"
                                                        hidden>
                                                        <i class="fas fa-download"></i>
                                                        <div class="spinner-border spinner-border-sm " id="sp-pkpnppn"
                                                            role="status" hidden><span
                                                                class="visually-hidden">Loading...</span></div> Download
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_pkpnppn">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover"
                                        id="table-pkpnppn">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th><input type="checkbox" id="select-all-pkpnppn"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>TGL FAKTUR PAJAK</th>
                                                <th>TOTAL HARGA</th>
                                                <th>TOTAL DISC</th>
                                                <th>TOTAL DPP</th>
                                                <th>TOTAL DPP LAIN</th>
                                                <th>TOTAL PPN</th>
                                                <th>ALAMAT</th>
                                                <th>TYPE PAJAK</th>
                                                <th>NAMA SESUAI NPWP</th>
                                                <th>ALAMAT NPWP</th>
                                                <th>NO TELEPON</th>
                                                <th>NO FP</th>
                                                <th>BRAND</th>
                                                <th>DEPO</th>
                                                <th>AREA</th>
                                                <th>TYPE JUAL</th>
                                                <th>KODE JENIS FP</th>
                                                <th>STATUS FP</th>
                                                <th>ID TKU PEMBELI</th>
                                                <th>JENIS</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th></th>
                                                <!-- Checkbox -->
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Customer ID" data-column="2"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="NIK" data-column="3"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Nama Customer" data-column="4"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="NPWP Customer" data-column="5"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="No DO" data-column="6"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="No Invoice" data-column="7"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Tgl Faktur Pajak" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Total Harga" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Total Disc" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Total DPP" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Total DPP Lain" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Total PPN" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Alamat" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Tipe Pajak" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Nama Sesuai NPWP" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Alamat NPWP" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="No Telepon" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="No FP" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Brand" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Depo" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Area" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Type Jual" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Kode Jenis FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Status FP" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="ID TKU Pembeli" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Jenis" data-column="27"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabpanel-nonpkp" role="tabpanel"
                                aria-labelledby="tabpanel-nonpkp">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-npkp"></i>
                                                <div class="spinner-border spinner-border-sm spinner-counter-npkp"
                                                    role="status" style="display: none;"><span
                                                        class="visually-hidden">Loading...</span></div> Terdapat:
                                            </td>
                                            <td id="total_ready2download_npkp">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                                                    <button class="btn btn-sm btn-primary ms-3 btn-download"
                                                        id="btn-download-npkp" onclick="downloadCheckedData('npkp')"
                                                        hidden>
                                                        <i class="fas fa-download"></i>
                                                        <div class="spinner-border spinner-border-sm " id="sp-npkp"
                                                            role="status" hidden><span
                                                                class="visually-hidden">Loading...</span></div> Download
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_npkp">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover"
                                        id="table-npkp">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th><input type="checkbox" id="select-all-npkp"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>TGL FAKTUR PAJAK</th>
                                                <th>TOTAL HARGA</th>
                                                <th>TOTAL DISC</th>
                                                <th>TOTAL DPP</th>
                                                <th>TOTAL DPP LAIN</th>
                                                <th>TOTAL PPN</th>
                                                <th>NETT DPP+PPN</th>
                                                <th>ALAMAT</th>
                                                <th>TYPE PAJAK</th>
                                                <th>NAMA SESUAI NPWP</th>
                                                <th>ALAMAT NPWP</th>
                                                <th>NO TELEPON</th>
                                                <th>NO FP</th>
                                                <th>DEPO</th>
                                                <th>AREA</th>
                                                <th>BRAND</th>
                                                <th>TYPE JUAL</th>
                                                <th>KODE JENIS FP</th>
                                                <th>STATUS FP</th>
                                                <th>ID TKU PEMBELI</th>
                                                <th>JENIS</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th></th>
                                                <!-- Checkbox -->
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Customer ID" data-column="2"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="NIK" data-column="3"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Nama Customer" data-column="4"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="NPWP Customer" data-column="5"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="No DO" data-column="6"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="No Invoice" data-column="7"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Tgl Faktur Pajak" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Total Harga" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Total Disc" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Total DPP" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Total DPP Lain" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Total PPN" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Nett DPP+PPN" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Alamat" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Tipe Pajak" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Nama Sesuai NPWP" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Alamat NPWP" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="No Telepon" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="No FP" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Depo" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Area" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Brand" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Type Jual" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Kode Jenis FP" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Status FP" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="ID TKU Pembeli" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Jenis" data-column="28"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabpanel-nonpkpnppn" role="tabpanel"
                                aria-labelledby="tabpanel-nonpkpnppn">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-npkpnppn"></i>
                                                <div class="spinner-border spinner-border-sm spinner-counter-npkpnppn"
                                                    role="status" style="display: none;"><span
                                                        class="visually-hidden">Loading...</span></div> Terdapat:
                                            </td>
                                            <td id="total_ready2download_npkpnppn">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                                                    <button class="btn btn-sm btn-primary ms-3 btn-download"
                                                        id="btn-download-npkpnppn"
                                                        onclick="downloadCheckedData('npkpnppn')" hidden>
                                                        <i class="fas fa-download"></i>
                                                        <div class="spinner-border spinner-border-sm " id="sp-npkpnppn"
                                                            role="status" hidden><span
                                                                class="visually-hidden">Loading...</span></div> Download
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_npkpnppn">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover"
                                        id="table-npkpnppn">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th><input type="checkbox" id="select-all-npkpnppn"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>TGL FAKTUR PAJAK</th>
                                                <th>TOTAL HARGA</th>
                                                <th>TOTAL DISC</th>
                                                <th>TOTAL DPP</th>
                                                <th>TOTAL DPP LAIN</th>
                                                <th>TOTAL PPN</th>
                                                <th>ALAMAT</th>
                                                <th>TYPE PAJAK</th>
                                                <th>NAMA SESUAI NPWP</th>
                                                <th>ALAMAT NPWP</th>
                                                <th>NO TELEPON</th>
                                                <th>NO FP</th>
                                                <th>DEPO</th>
                                                <th>AREA</th>
                                                <th>BRAND</th>
                                                <th>TYPE JUAL</th>
                                                <th>KODE JENIS FP</th>
                                                <th>STATUS FP</th>
                                                <th>ID TKU PEMBELI</th>
                                                <th>JENIS</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th></th>
                                                <!-- Checkbox -->
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Customer ID" data-column="2"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="NIK" data-column="3"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Nama Customer" data-column="4"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="NPWP Customer" data-column="5"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="No DO" data-column="6"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="No Invoice" data-column="7"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Tgl Faktur Pajak" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Total Harga" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Total Disc" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Total DPP" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Total DPP Lain" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Total PPN" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Alamat" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Tipe Pajak" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Nama Sesuai NPWP" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Alamat NPWP" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="No Telepon" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="No FP" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Depo" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Area" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Brand" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Type Jual" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Kode Jenis FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Status FP" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="ID TKU Pembeli" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Jenis" data-column="27"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabpanel-retur" role="tabpanel" aria-labelledby="tabpanel-retur">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-retur"></i>
                                                <div class="spinner-border spinner-border-sm spinner-counter-retur"
                                                    role="status" style="display: none;"><span
                                                        class="visually-hidden">Loading...</span></div> Terdapat:
                                            </td>
                                            <td id="total_ready2download_retur">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                                                    <button class="btn btn-sm btn-primary ms-3 btn-download"
                                                        id="btn-download-retur" onclick="downloadCheckedData('retur')"
                                                        hidden>
                                                        <i class="fas fa-download"></i>
                                                        <div class="spinner-border spinner-border-sm" id="sp-retur"
                                                            role="status" hidden><span
                                                                class="visually-hidden">Loading...</span></div> Download
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_retur">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover"
                                        id="table-retur">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th><input type="checkbox" id="select-all-retur"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>TGL FAKTUR PAJAK</th>
                                                <th>TOTAL HARGA</th>
                                                <th>TOTAL DISC</th>
                                                <th>TOTAL DPP</th>
                                                <th>TOTAL DPP LAIN</th>
                                                <th>TOTAL PPN</th>
                                                <th>ALAMAT</th>
                                                <th>TYPE PAJAK</th>
                                                <th>NAMA SESUAI NPWP</th>
                                                <th>ALAMAT NPWP</th>
                                                <th>NO TELEPON</th>
                                                <th>NO FP</th>
                                                <th>DEPO</th>
                                                <th>AREA</th>
                                                <th>BRAND</th>
                                                <th>TYPE JUAL</th>
                                                <th>KODE JENIS FP</th>
                                                <th>STATUS FP</th>
                                                <th>ID TKU PEMBELI</th>
                                                <th>JENIS</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <!-- Expand/collapse -->
                                                <th></th>
                                                <!-- Checkbox -->
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Customer ID" data-column="2"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="NIK" data-column="3"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Nama Customer" data-column="4"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="NPWP Customer" data-column="5"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="No DO" data-column="6"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="No Invoice" data-column="7"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Tgl Faktur Pajak" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Total Harga" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Total Disc" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Total DPP" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Total DPP Lain" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Total PPN" disabled></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Alamat" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Tipe Pajak" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Nama Sesuai NPWP" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Alamat NPWP" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="No Telepon" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="No FP" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Depo" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Area" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Brand" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Type Jual" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Kode Jenis FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Status FP" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="ID TKU Pembeli" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Jenis" data-column="27"></th>
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
            <div class="row mt-2" id="page-summary-section">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Summary Selection</div>
                        </div>
                        <div class="card-body" id="summary-content">
                            <div class="alert alert-info">Belum ada data yang dipilih.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $dtDataRoute = route('pnl.reguler.pajak-keluaran-db.dtdata');
@endphp
@include('pnl.reguler.pajak-keluaran.script-db')
