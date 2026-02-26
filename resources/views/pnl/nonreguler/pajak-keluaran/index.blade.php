@extends('layouts.master')

@section('title', 'PNL - Pajak Keluaran - NonReguler')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Pajak Keluaran</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="#">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Non-Reguler</a>
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
                <div class="col-md-4">
                    <div class="form-group form-group-default">
                        <label>BRAND</label>
                        <select class="form-select" id="filter_brand">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group form-group-default">
                        <label>DEPO</label>
                        <select class="form-select" id="filter_depo">
                            <option value="all">--ALL--</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group form-group-default">
                        <label>PERIODE</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="filter_periode" id="filter_periode"
                                placeholder="Pilih Periode" aria-label="Pilih Periode" value="01/01/2024 - 02/01/2024" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-sm btn-info float-end"><i class="fas fa-check"></i> filter</button>
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
                                <a class="nav-link" id="simple-tab-1" data-bs-toggle="tab" href="#tabpanel-nonpkp"
                                    role="tab" aria-controls="tabpanel-nonpkp" aria-selected="false">Non-PKP</a>
                            </li>
                        </ul>
                        <div class="tab-content pt-5" id="tab-content">
                            <div class="tab-pane active" id="tabpanel-pkp" role="tabpanel" aria-labelledby="tabpanel-pkp">
                                <table class="table table-striped table-bordered table-hover" id="table-pkp">
                                    <thead>
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
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="tabpanel-nonpkp" role="tabpanel" aria-labelledby="tabpanel-nonpkp">
                                <table class="table table-striped table-bordered table-hover" id="table-nonpkp">
                                    <thead>
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
    {{-- <script src="{{ asset('assets/js/plugin/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/datatables/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/datatables/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/datatables/vfs_fonts.js') }}"></script> --}}
    <script>
        // Destroy existing DataTable instance if exists
        if ($.fn.DataTable.isDataTable('#table-pkp')) {
            $('#table-pkp').DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable('#table-nonpkp')) {
            $('#table-nonpkp').DataTable().destroy();
        }

        // Initialize new DataTable
        $('#table-pkp').DataTable({
            "pageLength": 50,
            "ordering": false,
            "info": false,
            "paging": true,
            "autoWidth": false,
            "responsive": false,
            "scrollX": true,
            "language": {
                "emptyTable": "Tidak ada data yang tersedia",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "infoEmpty": "",
                "infoFiltered": ""
            },
        });
        $('#table-nonpkp').DataTable({
            "pageLength": 50,
            "searching": false,
            "info": false,
            "paging": true,
            "autoWidth": false,
            "responsive": false,
            "scrollX": true,
            "language": {
                "emptyTable": "Tidak ada data yang tersedia",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "infoEmpty": "",
                "infoFiltered": ""
            }
        });

        $(function() {
            $('input[name="filter_periode"]').daterangepicker({
                opens: 'left',
                minDate: moment('2024-01-01'),
            }, function(start, end, label) {
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') +
                    ' to ' + end
                    .format('YYYY-MM-DD'));
            });
        });
    </script>
@endsection
