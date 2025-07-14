@extends('layouts.master')

@section('title', 'Pajak Keluaran - Reguler | PNL')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
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
            display: flex; /* Make it a flex container */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
        }

        div.dataTables_processing > div {
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

            div.dataTables_processing > div {
                gap: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            div.dataTables_processing {
                padding: 0.5rem;
                border-radius: 0.25rem;
            }

            div.dataTables_processing > div {
                gap: 0.5rem;
            }
        }

        table.dataTable thead th {
            white-space: nowrap;
            /* min-width: 100px; */
        }

        /* table.dataTable thead th, */
        /* table.dataTable tbody td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        } */

        table.dataTable thead th:nth-child(1),
        table.dataTable tbody td:nth-child(1) {
            width: 12px;
            /* max-width: 3%;
            min-width: 2%; */
        }

        /* kolom checkbox header ukuran fixed
        table.dataTable thead th:nth-child(1) {
            width: 5%;
            max-width: 5%;
            min-width: 5%;
        } */
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
                        <label>PT</label>
                        <select class="form-select" id="filter_pt">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label>BRAND</label>
                        <select class="form-select" id="filter_brand">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label>DEPO</label>
                        <select class="form-select" id="filter_depo">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label>PERIODE</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="filter_periode" id="filter_periode"
                                placeholder="Pilih Periode" aria-label="Pilih Periode" value="01/01/2025 - 02/01/2025" />
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label>Status</label>
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
                        <button class="btn btn-outline-primary" id="btn-apply-filter"><i class="fas fa-check"></i> Filter</button>
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
                                    role="tab" aria-controls="tabpanel-pkpnppn" aria-selected="true">PKP (Non-PPN)</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-2" data-bs-toggle="tab" href="#tabpanel-nonpkp"
                                    role="tab" aria-controls="tabpanel-nonpkp" aria-selected="false">Non-PKP</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-3" data-bs-toggle="tab" href="#tabpanel-nonpkpnppn"
                                    role="tab" aria-controls="tabpanel-nonpkpnppn" aria-selected="false">Non-PKP (Non-PPN)</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="simple-tab-4" data-bs-toggle="tab" href="#tabpanel-retur"
                                    role="tab" aria-controls="tabpanel-retur" aria-selected="false">Retur</a>
                            </li>
                        </ul>
                        <div class="tab-content pt-3" id="tab-content">
                            <div class="tab-pane active" id="tabpanel-pkp" role="tabpanel" aria-labelledby="tabpanel-pkp">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-pkp"></i><div class="spinner-border spinner-border-sm spinner-counter-pkp" role="status" style="display: none;"><span class="visually-hidden">Loading...</span></div> Terdapat: </td>
                                            <td id="total_ready2download_pkp">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                <button class="btn btn-sm btn-primary ms-3 btn-download" id="btn-download-pkp" onclick="downloadCheckedData('pkp')" hidden>
                                                    <i class="fas fa-download"></i>
                                                    <div class="spinner-border spinner-border-sm " id="sp-pkp" role="status" hidden><span class="visually-hidden">Loading...</span></div> Download
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_pkp">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <table class="table table-xm table-striped table-bordered table-hover" id="table-pkp">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-pkp"></th> <!-- Checkbox untuk select all -->
                                            <th>MOVE TO</th>
                                            <th>NO INVOICE</th>
                                            <th>NO DO</th>
                                            <th>KODE PRODUK</th>
                                            <th>QTY (PCS)</th>
                                            <th>HARGA SATUAN</th>
                                            <th>DISC</th>
                                            <th>HARGA TOTAL</th>
                                            <th>DPP</th>
                                            <th>PPN 11%</th>
                                            <th>TGL FAKTUR PAJAK</th>
                                            <th>DEPO</th>
                                            <th>AREA</th>
                                            <th>NAMA PRODUK</th>
                                            <th>NPWP CUSTOMER</th>
                                            <th>CUSTOMER ID</th>
                                            <th>NAMA CUSTOMER</th>
                                            <th>ALAMAT</th>
                                            <th>TYPE PAJAK</th>
                                            <th>SATUAN</th>
                                            <th>NAMA SESUAI NPWP</th>
                                            <th>ALAMAT NPWP</th>
                                            <th>NO TELEPON</th>
                                            <th>NO FP</th>
                                            <th>BRAND</th>
                                            <th>TYPE JUAL</th>
                                            <th>KODE JENIS FP</th>
                                            <th>STATUS FP</th>
                                            <th>NIK</th>
                                            <th>DPP LAIN</th>
                                            <th>ID TKU PEMBELI</th>
                                            <th>JENIS</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Invoice" data-column="1"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter DO" data-column="2"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Kode Produk" data-column="3"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Qty" data-column="4"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Harga Satuan" data-column="5"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Disc" data-column="6"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Harga Total" data-column="7"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter DPP" data-column="8"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter PPN" data-column="9"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Tgl Faktur Pajak" data-column="10"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Depo" data-column="11"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Area" data-column="12"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Nama Produk" data-column="13"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter NPWP Customer" data-column="14"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Customer ID" data-column="15"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Nama Customer" data-column="16"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Alamat" data-column="17"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Type Pajak" data-column="18"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Satuan" data-column="19"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Nama Sesuai NPWP" data-column="20"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Alamat NPWP" data-column="21"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter No Telepon" data-column="22"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter No FP" data-column="23"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Brand" data-column="24"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Type Jual" data-column="25"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Kode Jenis FP" data-column="26"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Status FP" data-column="27"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter NIK" data-column="28"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter DPP Lain" data-column="29"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter ID TKU Pembeli" data-column="30"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                    placeholder="Filter Jenis" data-column="31"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="tabpanel-pkpnppn" role="tabpanel" aria-labelledby="tabpanel-pkpnppn">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-pkpnppn"></i><div class="spinner-border spinner-border-sm spinner-counter-pkpnppn" role="status" style="display: none;"><span class="visually-hidden">Loading...</span></div> Terdapat: </td>
                                            <td id="total_ready2download_pkpnppn">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                <button class="btn btn-sm btn-primary ms-3 btn-download" id="btn-download-pkpnppn" onclick="downloadCheckedData('pkpnppn')" hidden>
                                                    <i class="fas fa-download"></i>
                                                    <div class="spinner-border spinner-border-sm " id="sp-pkpnppn" role="status" hidden><span class="visually-hidden">Loading...</span></div> Download
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_pkpnppn">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <table class="table table-sm table-striped table-bordered table-hover" id="table-pkpnppn">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-pkpnppn"></th> <!-- Checkbox untuk select all -->
                                            <th>NO INVOICE</th>
                                            <th>NO DO</th>
                                            <th>KODE PRODUK</th>
                                            <th>QTY (PCS)</th>
                                            <th>HARGA SATUAN</th>
                                            <th>DISC</th>
                                            <th>HARGA TOTAL</th>
                                            <th>DPP</th>
                                            <th>PPN 11%</th>
                                            <th>TGL FAKTUR PAJAK</th>
                                            <th>DEPO</th>
                                            <th>AREA</th>
                                            <th>NAMA PRODUK</th>
                                            <th>NPWP CUSTOMER</th>
                                            <th>CUSTOMER ID</th>
                                            <th>NAMA CUSTOMER</th>
                                            <th>ALAMAT</th>
                                            <th>TYPE PAJAK</th>
                                            <th>SATUAN</th>
                                            <th>NAMA SESUAI NPWP</th>
                                            <th>ALAMAT NPWP</th>
                                            <th>NO TELEPON</th>
                                            <th>NO FP</th>
                                            <th>BRAND</th>
                                            <th>TYPE JUAL</th>
                                            <th>KODE JENIS FP</th>
                                            <th>STATUS FP</th>
                                            <th>NIK</th>
                                            <th>DPP LAIN</th>
                                            <th>ID TKU PEMBELI</th>
                                            <th>JENIS</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Invoice" data-column="1"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter DO" data-column="2"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Kode Produk" data-column="3"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Qty" data-column="4"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Harga Satuan" data-column="5"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Disc" data-column="6"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Harga Total" data-column="7"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter DPP" data-column="8"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter PPN" data-column="9"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Tgl Faktur Pajak" data-column="10"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Depo" data-column="11"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Area" data-column="12"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Nama Produk" data-column="13"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter NPWP Customer" data-column="14"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Customer ID" data-column="15"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Nama Customer" data-column="16"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Alamat" data-column="17"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Type Pajak" data-column="18"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Satuan" data-column="19"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Nama Sesuai NPWP" data-column="20"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Alamat NPWP" data-column="21"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter No Telepon" data-column="22"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter No FP" data-column="23"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Brand" data-column="24"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Type Jual" data-column="25"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Kode Jenis FP" data-column="26"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Status FP" data-column="27"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter NIK" data-column="28"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter DPP Lain" data-column="29"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter ID TKU Pembeli" data-column="30"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-pkpnppn"
                                                    placeholder="Filter Jenis" data-column="31"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="tabpanel-nonpkp" role="tabpanel" aria-labelledby="tabpanel-nonpkp">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-npkp"></i><div class="spinner-border spinner-border-sm spinner-counter-npkp" role="status" style="display: none;"><span class="visually-hidden">Loading...</span></div> Terdapat: </td>
                                            <td id="total_ready2download_npkp">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                <button class="btn btn-sm btn-primary ms-3 btn-download" id="btn-download-npkp" onclick="downloadCheckedData('npkp')" hidden>
                                                    <i class="fas fa-download"></i>
                                                    <div class="spinner-border spinner-border-sm " id="sp-npkp" role="status" hidden><span class="visually-hidden">Loading...</span></div> Download
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_npkp">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <table class="table table-sm table-striped table-bordered table-hover" id="table-npkp">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-npkp"></th> <!-- Checkbox untuk select all -->
                                            <th>NO INVOICE</th>
                                            <th>NO DO</th>
                                            <th>KODE PRODUK</th>
                                            <th>QTY (PCS)</th>
                                            <th>HARGA SATUAN</th>
                                            <th>DISC</th>
                                            <th>HARGA TOTAL</th>
                                            <th>DPP</th>
                                            <th>PPN 11%</th>
                                            <th>TGL FAKTUR PAJAK</th>
                                            <th>DEPO</th>
                                            <th>AREA</th>
                                            <th>NAMA PRODUK</th>
                                            <th>NPWP CUSTOMER</th>
                                            <th>CUSTOMER ID</th>
                                            <th>NAMA CUSTOMER</th>
                                            <th>ALAMAT</th>
                                            <th>TYPE PAJAK</th>
                                            <th>SATUAN</th>
                                            <th>NAMA SESUAI NPWP</th>
                                            <th>ALAMAT NPWP</th>
                                            <th>NO TELEPON</th>
                                            <th>NO FP</th>
                                            <th>BRAND</th>
                                            <th>TYPE JUAL</th>
                                            <th>KODE JENIS FP</th>
                                            <th>STATUS FP</th>
                                            <th>NIK</th>
                                            <th>DPP LAIN</th>
                                            <th>ID TKU PEMBELI</th>
                                            <th>JENIS</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Invoice" data-column="1"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter DO" data-column="2"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Kode Produk" data-column="3"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Qty" data-column="4"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Harga Satuan" data-column="5"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Disc" data-column="6"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Harga Total" data-column="7"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter DPP" data-column="8"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter PPN" data-column="9"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Tgl Faktur Pajak" data-column="10"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Depo" data-column="11"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Area" data-column="12"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Nama Produk" data-column="13"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter NPWP Customer" data-column="14"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Customer ID" data-column="15"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Nama Customer" data-column="16"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Alamat" data-column="17"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Type Pajak" data-column="18"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Satuan" data-column="19"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Nama Sesuai NPWP" data-column="20"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Alamat NPWP" data-column="21"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter No Telepon" data-column="22"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter No FP" data-column="23"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Brand" data-column="24"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Type Jual" data-column="25"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Kode Jenis FP" data-column="26"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Status FP" data-column="27"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter NIK" data-column="28"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter DPP Lain" data-column="29"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter ID TKU Pembeli" data-column="30"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkp"
                                                    placeholder="Filter Jenis" data-column="31"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="tabpanel-nonpkpnppn" role="tabpanel" aria-labelledby="tabpanel-nonpkpnppn">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-npkpnppn"></i><div class="spinner-border spinner-border-sm spinner-counter-npkpnppn" role="status" style="display: none;"><span class="visually-hidden">Loading...</span></div> Terdapat: </td>
                                            <td id="total_ready2download_npkpnppn">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                <button class="btn btn-sm btn-primary ms-3 btn-download" id="btn-download-npkpnppn" onclick="downloadCheckedData('npkpnppn')" hidden>
                                                    <i class="fas fa-download"></i>
                                                    <div class="spinner-border spinner-border-sm " id="sp-npkpnppn" role="status" hidden><span class="visually-hidden">Loading...</span></div> Download
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_npkpnppn">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <table class="table table-sm table-striped table-bordered table-hover" id="table-npkpnppn">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-npkpnppn"></th> <!-- Checkbox untuk select all -->
                                            <th>NO INVOICE</th>
                                            <th>NO DO</th>
                                            <th>KODE PRODUK</th>
                                            <th>QTY (PCS)</th>
                                            <th>HARGA SATUAN</th>
                                            <th>DISC</th>
                                            <th>HARGA TOTAL</th>
                                            <th>DPP</th>
                                            <th>PPN 11%</th>
                                            <th>TGL FAKTUR PAJAK</th>
                                            <th>DEPO</th>
                                            <th>AREA</th>
                                            <th>NAMA PRODUK</th>
                                            <th>NPWP CUSTOMER</th>
                                            <th>CUSTOMER ID</th>
                                            <th>NAMA CUSTOMER</th>
                                            <th>ALAMAT</th>
                                            <th>TYPE PAJAK</th>
                                            <th>SATUAN</th>
                                            <th>NAMA SESUAI NPWP</th>
                                            <th>ALAMAT NPWP</th>
                                            <th>NO TELEPON</th>
                                            <th>NO FP</th>
                                            <th>BRAND</th>
                                            <th>TYPE JUAL</th>
                                            <th>KODE JENIS FP</th>
                                            <th>STATUS FP</th>
                                            <th>NIK</th>
                                            <th>DPP LAIN</th>
                                            <th>ID TKU PEMBELI</th>
                                            <th>JENIS</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Invoice" data-column="1"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter DO" data-column="2"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Kode Produk" data-column="3"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Qty" data-column="4"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Harga Satuan" data-column="5"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Disc" data-column="6"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Harga Total" data-column="7"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter DPP" data-column="8"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter PPN" data-column="9"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Tgl Faktur Pajak" data-column="10"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Depo" data-column="11"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Area" data-column="12"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Nama Produk" data-column="13"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter NPWP Customer" data-column="14"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Customer ID" data-column="15"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Nama Customer" data-column="16"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Alamat" data-column="17"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Type Pajak" data-column="18"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Satuan" data-column="19"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Nama Sesuai NPWP" data-column="20"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Alamat NPWP" data-column="21"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter No Telepon" data-column="22"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter No FP" data-column="23"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Brand" data-column="24"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Type Jual" data-column="25"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Kode Jenis FP" data-column="26"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Status FP" data-column="27"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter NIK" data-column="28"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter DPP Lain" data-column="29"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter ID TKU Pembeli" data-column="30"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-npkpnppn"
                                                    placeholder="Filter Jenis" data-column="31"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="tabpanel-retur" role="tabpanel" aria-labelledby="tabpanel-retur">
                                <div class="col-md-6 col-sm-12 col-xm-12 mb-3">
                                    <table>
                                        <tr>
                                            <td><i class="fas fa-info-circle icon-counter-retur"></i><div class="spinner-border spinner-border-sm spinner-counter-retur" role="status" style="display: none;"><span class="visually-hidden">Loading...</span></div> Terdapat: </td>
                                            <td id="total_ready2download_retur">0</td>
                                            <td>data siap&nbsp;&nbsp; di-download</td>
                                            <td rowspan="2">
                                                <button class="btn btn-sm btn-primary ms-3 btn-download" id="btn-download-retur" onclick="downloadCheckedData('retur')" hidden>
                                                    <i class="fas fa-download"></i>
                                                    <div class="spinner-border spinner-border-sm" id="sp-retur" role="status" hidden><span class="visually-hidden">Loading...</span></div> Download
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="total_downloaded_retur">0</td>
                                            <td>data telah di-download</td>
                                        </tr>
                                    </table>
                                </div>
                                <table class="table table-sm table-striped table-bordered table-hover" id="table-retur">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-retur"></th> <!-- Checkbox untuk select all -->
                                            <th>NO INVOICE</th>
                                            <th>NO DO</th>
                                            <th>KODE PRODUK</th>
                                            <th>QTY (PCS)</th>
                                            <th>HARGA SATUAN</th>
                                            <th>DISC</th>
                                            <th>HARGA TOTAL</th>
                                            <th>DPP</th>
                                            <th>PPN 11%</th>
                                            <th>TGL FAKTUR PAJAK</th>
                                            <th>DEPO</th>
                                            <th>AREA</th>
                                            <th>NAMA PRODUK</th>
                                            <th>NPWP CUSTOMER</th>
                                            <th>CUSTOMER ID</th>
                                            <th>NAMA CUSTOMER</th>
                                            <th>ALAMAT</th>
                                            <th>TYPE PAJAK</th>
                                            <th>SATUAN</th>
                                            <th>NAMA SESUAI NPWP</th>
                                            <th>ALAMAT NPWP</th>
                                            <th>NO TELEPON</th>
                                            <th>NO FP</th>
                                            <th>BRAND</th>
                                            <th>TYPE JUAL</th>
                                            <th>KODE JENIS FP</th>
                                            <th>STATUS FP</th>
                                            <th>NIK</th>
                                            <th>DPP LAIN</th>
                                            <th>ID TKU PEMBELI</th>
                                            <th>JENIS</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Invoice" data-column="1"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter DO" data-column="2"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Kode Produk" data-column="3"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Qty" data-column="4"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Harga Satuan" data-column="5"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Disc" data-column="6"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Harga Total" data-column="7"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter DPP" data-column="8"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter PPN" data-column="9"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Tgl Faktur Pajak" data-column="10"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Depo" data-column="11"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Area" data-column="12"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Nama Produk" data-column="13"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter NPWP Customer" data-column="14"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Customer ID" data-column="15"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Nama Customer" data-column="16"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Alamat" data-column="17"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Type Pajak" data-column="18"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Satuan" data-column="19"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Nama Sesuai NPWP" data-column="20"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Alamat NPWP" data-column="21"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter No Telepon" data-column="22"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter No FP" data-column="23"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Brand" data-column="24"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Type Jual" data-column="25"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Kode Jenis FP" data-column="26"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Status FP" data-column="27"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter NIK" data-column="28"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter DPP Lain" data-column="29"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter ID TKU Pembeli" data-column="30"></th>
                                            <th><input type="text" class="form-control form-control-sm column-filter-retur"
                                                    placeholder="Filter Jenis" data-column="31"></th>
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
@endsection

@section('script')
    <script src="{{ asset('assets/js/plugin/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/toastr/toastr.min.js') }}"></script>
    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut",
            "toastClass": "colored-toast"
        };

        let tablePkp;
        let tablePkpNppn;
        let tableNonPkp;
        let tableNonPkpNppn;
        let tableRetur;

        // Initialize new DataTable for PKP
        function initializeDataTablePkp() {
            if ($.fn.DataTable.isDataTable('#table-pkp')) {
                $('#table-pkp').DataTable().destroy();
            }

            tablePkp = $('#table-pkp').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('pnl.reguler.pajak-keluaran.dtdata') }}",
                    type: "POST",
                    data: function(d) {
                        d.pt = $('#filter_pt').val();
                        d.brand = $('#filter_brand').val();
                        d.depo = $('#filter_depo').val();
                        d.periode = $('#filter_periode').val();
                        d.tipe = 'pkp';
                        d.chstatus = $('#filter_chstatus').val();
                        return d;
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                },
                columns: [
                    {
                        data: 'is_checked',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = data == 1 ? 'checked' : '';
                            if(row.is_downloaded == 1 && data == 1) {
                                return '<div style="display: flex; align-items: center; gap: 5px;"><i class="fas fa-fw fa-check text-secondary"></i><i class="fas fa-fw fa-download text-secondary"></i></div>';
                            }
                            return `<input type="checkbox" class="row-checkbox-pkp" data-id="${row.id}" ${checked}>`;
                        }
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = row.is_checked == 1 ? '' : 'disabled';
                            return `<select id="move-to-${row.id}" class="form-select move-to" data-id="${row.id}" data-from="pkp" ${checked}>
                                <option value="">Pilih...</option>
                                <option value="pkp">PKP</option>
                                <option value="pkpnppn">PKP Non-PPN</option>
                                <option value="npkp">Non-PKP</option>
                                <option value="npkpnppn">Non-PKP Non-PPN</option>
                                <option value="retur">Retur</option>
                            </select>`;
                        }
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'qty_pcs',
                        name: 'qty_pcs'
                    },
                    {
                        data: 'hargasatuan_sblm_ppn',
                        name: 'hargasatuan_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'ppn',
                        name: 'ppn'
                    },
                    {
                        data: 'tgl_faktur_pajak',
                        name: 'tgl_faktur_pajak'
                    },
                    {
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
                    },
                    {
                        data: 'nama_sesuai_npwp',
                        name: 'nama_sesuai_npwp'
                    },
                    {
                        data: 'alamat_npwp_lengkap',
                        name: 'alamat_npwp_lengkap'
                    },
                    {
                        data: 'no_telepon',
                        name: 'no_telepon'
                    },
                    {
                        data: 'no_fp',
                        name: 'no_fp'
                    },
                    {
                        data: 'brand',
                        name: 'brand'
                    },
                    {
                        data: 'type_jual',
                        name: 'type_jual'
                    },
                    {
                        data: 'kode_jenis_fp',
                        name: 'kode_jenis_fp'
                    },
                    {
                        data: 'fp_normal_pengganti',
                        name: 'fp_normal_pengganti'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
                    },
                    {
                        data: 'id_tku_pembeli',
                        name: 'id_tku_pembeli'
                    },
                    {
                        data: 'barang_jasa',
                        name: 'barang_jasa'
                    }
                ],
                columnDefs: [{
                    targets: [0, ],
                    className: 'text-center',
                    width: '10%'
                }],
                order: [
                    [1, 'desc']
                ],
                lengthMenu: [
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, -1],
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, "All"]
                ],
                pageLength: 10,
                ordering: true,
                responsive: false,
                autoWidth: true,
                scrollX: true,
                language: {
                    processing: '<div><i class="fas fa-spinner fa-spin fa-2x"></i><span>Loading...</span></div>',
                    emptyTable: "Tidak ada data yang tersedia",
                    zeroRecords: "Tidak ada data yang ditemukan"
                },
                orderCellsTop: true,
                fixedHeader: true,
                fixedColumns: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    var api = this.api();

                    // Apply the search for each column
                    api.columns().every(function() {
                        var column = this;
                        var input = $('.column-filter-pkp[data-column="' + (column.index() - 1) + '"]');

                        input.on('keyup change clear', function() {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                    });

                    tablePkp.columns.adjust();
                },
                ajaxComplete: function() {
                    setDownloadCounter('pkp');
                },
                drawCallback: function(settings) {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    setDownloadCounter('pkp');
                }
            });
        }

        // Initialize new DataTable for PKP Non-PPN
        function initializeDataTablePkpNppn() {
            if ($.fn.DataTable.isDataTable('#table-pkpnppn')) {
                $('#table-pkpnppn').DataTable().destroy();
            }

            tablePkpNppn = $('#table-pkpnppn').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('pnl.reguler.pajak-keluaran.dtdata') }}",
                    type: "POST",
                    data: function(d) {
                        d.pt = $('#filter_pt').val();
                        d.brand = $('#filter_brand').val();
                        d.depo = $('#filter_depo').val();
                        d.periode = $('#filter_periode').val();
                        d.tipe = 'pkpnppn';
                        d.chstatus = $('#filter_chstatus').val();
                        return d;
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                },
                columns: [
                    {
                        data: 'is_checked',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = data == 1 ? 'checked' : '';
                            if(row.is_downloaded == 1 && data == 1) {
                                return '<div style="display: flex; align-items: center; gap: 5px;"><i class="fas fa-fw fa-check text-secondary"></i><i class="fas fa-fw fa-download text-secondary"></i></div>';
                            }
                            return `<input type="checkbox" class="row-checkbox-pkpnppn" data-id="${row.id}" ${checked}>`;
                        }
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'qty_pcs',
                        name: 'qty_pcs'
                    },
                    {
                        data: 'hargasatuan_sblm_ppn',
                        name: 'hargasatuan_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'ppn',
                        name: 'ppn'
                    },
                    {
                        data: 'tgl_faktur_pajak',
                        name: 'tgl_faktur_pajak'
                    },
                    {
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
                    },
                    {
                        data: 'nama_sesuai_npwp',
                        name: 'nama_sesuai_npwp'
                    },
                    {
                        data: 'alamat_npwp_lengkap',
                        name: 'alamat_npwp_lengkap'
                    },
                    {
                        data: 'no_telepon',
                        name: 'no_telepon'
                    },
                    {
                        data: 'no_fp',
                        name: 'no_fp'
                    },
                    {
                        data: 'brand',
                        name: 'brand'
                    },
                    {
                        data: 'type_jual',
                        name: 'type_jual'
                    },
                    {
                        data: 'kode_jenis_fp',
                        name: 'kode_jenis_fp'
                    },
                    {
                        data: 'fp_normal_pengganti',
                        name: 'fp_normal_pengganti'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
                    },
                    {
                        data: 'id_tku_pembeli',
                        name: 'id_tku_pembeli'
                    },
                    {
                        data: 'barang_jasa',
                        name: 'barang_jasa'
                    }
                ],
                columnDefs: [{
                    targets: [0, ],
                    className: 'text-center',
                    width: '10%'
                }],
                order: [
                    [1, 'desc']
                ],
                lengthMenu: [
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, -1],
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, "All"]
                ],
                pageLength: 10,
                ordering: true,
                responsive: false,
                autoWidth: true,
                scrollX: true,
                language: {
                    processing: '<div><i class="fas fa-spinner fa-spin fa-2x"></i><span>Loading...</span></div>',
                    emptyTable: "Tidak ada data yang tersedia",
                    zeroRecords: "Tidak ada data yang ditemukan"
                },
                orderCellsTop: true,
                fixedHeader: true,
                fixedColumns: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    var api = this.api();

                    // Apply the search for each column
                    api.columns().every(function() {
                        var column = this;
                        var input = $('.column-filter-pkpnppn[data-column="' + column.index() + '"]');

                        input.on('keyup change clear', function() {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                    });

                    tablePkpNppn.columns.adjust();
                },
                ajaxComplete: function() {
                    setDownloadCounter('pkpnppn');
                },
                drawCallback: function(settings) {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    setDownloadCounter('pkpnppn');
                }
            });
        }

        // Initialize new DataTable for Non-PKP
        function initializeDataTableNonPkp() {
            if ($.fn.DataTable.isDataTable('#table-npkp')) {
                $('#table-npkp').DataTable().destroy();
            }

            tableNonPkp = $('#table-npkp').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('pnl.reguler.pajak-keluaran.dtdata') }}",
                    type: "POST",
                    data: function(d) {
                        d.pt = $('#filter_pt').val();
                        d.brand = $('#filter_brand').val();
                        d.depo = $('#filter_depo').val();
                        d.periode = $('#filter_periode').val();
                        d.tipe = 'npkp';
                        d.chstatus = $('#filter_chstatus').val();
                        return d;
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                },
                columns: [
                    {
                        data: 'is_checked',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = data == 1 ? 'checked' : '';
                            if(row.is_downloaded == 1 && data == 1) {
                                return '<div style="display: flex; align-items: center; gap: 5px;"><i class="fas fa-fw fa-check text-secondary"></i><i class="fas fa-fw fa-download text-secondary"></i></div>';
                            }
                            return `<input type="checkbox" class="row-checkbox-npkp" data-id="${row.id}" ${checked}>`;
                        }
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'qty_pcs',
                        name: 'qty_pcs'
                    },
                    {
                        data: 'hargasatuan_sblm_ppn',
                        name: 'hargasatuan_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'ppn',
                        name: 'ppn'
                    },
                    {
                        data: 'tgl_faktur_pajak',
                        name: 'tgl_faktur_pajak'
                    },
                    {
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
                    },
                    {
                        data: 'nama_sesuai_npwp',
                        name: 'nama_sesuai_npwp'
                    },
                    {
                        data: 'alamat_npwp_lengkap',
                        name: 'alamat_npwp_lengkap'
                    },
                    {
                        data: 'no_telepon',
                        name: 'no_telepon'
                    },
                    {
                        data: 'no_fp',
                        name: 'no_fp'
                    },
                    {
                        data: 'brand',
                        name: 'brand'
                    },
                    {
                        data: 'type_jual',
                        name: 'type_jual'
                    },
                    {
                        data: 'kode_jenis_fp',
                        name: 'kode_jenis_fp'
                    },
                    {
                        data: 'fp_normal_pengganti',
                        name: 'fp_normal_pengganti'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
                    },
                    {
                        data: 'id_tku_pembeli',
                        name: 'id_tku_pembeli'
                    },
                    {
                        data: 'barang_jasa',
                        name: 'barang_jasa'
                    }
                ],
                columnDefs: [{
                    targets: [0, ],
                    className: 'text-center',
                    width: '10%'
                }],
                order: [
                    [1, 'desc']
                ],
                lengthMenu: [
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, -1],
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, "All"]
                ],
                pageLength: 10,
                ordering: true,
                responsive: false,
                autoWidth: true,
                scrollX: true,
                language: {
                    processing: '<div><i class="fas fa-spinner fa-spin fa-2x"></i><span>Loading...</span></div>',
                    emptyTable: "Tidak ada data yang tersedia",
                    zeroRecords: "Tidak ada data yang ditemukan"
                },
                orderCellsTop: true,
                fixedHeader: true,
                fixedColumns: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    var api = this.api();

                    // Apply the search for each column
                    api.columns().every(function() {
                        var column = this;
                        var input = $('.column-filter-npkp[data-column="' + column.index() + '"]');

                        input.on('keyup change clear', function() {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                    });
                    tableNonPkp.columns.adjust();
                },
                ajaxComplete: function() {
                    setDownloadCounter('npkp');
                },
                drawCallback: function(settings) {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    setDownloadCounter('npkp');
                }
            });
        }

        // Initialize new DataTable for Non-PKP Non-PPN
        function initializeDataTableNonPkpNppn() {
            if ($.fn.DataTable.isDataTable('#table-npkpnppn')) {
                $('#table-npkpnppn').DataTable().destroy();
            }

            tableNonPkpNppn = $('#table-npkpnppn').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('pnl.reguler.pajak-keluaran.dtdata') }}",
                    type: "POST",
                    data: function(d) {
                        d.pt = $('#filter_pt').val();
                        d.brand = $('#filter_brand').val();
                        d.depo = $('#filter_depo').val();
                        d.periode = $('#filter_periode').val();
                        d.tipe = 'npkpnppn';
                        d.chstatus = $('#filter_chstatus').val();
                        return d;
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                },
                columns: [
                    {
                        data: 'is_checked',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = data == 1 ? 'checked' : '';
                            if(row.is_downloaded == 1 && data == 1) {
                                return '<div style="display: flex; align-items: center; gap: 5px;"><i class="fas fa-fw fa-check text-secondary"></i><i class="fas fa-fw fa-download text-secondary"></i></div>';
                            }
                            return `<input type="checkbox" class="row-checkbox-npkpnppn" data-id="${row.id}" ${checked}>`;
                        }
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'qty_pcs',
                        name: 'qty_pcs'
                    },
                    {
                        data: 'hargasatuan_sblm_ppn',
                        name: 'hargasatuan_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'ppn',
                        name: 'ppn'
                    },
                    {
                        data: 'tgl_faktur_pajak',
                        name: 'tgl_faktur_pajak'
                    },
                    {
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
                    },
                    {
                        data: 'nama_sesuai_npwp',
                        name: 'nama_sesuai_npwp'
                    },
                    {
                        data: 'alamat_npwp_lengkap',
                        name: 'alamat_npwp_lengkap'
                    },
                    {
                        data: 'no_telepon',
                        name: 'no_telepon'
                    },
                    {
                        data: 'no_fp',
                        name: 'no_fp'
                    },
                    {
                        data: 'brand',
                        name: 'brand'
                    },
                    {
                        data: 'type_jual',
                        name: 'type_jual'
                    },
                    {
                        data: 'kode_jenis_fp',
                        name: 'kode_jenis_fp'
                    },
                    {
                        data: 'fp_normal_pengganti',
                        name: 'fp_normal_pengganti'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
                    },
                    {
                        data: 'id_tku_pembeli',
                        name: 'id_tku_pembeli'
                    },
                    {
                        data: 'barang_jasa',
                        name: 'barang_jasa'
                    }
                ],
                columnDefs: [{
                    targets: [0, ],
                    className: 'text-center',
                    width: '10%'
                }],
                order: [
                    [1, 'desc']
                ],
                lengthMenu: [
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, -1],
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, "All"]
                ],
                pageLength: 10,
                ordering: true,
                responsive: false,
                autoWidth: true,
                scrollX: true,
                language: {
                    processing: '<div><i class="fas fa-spinner fa-spin fa-2x"></i><span>Loading...</span></div>',
                    emptyTable: "Tidak ada data yang tersedia",
                    zeroRecords: "Tidak ada data yang ditemukan"
                },
                orderCellsTop: true,
                fixedHeader: true,
                fixedColumns: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    var api = this.api();

                    // Apply the search for each column
                    api.columns().every(function() {
                        var column = this;
                        var input = $('.column-filter-npkpnppn[data-column="' + column.index() + '"]');

                        input.on('keyup change clear', function() {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                    });
                    tableNonPkpNppn.columns.adjust();
                },
                ajaxComplete: function() {
                    setDownloadCounter('npkpnppn');
                },
                drawCallback: function(settings) {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    setDownloadCounter('npkpnppn');
                }
            });
        }

        // Initialize new DataTable for Retur
        function initializeDataTableRetur() {
            if ($.fn.DataTable.isDataTable('#table-retur')) {
                $('#table-retur').DataTable().destroy();
            }

            tableRetur = $('#table-retur').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('pnl.reguler.pajak-keluaran.dtdata') }}",
                    type: "POST",
                    data: function(d) {
                        d.pt = $('#filter_pt').val();
                        d.brand = $('#filter_brand').val();
                        d.depo = $('#filter_depo').val();
                        d.periode = $('#filter_periode').val();
                        d.tipe = 'retur';
                        d.chstatus = $('#filter_chstatus').val();
                        return d;
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                },
                columns: [
                    {
                        data: 'is_checked',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = data == 1 ? 'checked' : '';
                            if(row.is_downloaded == 1 && data == 1) {
                                return '<div style="display: flex; align-items: center; gap: 5px;"><i class="fas fa-fw fa-check text-secondary"></i><i class="fas fa-fw fa-download text-secondary"></i></div>';
                            }
                            return `<input type="checkbox" class="row-checkbox-retur" data-id="${row.id}" ${checked}>`;
                        }
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'qty_pcs',
                        name: 'qty_pcs'
                    },
                    {
                        data: 'hargasatuan_sblm_ppn',
                        name: 'hargasatuan_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'ppn',
                        name: 'ppn'
                    },
                    {
                        data: 'tgl_faktur_pajak',
                        name: 'tgl_faktur_pajak'
                    },
                    {
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
                    },
                    {
                        data: 'nama_sesuai_npwp',
                        name: 'nama_sesuai_npwp'
                    },
                    {
                        data: 'alamat_npwp_lengkap',
                        name: 'alamat_npwp_lengkap'
                    },
                    {
                        data: 'no_telepon',
                        name: 'no_telepon'
                    },
                    {
                        data: 'no_fp',
                        name: 'no_fp'
                    },
                    {
                        data: 'brand',
                        name: 'brand'
                    },
                    {
                        data: 'type_jual',
                        name: 'type_jual'
                    },
                    {
                        data: 'kode_jenis_fp',
                        name: 'kode_jenis_fp'
                    },
                    {
                        data: 'fp_normal_pengganti',
                        name: 'fp_normal_pengganti'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
                    },
                    {
                        data: 'id_tku_pembeli',
                        name: 'id_tku_pembeli'
                    },
                    {
                        data: 'barang_jasa',
                        name: 'barang_jasa'
                    }
                ],
                columnDefs: [{
                    targets: [0, ],
                    className: 'text-center',
                    width: '10%'
                }],
                order: [
                    [1, 'desc']
                ],
                lengthMenu: [
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, -1],
                    [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, "All"]
                ],
                pageLength: 10,
                ordering: true,
                responsive: false,
                autoWidth: true,
                scrollX: true,
                language: {
                    processing: '<div><i class="fas fa-spinner fa-spin fa-2x"></i><span>Loading...</span></div>',
                    emptyTable: "Tidak ada data yang tersedia",
                    zeroRecords: "Tidak ada data yang ditemukan"
                },
                orderCellsTop: true,
                fixedHeader: true,
                fixedColumns: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    var api = this.api();

                    // Apply the search for each column
                    api.columns().every(function() {
                        var column = this;
                        var input = $('.column-filter-retur[data-column="' + column.index() + '"]');

                        input.on('keyup change clear', function() {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                    });
                    tableNonPkpNppn.columns.adjust();
                },
                ajaxComplete: function() {
                    setDownloadCounter('retur');
                },
                drawCallback: function(settings) {
                    // Remove thead from .dataTables_scrollBody
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                    setDownloadCounter('retur');
                }
            });
        }

        function downloadCheckedData(tipe) {
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.download') }}?tipe=" + tipe,
                method: 'GET',
                xhrFields: {
                    responseType: 'blob'
                },
                beforeSend: function(xhr) {
                    $('.fa-download').hide();
                    $('#sp-' + tipe).show();
                },
                success: function(response, status, xhr) {
                    // Create a new blob object
                    var blob = new Blob([response], { type: xhr.getResponseHeader('Content-Type') });

                    // Create a temporary URL for the blob
                    var url = URL.createObjectURL(blob);

                    // Create an anchor element
                    var a = document.createElement('a');
                    a.href = url;

                    // Set the file name from response header
                    var filename = 'pajak_keluaran.xlsx';
                    var contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    if (contentDisposition && contentDisposition.indexOf('attachment') !== -1) {
                        var matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    a.download = filename;

                    // Append the anchor to the body,
                    // trigger the download by clicking the anchor,
                    // and then remove the anchor from the body
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);

                    // Revoke the temporary URL to free up memory
                    URL.revokeObjectURL(url);
                    // $('#sp-' + tipe).hide();

                    // reload the table
                    switch (tipe) {
                        case 'pkp':
                            tablePkp.ajax.reload();
                            setDownloadCounter('pkp');
                            break;

                        case 'pkpnppn':
                            tablePkpNppn.ajax.reload();
                            setDownloadCounter('pkpnppn');
                            break;

                        case 'nonpkp':
                            tableNonPkp.ajax.reload();
                            setDownloadCounter('nonpkp');
                            break;

                        case 'nonpkpnppn':
                            tableNonPkpNppn.ajax.reload();
                            setDownloadCounter('nonpkpnppn');
                            break;

                        case 'retur':
                            tableRetur.ajax.reload();
                            setDownloadCounter('retur');
                            break;

                        default:
                            tablePkp.ajax.reload();
                            setDownloadCounter('pkp');
                            tablePkpNppn.ajax.reload();
                            setDownloadCounter('pkpnppn');
                            tableNonPkp.ajax.reload();
                            setDownloadCounter('npkp');
                            tableNonPkpNppn.ajax.reload();
                            setDownloadCounter('npkpnppn');
                            tableRetur.ajax.reload();
                            setDownloadCounter('retur');
                            break;
                    }
                },
                error: function(error) {
                    console.error('Error: ' + error);
                    // $('#sp-' + tipe).hide();
                }
            });
        }

        function setDownloadCounter(tipe) {
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.count') }}?tipe=" + tipe,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    pt: $('#filter_pt').val(),
                    brand: $('#filter_brand').val(),
                    depo: $('#filter_depo').val(),
                    periode: $('#filter_periode').val(),
                    tipe: tipe
                },
                beforeSend: function(xhr) {
                    toggleSpinnerDownload(tipe, true);
                },
                success: function(response) {
                    $('#total_ready2download_' + tipe).text(response.data[0].ready2download_count ?? 0);
                    $('#total_downloaded_' + tipe).text(response.data[0].downloaded_count ?? 0);
                    if (parseInt(response.data[0].ready2download_count ?? 0) > 0){
                        $('#btn-download-' + tipe).prop('hidden', false);
                    } else {
                        $('#btn-download-' + tipe).prop('hidden', true);
                    }
                    toggleSpinnerDownload(tipe, false);
                },
                error: function(error) {
                    console.error('Error:', error);
                    toggleSpinnerDownload(tipe, false);
                }
            });
        }

        function toggleSpinnerDownload(tipe, show) {
            const spinner = $(`.spinner-counter-${tipe}`);
            const icon = $(`.icon-counter-${tipe}`);
            if (show) {
                spinner.show();
                icon.hide();
            } else {
                spinner.hide();
                icon.show();
            }
        }

        $(document).ready(function() {
            initializeDataTablePkp();
            initializeDataTableNonPkp();
            initializeDataTablePkpNppn();
            initializeDataTableNonPkpNppn();
            initializeDataTableRetur();

            // Event listener untuk tab switching
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('href');
                if (target === '#tabpanel-pkp') {
                    tablePkp.columns.adjust();
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                }
                if (target === '#tabpanel-nonpkp') {
                    tableNonPkp.columns.adjust();
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                }
                if (target === '#tabpanel-pkpnppn') {
                    tablePkpNppn.columns.adjust();
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                }
                if (target === '#tabpanel-nonpkpnppn') {
                    tableNonPkpNppn.columns.adjust();
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                }
                if (target === '#tabpanel-retur') {
                    tableRetur.columns.adjust();
                    $('.dataTables_scrollBody thead').remove();
                    $('.dataTables_scrollBody tfoot').remove();
                }
            });

            // Add change event listeners to filters
            $('#btn-apply-filter').on('click', function() {
                let appllied_tab = $('#inputGroupFilter').val();
                if (appllied_tab == 'pkp') {
                    tablePkp.ajax.reload();
                    setDownloadCounter('pkp');
                } else if (appllied_tab === 'pkpnppn') {
                    tablePkpNppn.ajax.reload();
                    setDownloadCounter('pkpnppn');
                } else if (appllied_tab === 'npkp') {
                    tableNonPkp.ajax.reload();
                    setDownloadCounter('npkp');
                } else if (appllied_tab === 'npkpnppn') {
                    tableNonPkpNppn.ajax.reload();
                    setDownloadCounter('npkpnppn');
                } else if (appllied_tab === 'retur') {
                    tableRetur.ajax.reload();
                    setDownloadCounter('retur');
                } else {
                    tablePkp.ajax.reload();
                    tablePkpNppn.ajax.reload();
                    tableNonPkp.ajax.reload();
                    tableNonPkpNppn.ajax.reload();
                    tableRetur.ajax.reload();
                    setDownloadCounter('pkp');
                    setDownloadCounter('pkpnppn');
                    setDownloadCounter('npkp');
                    setDownloadCounter('npkpnppn');
                    setDownloadCounter('retur');
                }
            });

            // Configure daterangepicker
            $('input[name="filter_periode"]').daterangepicker({
                opens: 'left',
                minDate: moment('2024-01-01'),
                locale: {
                    format: 'DD/MM/YYYY'
                }
            })

            /////// Checkbox PKP //////////
            // Event listener untuk checkbox individual
            $('#table-pkp tbody').on('change', '.row-checkbox-pkp', function() {
                const allChecked = $('.row-checkbox-pkp').length === $('.row-checkbox-pkp:checked').length;
                $('#select-all-pkp').prop('checked', allChecked);
                setDownloadCounter('pkp');
            });
            // Event listener untuk checkbox select all
            $('#select-all-pkp').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-pkp').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-pkp').map(function() {
                    return $(this).data('id');
                }).get();
                // Kirim AJAX request untuk memperbarui semua status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('pkp');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('pkp');
                    }
                });
            });
            // Event listener untuk checkbox individual
            $('#table-pkp tbody').on('change', '.row-checkbox-pkp', function() {
                const isChecked = $(this).is(':checked') ? 1 : 0;
                const id = $(this).data('id');

                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                const checkbox = $(this);
                const moveToButton = $('.move-to[data-id="' + id + '"]');
                if (checkbox.is(':checked')) {
                    moveToButton.prop('disabled', true);
                } else {
                    moveToButton.prop('disabled', false);
                }

                // Kirim AJAX request untuk memperbarui status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}", // Tambahkan route untuk update
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('pkp');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('pkp');
                    }
                });
            });

            //////// Checkbox PKP Non-PPN ////////
            // Event listener untuk checkbox individual
            $('#table-pkpnppn tbody').on('change', '.row-checkbox-pkpnppn', function() {
                const allChecked = $('.row-checkbox-pkpnppn').length === $('.row-checkbox-pkpnppn:checked').length;
                $('#select-all-pkpnppn').prop('checked', allChecked);
                setDownloadCounter('pkpnppn');
            });
            // Event listener untuk checkbox select all
            $('#select-all-pkpnppn').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-pkpnppn').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-pkpnppn').map(function() {
                    return $(this).data('id');
                }).get();
                // Kirim AJAX request untuk memperbarui semua status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('pkpnppn');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('pkpnppn');
                    }
                });
            });
            // Event listener untuk checkbox individual
            $('#table-pkpnppn tbody').on('change', '.row-checkbox-pkpnppn', function() {
                const isChecked = $(this).is(':checked') ? 1 : 0;
                const id = $(this).data('id');

                // Kirim AJAX request untuk memperbarui status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}", // Tambahkan route untuk update
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('pkpnppn');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('pkpnppn');
                    }
                });
            });

            //////// Checkbox Non-PKP //////////
            // Event listener untuk checkbox individual
            $('#table-npkp tbody').on('change', '.row-checkbox-npkp', function() {
                const allChecked = $('.row-checkbox-npkp').length === $('.row-checkbox-npkp:checked').length;
                $('#select-all-npkp').prop('checked', allChecked);
                setDownloadCounter('npkp');
            });
            // Event listener untuk checkbox select all
            $('#select-all-npkp').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-npkp').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-npkp').map(function() {
                    return $(this).data('id');
                }).get();

                // Kirim AJAX request untuk memperbarui semua status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('npkp');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('npkp');
                    }
                });
            });
            // Event listener untuk checkbox individual
            $('#table-npkp tbody').on('change', '.row-checkbox-npkp', function() {
                const isChecked = $(this).is(':checked') ? 1 : 0;
                const id = $(this).data('id');
                // Kirim AJAX request untuk memperbarui status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}", // Tambahkan route untuk update
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('npkp');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('npkp');
                    }
                });
            });

            //////// Checkbox Non-PKP Non-PPN ////////
            // Event listener untuk checkbox individual
            $('#table-npkpnppn tbody').on('change', '.row-checkbox-npkpnppn', function() {
                const allChecked = $('.row-checkbox-npkpnppn').length === $('.row-checkbox-npkpnppn:checked').length;
                $('#select-all-npkpnppn').prop('checked', allChecked);
                setDownloadCounter('npkpnppn');
            });
            // Event listener untuk checkbox select all
            $('#select-all-npkpnppn').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-npkpnppn').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-npkpnppn').map(function() {
                    return $(this).data('id');
                }).get();

                // Kirim AJAX request untuk memperbarui semua status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('npkpnppn');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('npkpnppn');
                    }
                });
            });
            // Event listener untuk checkbox individual
            $('#table-npkpnppn tbody').on('change', '.row-checkbox-npkpnppn', function() {
                const isChecked = $(this).is(':checked') ? 1 : 0;
                const id = $(this).data('id');
                // Kirim AJAX request untuk memperbarui status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}", // Tambahkan route untuk update
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('npkpnppn');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('npkpnppn');
                    }
                });
            });

            //////// Checkbox RETUR ////////
            // Event listener untuk checkbox individual
            $('#table-retur tbody').on('change', '.row-checkbox-retur', function() {
                const allChecked = $('.row-checkbox-retur').length === $('.row-checkbox-retur:checked').length;
                $('#select-all-retur').prop('checked', allChecked);
                setDownloadCounter('retur');
            });
            // Event listener untuk checkbox select all
            $('#select-all-retur').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-retur').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-retur').map(function() {
                    return $(this).data('id');
                }).get();

                // Kirim AJAX request untuk memperbarui semua status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('retur');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('retur');
                    }
                });
            });
            // Event listener untuk checkbox individual
            $('#table-retur tbody').on('change', '.row-checkbox-retur', function() {
                const isChecked = $(this).is(':checked') ? 1 : 0;
                const id = $(this).data('id');
                // Kirim AJAX request untuk memperbarui status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}", // Tambahkan route untuk update
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        is_checked: isChecked
                    },
                    success: function(response) {
                        console.log(response.message);
                        setDownloadCounter('retur');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        setDownloadCounter('retur');
                    }
                });
            });

            function reloadTableMoveFromMove2(move_from, move_to) {
                // Mapping tipe ke variabel DataTable
                const tableMap = {
                    pkp: tablePkp,
                    pkpnppn: tablePkpNppn,
                    npkp: tableNonPkp,
                    npkpnppn: tableNonPkpNppn,
                    retur: tableRetur
                };

                // Reload tabel asal
                if (tableMap[move_from]) {
                    tableMap[move_from].ajax.reload();
                    setDownloadCounter(move_from);
                }
                // Reload tabel tujuan jika berbeda
                if (move_to && move_to !== move_from && tableMap[move_to]) {
                    tableMap[move_to].ajax.reload();
                    setDownloadCounter(move_to);
                }
            }

            // Event listener untuk select class move-to
            $(document).on('change', '.move-to', function(){
                console.log('hit move')
                const id = $(this).data('id');
                const move_from = $(this).data('from');
                const move_to = $(this).val();
                // Kirim AJAX request untuk memperbarui status di database
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateMove2') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        move_from: move_from,
                        move_to: move_to
                    },
                    success: function(response) {
                        if (response.status){
                            reloadTableMoveFromMove2(move_from, move_to);
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseText);
                    }
                });
            });

            // AJAX request untuk filter brand
            $.ajax({
                url: "{{ route('pnl.master-data.brands') }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    $.each(data.data, function(index, brand) {
                        $('#filter_brand').append($('<option>', {
                            value: brand.code,
                            text: brand.name
                        }));
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });

            // AJAX request untuk filter depo
            $.ajax({
                url: "{{ route('pnl.master-data.depos') }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    let currentUserDepo = "{{ Auth::user()->depo }}";
                    currentUserDepo = currentUserDepo.split('|');

                    // Check if user has 'all' access
                    let hasAllAccess = currentUserDepo.includes('all');

                    // If user doesn't have 'all' access, filter the depos
                    if (!hasAllAccess) {
                        // Filter data to only show depos the user has access to
                        $.each(data.data, function(index, depo) {
                            if (currentUserDepo.includes(depo.code)) {
                                $('#filter_depo').append($('<option>', {
                                    value: depo.code,
                                    text: depo.code + ' - ' + depo.name
                                }));
                            }
                        });
                    } else {
                        // User has 'all' access, show all depos
                        $.each(data.data, function(index, depo) {
                            $('#filter_depo').append($('<option>', {
                                value: depo.code,
                                text: depo.code + ' - ' + depo.name
                            }));
                        });
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });

            // AJAX request untuk filter pt
            $.ajax({
                url: "{{ route('pnl.master-data.companies') }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    $.each(data.data, function(index, pt) {
                        $('#filter_pt').append($('<option>', {
                            value: pt.code,
                            text: pt.code + ' - ' +pt.name
                        }));
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });

            // Remove filter button since we're auto-filtering
            $('.btn-info').hide();
        });
    </script>
@endsection
