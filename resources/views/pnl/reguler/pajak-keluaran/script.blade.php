@section('script')
    <script src="{{ asset('assets/js/plugin/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
    <script>

        let tablePkp;
        let tablePkpNppn;
        let tableNonPkp;
        let tableNonPkpNppn;
        let tableRetur;

        $.fn.dataTable.ext.errMode = 'none';

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
                    },
                    async: true,
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                        toastr.error('Gagal memuat data. Silakan coba lagi.', 'Error');
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
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
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
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
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
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
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
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
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
                        data: 'id_tku_pembeli',
                        name: 'id_tku_pembeli'
                    },
                    {
                        data: 'barang_jasa',
                        name: 'barang_jasa'
                    }
                ],
                columnDefs: [
                    {
                        targets: [0, ],
                        className: 'text-center',
                        width: '10%'
                    },
                    {
                        targets: [1],
                        width: '100px'
                    },
                    { targets: 1, width: '100px' },  // UBAH TIPE
                    { targets: 2, width: '120px' },  // NO INVOICE
                    { targets: 3, width: '120px' },  // NO DO
                    { targets: 4, width: '120px' },  // KODE PRODUK
                    { targets: 5, width: '80px' },   // QTY
                    { targets: 6, width: '120px' },  // HARGA SATUAN
                    { targets: 7, width: '80px' },   // DISC
                    { targets: 8, width: '120px' },  // HARGA TOTAL
                    { targets: 9, width: '100px' },  // DPP
                    { targets: 10, width: '100px' }, // PPN
                    { targets: 11, width: '120px' }, // TGL FAKTUR PAJAK
                    { targets: 12, width: '80px' },  // DEPO
                    { targets: 13, width: '100px' }, // AREA
                    { targets: 14, width: '200px' }, // NAMA PRODUK
                    { targets: 15, width: '150px' }, // NPWP CUSTOMER
                    { targets: 16, width: '100px' }, // CUSTOMER ID
                    { targets: 17, width: '200px' }, // NAMA CUSTOMER
                    { targets: 18, width: '250px' }, // ALAMAT
                    { targets: 19, width: '100px' }, // TYPE PAJAK
                    { targets: 20, width: '80px' },  // SATUAN
                    { targets: 21, width: '200px' }, // NAMA SESUAI NPWP
                    { targets: 22, width: '250px' }, // ALAMAT NPWP
                    { targets: 23, width: '100px' }, // NO TELEPON
                    { targets: 24, width: '100px' }, // NO FP
                    { targets: 25, width: '100px' }, // BRAND
                    { targets: 26, width: '100px' }, // TYPE JUAL
                    { targets: 27, width: '120px' }, // KODE JENIS FP
                    { targets: 28, width: '100px' }, // STATUS FP
                    { targets: 29, width: '100px' }, // NIK
                    { targets: 30, width: '100px' }, // DPP LAIN
                    { targets: 31, width: '120px' }, // ID TKU PEMBELI
                    { targets: 32, width: '100px' }  // JENIS
                ],
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
                autoWidth: false,
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
                    var api = this.api();

                    // Remove thead and tfoot from scrollBody
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();

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
                    api.columns.adjust();
                },
                ajaxComplete: function() {
                    setDownloadCounter('pkp');
                },
                drawCallback: function(settings) {
                    var api = this.api();

                    // Remove thead and tfoot from scrollBody
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();

                    setDownloadCounter('pkp');
                },
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
                    },
                    async: true,
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                        toastr.error('Gagal memuat data. Silakan coba lagi.', 'Error');
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
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = row.is_checked == 1 ? '' : 'disabled';
                            return `<select id="move-to-${row.id}" class="form-select move-to" data-id="${row.id}" data-from="pkpnppn" ${checked}>
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
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
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
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
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
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
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
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
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
                // autoWidth: true,
                // scrollX: true,
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
                    },
                    async: true,
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                        toastr.error('Gagal memuat data. Silakan coba lagi.', 'Error');
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
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = row.is_checked == 1 ? '' : 'disabled';
                            return `<select id="move-to-${row.id}" class="form-select move-to" data-id="${row.id}" data-from="npkp" ${checked}>
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
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
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
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
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
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
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
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
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
                // scrollX: true,
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
                    },
                    async: true,
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                        toastr.error('Gagal memuat data. Silakan coba lagi.', 'Error');
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
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = row.is_checked == 1 ? '' : 'disabled';
                            return `<select id="move-to-${row.id}" class="form-select move-to" data-id="${row.id}" data-from="npkpnppn" ${checked}>
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
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
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
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
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
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
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
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
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
                // scrollX: true,
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
                    },
                    async: true,
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                        toastr.error('Gagal memuat data. Silakan coba lagi.', 'Error');
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
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const checked = row.is_checked == 1 ? '' : 'disabled';
                            return `<select id="move-to-${row.id}" class="form-select move-to" data-id="${row.id}" data-from="retur" ${checked}>
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
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'nama_customer_sistem',
                        name: 'nama_customer_sistem'
                    },
                    {
                        data: 'npwp_customer',
                        name: 'npwp_customer'
                    },
                    {
                        data: 'no_do',
                        name: 'no_do'
                    },
                    {
                        data: 'no_invoice',
                        name: 'no_invoice'
                    },
                    {
                        data: 'kode_produk',
                        name: 'kode_produk'
                    },
                    {
                        data: 'nama_produk',
                        name: 'nama_produk'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
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
                        data: 'hargatotal_sblm_ppn',
                        name: 'hargatotal_sblm_ppn'
                    },
                    {
                        data: 'disc',
                        name: 'disc'
                    },
                    {
                        data: 'dpp',
                        name: 'dpp'
                    },
                    {
                        data: 'dpp_lain',
                        name: 'dpp_lain'
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
                        data: 'alamat_sistem',
                        name: 'alamat_sistem'
                    },
                    {
                        data: 'type_pajak',
                        name: 'type_pajak'
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
                        data: 'depo',
                        name: 'depo'
                    },
                    {
                        data: 'area',
                        name: 'area'
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
                // scrollX: true,
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
                    tipe: tipe,
                    chstatus: 'checked-ready2download'
                },
                async: true,
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
            // Event listener untuk checkbox select all
            $('#select-all-pkp').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-pkp').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-pkp').map(function() {
                    return $(this).data('id');
                }).get();
                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                for (const id of ids) {
                    toggleMoveToSelect(id, isChecked);
                }
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
                const allChecked = $('.row-checkbox-pkp').length === $('.row-checkbox-pkp:checked').length;
                $('#select-all-pkp').prop('checked', allChecked);

                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                toggleMoveToSelect(id, isChecked);

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
            // Event listener untuk checkbox select all
            $('#select-all-pkpnppn').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-pkpnppn').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-pkpnppn').map(function() {
                    return $(this).data('id');
                }).get();
                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                for (const id of ids) {
                    toggleMoveToSelect(id, isChecked);
                }
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
                const allChecked = $('.row-checkbox-pkpnppn').length === $('.row-checkbox-pkpnppn:checked').length;
                $('#select-all-pkpnppn').prop('checked', allChecked);
                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                toggleMoveToSelect(id, isChecked);
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
            // Event listener untuk checkbox select all
            $('#select-all-npkp').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-npkp').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-npkp').map(function() {
                    return $(this).data('id');
                }).get();
                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                for (const id of ids) {
                    toggleMoveToSelect(id, isChecked);
                }
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
                const allChecked = $('.row-checkbox-npkp').length === $('.row-checkbox-npkp:checked').length;
                $('#select-all-npkp').prop('checked', allChecked);
                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                toggleMoveToSelect(id, isChecked);
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
            // Event listener untuk checkbox select all
            $('#select-all-npkpnppn').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-npkpnppn').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-npkpnppn').map(function() {
                    return $(this).data('id');
                }).get();
                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                for (const id of ids) {
                    toggleMoveToSelect(id, isChecked);
                }
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
                const allChecked = $('.row-checkbox-npkpnppn').length === $('.row-checkbox-npkpnppn:checked').length;
                $('#select-all-npkpnppn').prop('checked', allChecked);
                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                toggleMoveToSelect(id, isChecked);
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
            // Event listener untuk checkbox select all
            $('#select-all-retur').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox-retur').prop('checked', isChecked);
                // Ambil semua ID dari checkbox
                const ids = $('.row-checkbox-retur').map(function() {
                    return $(this).data('id');
                }).get();
                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                for (const id of ids) {
                    toggleMoveToSelect(id, isChecked);
                }
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
                const allChecked = $('.row-checkbox-retur').length === $('.row-checkbox-retur:checked').length;
                $('#select-all-retur').prop('checked', allChecked);
                // Class move-to dengan data-id bersangkutan berubah menjadi disabled
                toggleMoveToSelect(id, isChecked);
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
                const id = $(this).data('id');
                const move_from = $(this).data('from');
                const move_to = $(this).val();
                if (move_from && move_to) {
                    $('.apply-move-to[data-for="' + move_from + '"]').prop('disabled', false);
                }
            });

            // Event listener untuk tombol apply move-to
            $(document).on('click', '.apply-move-to', function() {
                const move_from = $(this).data('for');
                applyMoveTo(move_from);
            });

            // Fungsi untuk mengaktifkan/menonaktifkan select move-to
            function toggleMoveToSelect(id, isChecked) {
                const moveToSelect = $('.move-to[data-id="' + id + '"]');
                if (isChecked) {
                    moveToSelect.prop('disabled', false);
                } else {
                    moveToSelect.prop('disabled', true);
                }
            }

            // Event listener untuk tombol apply move-to
            function applyMoveTo(from) {
                const move_from = from;
                // ambil nilai dari select dengan id move-to yang saat ini aktif (tidak disabled)
                const move_to = $('select[id*="move-to-"]:not([disabled]):visible').val();
                if (move_from && move_to) {
                    // Ambil semua ID dari checkbox yang dicentang
                    const ids = $('.row-checkbox-' + move_from).filter(':checked').map(function() {
                        return $(this).data('id');
                    }).get();

                    if (ids.length > 0) {
                        // Kirim AJAX request untuk memperbarui status di database
                        $.ajax({
                            url: "{{ route('pnl.reguler.pajak-keluaran.updateMove2') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: ids,
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
                    } else {
                        toastr.warning('Tidak ada data yang dipilih untuk dipindahkan.');
                    }
                } else {
                    toastr.warning('Silakan pilih tipe pajak dan tujuan pemindahan.');
                }
            }

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
                    $('#sp-filter-brand').hide();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $('#sp-filter-brand').hide();
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

                    $('#sp-filter-depo').hide();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    toastr.error('Gagal memuat data DEPO.');
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
                    $('#sp-filter-pt').hide();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    toastr.error('Gagal memuat data PT.');
                }
            });

            // Remove filter button since we're auto-filtering
            $('.btn-info').hide();

            // select2 initialization, set default values to all
            $('#filter_brand, #filter_depo, #filter_pt').select2({
                allowClear: true,
                width: '100%',
                placeholder: 'Pilih..',
            });

            // get brand when filter_pt (multiple value from select2) changes
            $('#filter_pt').on('change', function() {
                let selectedPt = $(this).val();
                if (selectedPt) {
                    $.ajax({
                        url: "{{ route('pnl.master-data.brands') }}",
                        type: "GET",
                        dataType: "json",
                        data: {
                            companies: selectedPt
                        },
                        beforeSend: function() {
                            $('#filter_brand').empty().append($('<option>', {
                                value: 'all',
                                text: '--ALL--',
                                selected: false
                            }));
                            $('#sp-filter-brand').show();
                        },
                        success: function(data) {
                            $.each(data.data, function(index, brand) {
                                $('#filter_brand').append($('<option>', {
                                    value: brand.code,
                                    text: brand.name
                                }));
                            });
                            $('#filter_brand').val(null).trigger('change');
                            $('#sp-filter-brand').hide();
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            toastr.error('Gagal memuat data brand.');
                            $('#sp-filter-brand').hide();
                        }
                    });
                } else {
                    $('#filter_brand').val(null).trigger('change');
                }
            });
        });
    </script>
@endsection
