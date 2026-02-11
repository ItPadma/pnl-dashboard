<script>
    // Function to format child row for Retur (product details)
    function formatChildRowRetur(d) {
        if (!d.products || d.products.length === 0) {
            return '<div style="padding:6px 12px;font-size:0.72rem;color:#6b7280;">Tidak ada detail produk</div>';
        }
        var fmt = function(v) {
            return parseFloat(v || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };
        var html = '<div style="padding:4px 8px;">';
        html += '<div style="font-size:0.68rem;color:#6b7280;margin-bottom:2px;font-weight:600;">' + d.products.length +
            ' produk</div>';
        html += '<table class="child-details" style="width:100%;border-collapse:collapse;font-size:0.75rem;">';
        html +=
            '<thead><tr><th>KODE</th><th>NAMA PRODUK</th><th>SAT</th><th style="text-align:right">QTY</th><th style="text-align:right">HARGA SAT</th><th style="text-align:right">HARGA TOTAL</th><th style="text-align:right">DISC</th><th style="text-align:right">DPP</th><th style="text-align:right">DPP LAIN</th><th style="text-align:right">PPN</th></tr></thead><tbody>';
        d.products.forEach(function(p) {
            html += '<tr>';
            html += '<td style="white-space:nowrap">' + (p.kode_produk || '-') + '</td>';
            html += '<td>' + (p.nama_produk || '-') + '</td>';
            html += '<td>' + (p.satuan || '-') + '</td>';
            html += '<td style="text-align:right">' + (p.qty_pcs || 0) + '</td>';
            html += '<td style="text-align:right">' + fmt(p.hargasatuan_sblm_ppn) + '</td>';
            html += '<td style="text-align:right">' + fmt(p.hargatotal_sblm_ppn) + '</td>';
            html += '<td style="text-align:right">' + fmt(p.disc) + '</td>';
            html += '<td style="text-align:right">' + fmt(p.dpp) + '</td>';
            html += '<td style="text-align:right">' + fmt(p.dpp_lain) + '</td>';
            html += '<td style="text-align:right">' + fmt(p.ppn) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        return html;
    }
</script>
<script>
    // Initialize DataTable for Retur (DB - Grouped by Invoice)
    function initializeDataTableReturDb() {
        if ($.fn.DataTable.isDataTable('#table-retur')) {
            $('#table-retur').DataTable().destroy();
        }

        tableReturDb = $('#table-retur').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ $dtDataRoute }}",
                type: "POST",
                data: function(d) {
                    d.pt = $('#filter_pt').val();
                    d.brand = $('#filter_brand').val();
                    d.depo = $('#filter_depo').val();
                    d.periode = $('#filter_periode').val();
                    d.tipe = 'retur';
                    d.chstatus = $('#filter_chstatus').val();
                    d.grouped = true;
                    return d;
                },
                dataSrc: function(json) {
                    retur_data_db = json.aaData;
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
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: '<i class="fas fa-plus-circle"></i>',
                    width: '30px'
                },
                {
                    data: 'is_checked',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        const checked = data == 1 ? 'checked' : '';
                        if (row.is_downloaded == 1 && data == 1) {
                            return '<div style="display: flex; align-items: center; gap: 5px;"><i class="fas fa-fw fa-check text-secondary"></i><i class="fas fa-fw fa-download text-secondary"></i></div>';
                        }
                        return `<input type="checkbox" class="row-checkbox-retur" data-invoice="${row.no_invoice}" ${checked}>`;
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
                    data: 'tgl_faktur_pajak',
                    name: 'tgl_faktur_pajak'
                },
                {
                    data: 'total_hargatotal',
                    name: 'total_hargatotal',
                    render: function(data) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    data: 'total_disc',
                    name: 'total_disc',
                    render: function(data) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    data: 'total_dpp',
                    name: 'total_dpp',
                    render: function(data) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    data: 'total_dpp_lain',
                    name: 'total_dpp_lain',
                    render: function(data) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                },
                {
                    data: 'total_ppn',
                    name: 'total_ppn',
                    render: function(data) {
                        return parseFloat(data || 0).toFixed(2);
                    }
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
                    data: 'depo',
                    name: 'depo'
                },
                {
                    data: 'area',
                    name: 'area'
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
                    data: 'id_tku_pembeli',
                    name: 'id_tku_pembeli'
                },
                {
                    data: 'barang_jasa',
                    name: 'barang_jasa'
                }
            ],
            columnDefs: [{
                    targets: [0],
                    className: 'text-center',
                    width: '30px'
                },
                {
                    targets: [1],
                    className: 'text-center',
                    width: '50px'
                }
            ],
            order: [
                [2, 'desc']
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
                $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                api.columns().every(function() {
                    var column = this;
                    var input = $('.column-filter-retur[data-column="' + (column.index()) + '"]');
                    input.on('keyup change clear', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
                api.columns.adjust();
            },
            drawCallback: function(settings) {
                var api = this.api();
                $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                setDownloadCounter('retur');
                showCheckedSummaryDb('retur', retur_data_db);
            },
        });

        // Add event listener for opening and closing details
        $('#table-retur tbody').on('click', 'td.dt-control', function() {
            var tr = $(this).closest('tr');
            var row = tableReturDb.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                $(this).html('<i class="fas fa-plus-circle"></i>');
            } else {
                row.child(formatChildRowRetur(row.data())).show();
                tr.addClass('shown');
                $(this).html('<i class="fas fa-minus-circle"></i>');
            }
        });
    }
</script>
