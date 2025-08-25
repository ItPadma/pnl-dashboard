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
        }

        /* .tbl-container scroll horizontal dan vertical */
        .tbl-container {
            overflow-x: auto;
            overflow-y: auto;
            width: 100%;
            max-height: 600px;
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
                        <label for="filter_pt">PT <div class="spinner-border spinner-border-sm " id="sp-filter-pt" role="status"><span class="visually-hidden">Loading...</span></div></label>
                        <select class="form-select" id="filter_pt" multiple="multiple">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_brand">BRAND <div class="spinner-border spinner-border-sm " id="sp-filter-brand" role="status"><span class="visually-hidden">Loading...</span></div></label>
                        <select class="form-select" id="filter_brand" multiple="multiple">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xm-12 col-sm-12">
                    <div class="form-group form-group-default">
                        <label for="filter_depo">DEPO <div class="spinner-border spinner-border-sm " id="sp-filter-depo" role="status"><span class="visually-hidden">Loading...</span></div></label>
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
                                placeholder="Pilih Periode" aria-label="Pilih Periode" value="01/01/2025 - 02/01/2025" />
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
                                <div class="tbl-container">
                                    <table class="table table-sm table-bordered table-hover table-fixed" id="table-pkp">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-pkp"></th> <!-- Checkbox untuk select all -->
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to" data-for="pkp" disabled>
                                                    <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Customer ID" data-column="1"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="NIK" data-column="2"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Nama Customer" data-column="3"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="NPWP Customer" data-column="4"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No DO" data-column="5"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No Invoice" data-column="6"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Kode Produk" data-column="7"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Nama Produk" data-column="8"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Satuan" data-column="9"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Qty" data-column="10"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Harga Satuan" data-column="11"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Harga Total" data-column="12"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Disc" data-column="13"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="DPP" data-column="14"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="DPP Lain" data-column="15"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="PPN" data-column="16"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Tgl Faktur Pajak" data-column="17"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Alamat" data-column="18"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Tipe Pajak" data-column="19"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Nama Sesuai NPWP" data-column="20"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Alamat NPWP" data-column="21"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No Telepon" data-column="22"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="No FP" data-column="23"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Brand" data-column="24"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Depo" data-column="25"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Area" data-column="26"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Type Jual" data-column="27"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Kode Jenis FP" data-column="28"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Status FP" data-column="29"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="ID TKU Pembeli" data-column="30"></th>
                                                <th><input type="text" class="form-control form-control-sm column-filter-pkp"
                                                        placeholder="Jenis" data-column="31"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
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
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover" id="table-pkpnppn">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-pkpnppn"></th> <!-- Checkbox untuk select all -->
                                                <th>UBAH TIPE</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to" data-for="pkpnppn" disabled>
                                                    <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
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
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover" id="table-npkp">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-npkp"></th> <!-- Checkbox untuk select all -->
                                                <th>UBAH TIPE</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to" data-for="npkp" disabled>
                                                    <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
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
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover" id="table-npkpnppn">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-npkpnppn"></th> <!-- Checkbox untuk select all -->
                                                <th>UBAH TIPE</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to" data-for="npkpnppn" disabled>
                                                    <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
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
                                <div class="tbl-container">
                                    <table class="table table-sm table-striped table-bordered table-hover" id="table-retur">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select-all-retur"></th> <!-- Checkbox untuk select all -->
                                                <th>UBAH TIPE</th>
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
                                                <th><button class="btn btn-sm btn-primary apply-move-to" data-for="retur" disabled>
                                                    <i class="fas fa-check fa-fw"></i> Terapkan</button></th>
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
    </div>
@endsection

@include('pnl.reguler.pajak-keluaran.script')
