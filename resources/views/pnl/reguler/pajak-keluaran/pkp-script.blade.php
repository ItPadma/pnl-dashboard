<script>
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
                dataSrc: function(json) {
                    pkp_data = json.aaData;
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
            columnDefs: [{
                    targets: [0, ],
                    className: 'text-center',
                    width: '10%'
                },
                {
                    targets: [1],
                    width: '100px'
                },
                {
                    targets: 1,
                    width: '100px'
                }, // UBAH TIPE
                {
                    targets: 2,
                    width: '120px'
                }, // NO INVOICE
                {
                    targets: 3,
                    width: '120px'
                }, // NO DO
                {
                    targets: 4,
                    width: '120px'
                }, // KODE PRODUK
                {
                    targets: 5,
                    width: '80px'
                }, // QTY
                {
                    targets: 6,
                    width: '120px'
                }, // HARGA SATUAN
                {
                    targets: 7,
                    width: '80px'
                }, // DISC
                {
                    targets: 8,
                    width: '120px'
                }, // HARGA TOTAL
                {
                    targets: 9,
                    width: '100px'
                }, // DPP
                {
                    targets: 10,
                    width: '100px'
                }, // PPN
                {
                    targets: 11,
                    width: '120px'
                }, // TGL FAKTUR PAJAK
                {
                    targets: 12,
                    width: '80px'
                }, // DEPO
                {
                    targets: 13,
                    width: '100px'
                }, // AREA
                {
                    targets: 14,
                    width: '200px'
                }, // NAMA PRODUK
                {
                    targets: 15,
                    width: '150px'
                }, // NPWP CUSTOMER
                {
                    targets: 16,
                    width: '100px'
                }, // CUSTOMER ID
                {
                    targets: 17,
                    width: '200px'
                }, // NAMA CUSTOMER
                {
                    targets: 18,
                    width: '250px'
                }, // ALAMAT
                {
                    targets: 19,
                    width: '100px'
                }, // TYPE PAJAK
                {
                    targets: 20,
                    width: '80px'
                }, // SATUAN
                {
                    targets: 21,
                    width: '200px'
                }, // NAMA SESUAI NPWP
                {
                    targets: 22,
                    width: '250px'
                }, // ALAMAT NPWP
                {
                    targets: 23,
                    width: '100px'
                }, // NO TELEPON
                {
                    targets: 24,
                    width: '100px'
                }, // NO FP
                {
                    targets: 25,
                    width: '100px'
                }, // BRAND
                {
                    targets: 26,
                    width: '100px'
                }, // TYPE JUAL
                {
                    targets: 27,
                    width: '120px'
                }, // KODE JENIS FP
                {
                    targets: 28,
                    width: '100px'
                }, // STATUS FP
                {
                    targets: 29,
                    width: '100px'
                }, // NIK
                {
                    targets: 30,
                    width: '100px'
                }, // DPP LAIN
                {
                    targets: 31,
                    width: '120px'
                }, // ID TKU PEMBELI
                {
                    targets: 32,
                    width: '100px'
                } // JENIS
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
</script>
