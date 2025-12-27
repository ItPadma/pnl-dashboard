<script>
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
                dataSrc: function(json) {
                    pkpnppn_data = json.aaData;
                    return json.aaData;
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
            columns: [{
                    data: 'is_checked',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        const checked = data == 1 ? 'checked' : '';
                        if (row.is_downloaded == 1 && data == 1) {
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
</script>
