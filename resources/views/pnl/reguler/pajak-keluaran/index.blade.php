@extends('layouts.master')

@section('title', 'Pajak Keluaran - Reguler | PNL')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
    <style>
        thead input.column-filter {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
            font-size: 0.8rem;
        }

        div.dataTables_processing {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%);
            z-index: 9999;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 1rem rgba(0, 0, 0, 0.2);
            display: flex;
            /* Make it a flex container */
            align-items: center;
            /* Center vertically */
            justify-content: center;
            /* Center horizontally */
        }

        div.dataTables_processing>div {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .dataTables_processing i {
            color: #1572e8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            div.dataTables_processing {
                padding: 0.75rem;
                border-radius: 0.25rem;
            }

            div.dataTables_processing>div {
                gap: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            div.dataTables_processing {
                padding: 0.5rem;
                border-radius: 0.25rem;
            }

            div.dataTables_processing>div {
                gap: 0.5rem;
            }
        }

        table.dataTable thead th {
            white-space: nowrap;
        }

        /* .tbl-container scroll horizontal dan vertical */
        .tbl-container {
            overflow-x: auto;
            /* overflow-y: auto; */
            width: 100%;
            /* max-height: 600px; */
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
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Pajak Keluaran</h3>
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
                                placeholder="Pilih Periode" aria-label="Pilih Periode"
                                value="{{ date('d/m/Y') }} - {{ date('d/m/Y') }}" />
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
                            <option value="nonstandar">Non Standar</option>
                            <option value="pembatalan">Pembatalan</option>
                            <option value="koreksi">Koreksi</option>
                            <option value="pending">Pending</option>
                        </select>
                        <button class="btn btn-outline-primary" id="btn-apply-filter"><i class="fas fa-check"></i>
                            Filter</button>
                        @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                            <button class="btn btn-outline-success ms-2" id="btn-download-filtered"
                                onclick="downloadFilteredData()">
                                <i class="fas fa-download"></i>
                                <div class="spinner-border spinner-border-sm ms-1" id="sp-download-filtered" role="status"
                                    hidden><span class="visually-hidden">Loading...</span></div>
                                Download
                            </button>
                        @endif
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
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-5" data-bs-toggle="tab" href="#tabpanel-pembatalan"
                                    role="tab" aria-controls="tabpanel-pembatalan"
                                    aria-selected="false">Pembatalan</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-6" data-bs-toggle="tab" href="#tabpanel-koreksi"
                                    role="tab" aria-controls="tabpanel-koreksi" aria-selected="false">Koreksi</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-7" data-bs-toggle="tab" href="#tabpanel-pending"
                                    role="tab" aria-controls="tabpanel-pending" aria-selected="false">Pending</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-8" data-bs-toggle="tab" href="#tabpanel-nonstandar"
                                    role="tab" aria-controls="tabpanel-nonstandar" aria-selected="false">Non
                                    Standar</a>
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
                                                <th><input type="checkbox" id="select-all-pkp"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>UBAH TIPE</th>
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>KODE PRODUK</th>
                                                <th>NAMA PRODUK</th>
                                                <th>SATUAN</th>
                                                <th>QTY (PCS)</th>
                                                <th>HARGA SATUAN</th>
                                                <th>HARGA TOTAL</th>
                                                <th>DISC</th>
                                                <th>DPP</th>
                                                <th>DPP LAIN</th>
                                                <th>PPN 11%</th>
                                                <th>TGL FAKTUR PAJAK</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to" data-for="pkp"
                                                        disabled>
                                                        <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
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
                                                        placeholder="Kode Produk" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Nama Produk" data-column="9"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Satuan" data-column="10"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Qty" data-column="11"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Harga Satuan" data-column="12"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Harga Total" data-column="13"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Disc" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="DPP" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="DPP Lain" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="PPN" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Tgl Faktur Pajak" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Alamat" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Tipe Pajak" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Nama Sesuai NPWP" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Alamat NPWP" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No Telepon" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Brand" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Depo" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Area" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Type Jual" data-column="28"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Kode Jenis FP" data-column="29"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Status FP" data-column="30"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="ID TKU Pembeli" data-column="31"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Jenis" data-column="32"></th>
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
                                                <th><input type="checkbox" id="select-all-pkpnppn"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>UBAH TIPE</th>
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>KODE PRODUK</th>
                                                <th>NAMA PRODUK</th>
                                                <th>SATUAN</th>
                                                <th>QTY (PCS)</th>
                                                <th>HARGA SATUAN</th>
                                                <th>HARGA TOTAL</th>
                                                <th>DISC</th>
                                                <th>DPP</th>
                                                <th>DPP LAIN</th>
                                                <th>PPN 11%</th>
                                                <th>TGL FAKTUR PAJAK</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to"
                                                        data-for="pkpnppn" disabled>
                                                        <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
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
                                                        placeholder="Kode Produk" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Nama Produk" data-column="9"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Satuan" data-column="10"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Qty" data-column="11"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Harga Satuan" data-column="12"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Harga Total" data-column="13"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Disc" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="DPP" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="DPP Lain" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="PPN" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Tgl Faktur Pajak" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Alamat" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Tipe Pajak" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Nama Sesuai NPWP" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Alamat NPWP" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="No Telepon" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="No FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Brand" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Depo" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Area" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Type Jual" data-column="28"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Kode Jenis FP" data-column="29"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Status FP" data-column="30"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="ID TKU Pembeli" data-column="31"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pkpnppn"
                                                        placeholder="Jenis" data-column="32"></th>
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
                                                <th><input type="checkbox" id="select-all-npkp"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>UBAH TIPE</th>
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>KODE PRODUK</th>
                                                <th>NAMA PRODUK</th>
                                                <th>SATUAN</th>
                                                <th>QTY (PCS)</th>
                                                <th>HARGA SATUAN</th>
                                                <th>HARGA TOTAL</th>
                                                <th>DISC</th>
                                                <th>DPP</th>
                                                <th>DPP LAIN</th>
                                                <th>PPN 11%</th>
                                                <th>TGL FAKTUR PAJAK</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to" data-for="npkp"
                                                        disabled>
                                                        <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
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
                                                        placeholder="Kode Produk" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Nama Produk" data-column="9"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Satuan" data-column="10"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Qty" data-column="11"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Harga Satuan" data-column="12"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Harga Total" data-column="13"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Disc" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="DPP" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="DPP Lain" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="PPN" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Tgl Faktur Pajak" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Alamat" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Tipe Pajak" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Nama Sesuai NPWP" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Alamat NPWP" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="No Telepon" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="No FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Brand" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Depo" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Area" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Type Jual" data-column="28"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Kode Jenis FP" data-column="29"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Status FP" data-column="30"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="ID TKU Pembeli" data-column="31"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkp"
                                                        placeholder="Jenis" data-column="32"></th>
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
                                                <th><input type="checkbox" id="select-all-npkpnppn"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>UBAH TIPE</th>
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>KODE PRODUK</th>
                                                <th>NAMA PRODUK</th>
                                                <th>SATUAN</th>
                                                <th>QTY (PCS)</th>
                                                <th>HARGA SATUAN</th>
                                                <th>HARGA TOTAL</th>
                                                <th>DISC</th>
                                                <th>DPP</th>
                                                <th>DPP LAIN</th>
                                                <th>PPN 11%</th>
                                                <th>TGL FAKTUR PAJAK</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to"
                                                        data-for="npkpnppn" disabled>
                                                        <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
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
                                                        placeholder="Kode Produk" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Nama Produk" data-column="9"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Satuan" data-column="10"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Qty" data-column="11"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Harga Satuan" data-column="12"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Harga Total" data-column="13"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Disc" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="DPP" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="DPP Lain" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="PPN" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Tgl Faktur Pajak" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Alamat" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Tipe Pajak" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Nama Sesuai NPWP" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Alamat NPWP" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="No Telepon" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="No FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Brand" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Depo" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Area" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Type Jual" data-column="28"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Kode Jenis FP" data-column="29"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Status FP" data-column="30"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="ID TKU Pembeli" data-column="31"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-npkpnppn"
                                                        placeholder="Jenis" data-column="32"></th>
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
                                                <th><input type="checkbox" id="select-all-retur"></th>
                                                <!-- Checkbox untuk select all -->
                                                <th>UBAH TIPE</th>
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>KODE PRODUK</th>
                                                <th>NAMA PRODUK</th>
                                                <th>SATUAN</th>
                                                <th>QTY (PCS)</th>
                                                <th>HARGA SATUAN</th>
                                                <th>HARGA TOTAL</th>
                                                <th>DISC</th>
                                                <th>DPP</th>
                                                <th>DPP LAIN</th>
                                                <th>PPN 11%</th>
                                                <th>TGL FAKTUR PAJAK</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to" data-for="retur"
                                                        disabled>
                                                        <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
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
                                                        placeholder="Kode Produk" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Nama Produk" data-column="9"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Satuan" data-column="10"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Qty" data-column="11"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Harga Satuan" data-column="12"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Harga Total" data-column="13"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Disc" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="DPP" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="DPP Lain" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="PPN" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Tgl Faktur Pajak" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Alamat" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Tipe Pajak" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Nama Sesuai NPWP" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Alamat NPWP" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="No Telepon" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="No FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Brand" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Depo" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Area" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Type Jual" data-column="28"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Kode Jenis FP" data-column="29"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Status FP" data-column="30"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="ID TKU Pembeli" data-column="31"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-retur"
                                                        placeholder="Jenis" data-column="32"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabpanel-nonstandar" role="tabpanel"
                                aria-labelledby="tabpanel-nonstandar">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-nonstandar"></i>
                                                <div class="spinner-border spinner-border-sm spinner-counter-nonstandar"
                                                    role="status" style="display: none;"><span
                                                        class="visually-hidden">Loading...</span></div> Terdapat:
                                            </td>
                                            <td id="total_ready2download_nonstandar">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                                                    <button class="btn btn-sm btn-primary ms-3 btn-download"
                                                        id="btn-download-nonstandar"
                                                        onclick="downloadCheckedData('nonstandar')" hidden>
                                                        <i class="fas fa-download"></i>
                                                        <div class="spinner-border spinner-border-sm" id="sp-nonstandar"
                                                            role="status" hidden><span
                                                                class="visually-hidden">Loading...</span></div> Download
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_nonstandar">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover"
                                        id="table-nonstandar">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-nonstandar"></th>
                                                <th>UBAH TIPE</th>
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>KODE PRODUK</th>
                                                <th>NAMA PRODUK</th>
                                                <th>SATUAN</th>
                                                <th>QTY (PCS)</th>
                                                <th>HARGA SATUAN</th>
                                                <th>HARGA TOTAL</th>
                                                <th>DISC</th>
                                                <th>DPP</th>
                                                <th>DPP LAIN</th>
                                                <th>PPN 11%</th>
                                                <th>TGL FAKTUR PAJAK</th>
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
                                                <th>KETERANGAN</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th><button class="btn btn-sm btn-primary apply-move-to"
                                                        data-for="nonstandar" disabled>
                                                        <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Customer ID" data-column="2"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="NIK" data-column="3"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Nama Customer" data-column="4"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="NPWP Customer" data-column="5"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="No DO" data-column="6"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="No Invoice" data-column="7"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Kode Produk" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Nama Produk" data-column="9"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Satuan" data-column="10"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Qty" data-column="11"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Harga Satuan" data-column="12"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Harga Total" data-column="13"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Disc" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="DPP" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="DPP Lain" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="PPN" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Tgl Faktur Pajak" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Alamat" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Tipe Pajak" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Nama Sesuai NPWP" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Alamat NPWP" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="No Telepon" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="No FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Depo" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Area" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Brand" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Type Jual" data-column="28"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Kode Jenis FP" data-column="29"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Status FP" data-column="30"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="ID TKU Pembeli" data-column="31"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Jenis" data-column="32"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-nonstandar"
                                                        placeholder="Keterangan" data-column="33"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab Panel Pembatalan --}}
                            <div class="tab-pane" id="tabpanel-pembatalan" role="tabpanel"
                                aria-labelledby="tabpanel-pembatalan">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-pembatalan"></i>
                                                <div class="spinner-border spinner-border-sm spinner-counter-pembatalan"
                                                    role="status" style="display: none;"><span
                                                        class="visually-hidden">Loading...</span></div> Terdapat:
                                            </td>
                                            <td id="total_ready2download_pembatalan">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                                                    <button class="btn btn-sm btn-primary ms-3 btn-download"
                                                        id="btn-download-pembatalan"
                                                        onclick="downloadCheckedData('pembatalan')" hidden>
                                                        <i class="fas fa-download"></i>
                                                        <div class="spinner-border spinner-border-sm" id="sp-pembatalan"
                                                            role="status" hidden><span
                                                                class="visually-hidden">Loading...</span></div> Download
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_pembatalan">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover"
                                        id="table-pembatalan">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-pembatalan"></th>
                                                <th>UBAH TIPE</th>
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>KODE PRODUK</th>
                                                <th>NAMA PRODUK</th>
                                                <th>SATUAN</th>
                                                <th>QTY (PCS)</th>
                                                <th>HARGA SATUAN</th>
                                                <th>HARGA TOTAL</th>
                                                <th>DISC</th>
                                                <th>DPP</th>
                                                <th>DPP LAIN</th>
                                                <th>PPN 11%</th>
                                                <th>TGL FAKTUR PAJAK</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to"
                                                        data-for="pembatalan" disabled>
                                                        <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Customer ID" data-column="2"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="NIK" data-column="3"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Nama Customer" data-column="4"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="NPWP Customer" data-column="5"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="No DO" data-column="6"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="No Invoice" data-column="7"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Kode Produk" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Nama Produk" data-column="9"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Satuan" data-column="10"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Qty" data-column="11"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Harga Satuan" data-column="12"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Harga Total" data-column="13"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Disc" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="DPP" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="DPP Lain" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="PPN" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Tgl Faktur Pajak" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Alamat" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Tipe Pajak" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Nama Sesuai NPWP" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Alamat NPWP" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="No Telepon" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="No FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Depo" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Area" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Brand" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Type Jual" data-column="28"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Kode Jenis FP" data-column="29"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Status FP" data-column="30"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="ID TKU Pembeli" data-column="31"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pembatalan"
                                                        placeholder="Jenis" data-column="32"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab Panel Koreksi --}}
                            <div class="tab-pane" id="tabpanel-koreksi" role="tabpanel"
                                aria-labelledby="tabpanel-koreksi">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-koreksi"></i>
                                                <div class="spinner-border spinner-border-sm spinner-counter-koreksi"
                                                    role="status" style="display: none;"><span
                                                        class="visually-hidden">Loading...</span></div> Terdapat:
                                            </td>
                                            <td id="total_ready2download_koreksi">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                                                    <button class="btn btn-sm btn-primary ms-3 btn-download"
                                                        id="btn-download-koreksi"
                                                        onclick="downloadCheckedData('koreksi')" hidden>
                                                        <i class="fas fa-download"></i>
                                                        <div class="spinner-border spinner-border-sm" id="sp-koreksi"
                                                            role="status" hidden><span
                                                                class="visually-hidden">Loading...</span></div> Download
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_koreksi">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover"
                                        id="table-koreksi">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-koreksi"></th>
                                                <th>UBAH TIPE</th>
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>KODE PRODUK</th>
                                                <th>NAMA PRODUK</th>
                                                <th>SATUAN</th>
                                                <th>QTY (PCS)</th>
                                                <th>HARGA SATUAN</th>
                                                <th>HARGA TOTAL</th>
                                                <th>DISC</th>
                                                <th>DPP</th>
                                                <th>DPP LAIN</th>
                                                <th>PPN 11%</th>
                                                <th>TGL FAKTUR PAJAK</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to"
                                                        data-for="koreksi" disabled>
                                                        <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Customer ID" data-column="2"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="NIK" data-column="3"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Nama Customer" data-column="4"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="NPWP Customer" data-column="5"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="No DO" data-column="6"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="No Invoice" data-column="7"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Kode Produk" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Nama Produk" data-column="9"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Satuan" data-column="10"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Qty" data-column="11"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Harga Satuan" data-column="12"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Harga Total" data-column="13"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Disc" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="DPP" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="DPP Lain" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="PPN" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Tgl Faktur Pajak" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Alamat" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Tipe Pajak" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Nama Sesuai NPWP" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Alamat NPWP" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="No Telepon" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="No FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Depo" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Area" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Brand" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Type Jual" data-column="28"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Kode Jenis FP" data-column="29"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Status FP" data-column="30"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="ID TKU Pembeli" data-column="31"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-koreksi"
                                                        placeholder="Jenis" data-column="32"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab Panel Pending --}}
                            <div class="tab-pane" id="tabpanel-pending" role="tabpanel"
                                aria-labelledby="tabpanel-pending">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-pending"></i>
                                                <div class="spinner-border spinner-border-sm spinner-counter-pending"
                                                    role="status" style="display: none;"><span
                                                        class="visually-hidden">Loading...</span></div> Terdapat:
                                            </td>
                                            <td id="total_ready2download_pending">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                @if (Auth::user()->canAccessMenu('reguler-pajak-keluaran', \App\Models\AccessGroup::LEVEL_READ_WRITE))
                                                    <button class="btn btn-sm btn-primary ms-3 btn-download"
                                                        id="btn-download-pending"
                                                        onclick="downloadCheckedData('pending')" hidden>
                                                        <i class="fas fa-download"></i>
                                                        <div class="spinner-border spinner-border-sm" id="sp-pending"
                                                            role="status" hidden><span
                                                                class="visually-hidden">Loading...</span></div> Download
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_pending">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover"
                                        id="table-pending">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-pending"></th>
                                                <th>UBAH TIPE</th>
                                                <th>CUSTOMER ID</th>
                                                <th>NIK</th>
                                                <th>NAMA CUSTOMER</th>
                                                <th>NPWP CUSTOMER</th>
                                                <th>NO DO</th>
                                                <th>NO INVOICE</th>
                                                <th>KODE PRODUK</th>
                                                <th>NAMA PRODUK</th>
                                                <th>SATUAN</th>
                                                <th>QTY (PCS)</th>
                                                <th>HARGA SATUAN</th>
                                                <th>HARGA TOTAL</th>
                                                <th>DISC</th>
                                                <th>DPP</th>
                                                <th>DPP LAIN</th>
                                                <th>PPN 11%</th>
                                                <th>TGL FAKTUR PAJAK</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to"
                                                        data-for="pending" disabled>
                                                        <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Customer ID" data-column="2"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="NIK" data-column="3"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Nama Customer" data-column="4"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="NPWP Customer" data-column="5"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="No DO" data-column="6"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="No Invoice" data-column="7"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Kode Produk" data-column="8"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Nama Produk" data-column="9"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Satuan" data-column="10"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Qty" data-column="11"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Harga Satuan" data-column="12"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Harga Total" data-column="13"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Disc" data-column="14"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="DPP" data-column="15"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="DPP Lain" data-column="16"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="PPN" data-column="17"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Tgl Faktur Pajak" data-column="18"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Alamat" data-column="19"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Tipe Pajak" data-column="20"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Nama Sesuai NPWP" data-column="21"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Alamat NPWP" data-column="22"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="No Telepon" data-column="23"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="No FP" data-column="24"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Depo" data-column="25"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Area" data-column="26"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Brand" data-column="27"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Type Jual" data-column="28"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Kode Jenis FP" data-column="29"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Status FP" data-column="30"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="ID TKU Pembeli" data-column="31"></th>
                                                <th><input type="text"
                                                        class="form-control form-control-sm column-filter-pending"
                                                        placeholder="Jenis" data-column="32"></th>
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

    <!-- Modal Input Master PKP -->
    <div class="modal fade" id="modalInputMasterPkp" tabindex="-1" aria-labelledby="modalInputMasterPkpLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInputMasterPkpLabel">Input Master PKP yang Belum Terdaftar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        Terdapat Master Data PKP yang belum ada untuk data-data yang dipilih. Silakan lengkapi data di bawah
                        ini sebelum melanjutkan proses 'Ubah Tipe'.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Pelanggan</th>
                                    <th>Nama PKP</th>
                                    <th>NPWP / No PKP</th>
                                    <th>Alamat PKP</th>
                                    <th>Type Pajak</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-missing-pkp">
                                <!-- Data di-render via Javascript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-save-master-pkp">Simpan &
                        Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $dtDataRoute = route('pnl.reguler.pajak-keluaran.dtdata');
@endphp
@include('pnl.reguler.pajak-keluaran.script')
