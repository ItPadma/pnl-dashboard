<script src="{{ asset('assets/js/plugin/moment/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/daterangepicker/daterangepicker.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>

<script>
    let table;
    let currentInvoiceForNett = null;

    $(document).ready(function() {
        // Initialize filters
        initializeFilters();

        // Initialize DataTable
        initializeDataTable();

        // Event handlers
        $('#btn-apply-filter').on('click', function() {
            console.log('Apply filter button clicked');
            console.log('Table object:', table);

            if (table) {
                table.ajax.reload(null, false); // false = keep current page
                swal('Filter Applied', 'Data sedang dimuat ulang...', 'info', {
                    buttons: false,
                    timer: 1500
                });
            } else {
                console.error('Table not initialized');
                swal('Error', 'Table belum diinisialisasi', 'error');
            }
        });

        $('#btn-export-xlsx').on('click', function() {
            exportData('xlsx');
        });

        $('#btn-export-csv').on('click', function() {
            exportData('csv');
        });

        // Select all retur checkbox
        $('#select-all-retur').on('change', function() {
            $('.retur-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Process nett button
        $('#btn-process-nett').on('click', function() {
            processNett();
        });
    });

    function initializeFilters() {
        // Initialize Select2 for PT
        $('#sp-filter-pt').show();
        $.ajax({
            url: '{{ route('pnl.master-data.companies') }}',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $.each(data.data, function(index, pt) {
                    $('#filter_pt').append($('<option>', {
                        value: pt.code,
                        text: pt.code + ' - ' + pt.name
                    }));
                });
                $('#sp-filter-pt').hide();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                $('#sp-filter-pt').hide();
            }
        });

        // Initialize Select2 for BRAND
        $('#sp-filter-brand').show();
        $.ajax({
            url: '{{ route('pnl.master-data.brands') }}',
            type: 'GET',
            dataType: 'json',
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

        // Initialize Select2 for DEPO with user access filtering
        $('#sp-filter-depo').show();
        $.ajax({
            url: '{{ route('pnl.master-data.depos') }}',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                @php
                    $userInfo = getLoggedInUserInfo();
                    $userDepos = $userInfo ? $userInfo->depo : [];
                    $hasAllAccess = in_array('all', $userDepos);
                @endphp

                let hasAllAccess = {{ $hasAllAccess ? 'true' : 'false' }};
                let currentUserDepo = @json($userDepos);

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
                $('#sp-filter-depo').hide();
            }
        });

        // Initialize Select2
        $('#filter_brand, #filter_depo, #filter_pt').select2({
            allowClear: true,
            width: '100%',
            placeholder: 'Pilih..',
        });

        // Dynamic brand loading when PT changes
        $('#filter_pt').on('change', function() {
            let selectedPt = $(this).val();
            if (selectedPt && selectedPt.length > 0) {
                $.ajax({
                    url: '{{ route('pnl.master-data.brands') }}',
                    type: 'GET',
                    dataType: 'json',
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
                        $('#sp-filter-brand').hide();
                    }
                });
            } else {
                $('#filter_brand').val(null).trigger('change');
            }
        });

        // Initialize daterangepicker for PERIODE
        $('#filter_periode').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY'
            },
            autoUpdateInput: true,
            startDate: moment(),
            endDate: moment()
        });
    }

    function initializeDataTable() {
        table = $('#table-nett-invoice').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('pnl.reguler.nett-invoice.data') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function(d) {
                    let ptVal = $('#filter_pt').val();
                    let brandVal = $('#filter_brand').val();
                    let depoVal = $('#filter_depo').val();

                    d.pt = (ptVal && ptVal.length > 0) ? ptVal : ['all'];
                    d.brand = (brandVal && brandVal.length > 0) ? brandVal : ['all'];
                    d.depo = (depoVal && depoVal.length > 0) ? depoVal : ['all'];
                    d.periode = $('#filter_periode').val();
                    d.tipe = $('#filter_tipe').val();

                    console.log('Filter data:', d);
                }
            },
            columns: [{
                    data: 'kode_pelanggan',
                    name: 'kode_pelanggan'
                },
                {
                    data: 'nama_pelanggan',
                    name: 'nama_pelanggan'
                },
                {
                    data: 'no_invoice',
                    name: 'no_invoice'
                },
                {
                    data: 'nilai_invoice',
                    name: 'nilai_invoice',
                    render: function(data, type, row) {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(data);
                    }
                },
                {
                    data: 'nett_invoice',
                    name: 'nett_invoice',
                    render: function(data, type, row) {
                        if (data == 0) {
                            return '-';
                        }
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(data);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let buttons = `
                            <button class="btn btn-sm btn-info btn-detail" data-invoice="${row.no_invoice}">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                        `;

                        // Only show "Pilih Retur" button if nett_invoice is 0 (not yet netted)
                        if (row.nett_invoice == 0) {
                            buttons += `
                                <button class="btn btn-sm btn-success btn-pilih-retur" 
                                    data-invoice="${row.no_invoice}"
                                    data-kode="${row.kode_pelanggan}"
                                    data-nama="${row.nama_pelanggan}"
                                    data-nilai="${row.nilai_invoice}">
                                    <i class="fas fa-exchange-alt"></i> Pilih Retur
                                </button>
                            `;
                        }

                        return buttons;
                    }
                }
            ],
            language: {
                processing: '<div><i class="fas fa-spinner fa-spin fa-2x"></i><div>Memuat data...</div></div>'
            },
            pageLength: 10,
            order: [
                [2, 'desc']
            ] // Order by no_invoice desc
        });

        // Event delegation for detail button
        $('#table-nett-invoice tbody').on('click', '.btn-detail', function() {
            const noInvoice = $(this).data('invoice');
            showDetail(noInvoice);
        });

        // Event delegation for pilih retur button
        $('#table-nett-invoice tbody').on('click', '.btn-pilih-retur', function() {
            const noInvoice = $(this).data('invoice');
            const rowData = {
                kode_pelanggan: $(this).data('kode'),
                nama_pelanggan: $(this).data('nama'),
                nilai_invoice: $(this).data('nilai')
            };
            showReturModal(noInvoice, rowData);
        });
    }

    function showDetail(noInvoice) {
        $.ajax({
            url: '{{ route('pnl.reguler.nett-invoice.detail') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                no_invoice: noInvoice
            },
            success: function(response) {
                if (response.status) {
                    let html = '';
                    response.data.forEach(function(item) {
                        html += `
                            <tr>
                                <td>${item.kode_produk}</td>
                                <td>${item.qty_pcs}</td>
                                <td>${new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR'}).format(item.dpp)}</td>
                                <td>${new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR'}).format(item.ppn)}</td>
                                <td>${new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR'}).format(item.disc)}</td>
                            </tr>
                        `;
                    });
                    $('#detail-items-tbody').html(html);
                    $('#modal-detail').modal('show');
                } else {
                    swal('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                swal('Error', 'Gagal mengambil detail invoice', 'error');
            }
        });
    }

    function showReturModal(noInvoice, rowData) {
        currentInvoiceForNett = noInvoice;
        $('#selected-invoice').text(noInvoice);
        $('#selected-kode-pelanggan').text(rowData.kode_pelanggan);
        $('#selected-nama-pelanggan').text(rowData.nama_pelanggan);
        $('#selected-nilai-invoice').text(
            new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(rowData.nilai_invoice)
        );

        $.ajax({
            url: '{{ route('pnl.reguler.nett-invoice.retur-list') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                pt: $('#filter_pt').val() || ['all'],
                brand: $('#filter_brand').val() || ['all'],
                depo: $('#filter_depo').val() || ['all'],
                periode: $('#filter_periode').val()
            },
            success: function(response) {
                if (response.status) {
                    let html = '';
                    if (response.data.length === 0) {
                        html =
                            '<tr><td colspan="5" class="text-center">Tidak ada invoice retur yang tersedia</td></tr>';
                    } else {
                        response.data.forEach(function(item) {
                            html += `
                                <tr>
                                    <td><input type="checkbox" class="retur-checkbox" value="${item.no_invoice}"></td>
                                    <td>${item.kode_pelanggan}</td>
                                    <td>${item.nama_pelanggan}</td>
                                    <td>${item.no_invoice}</td>
                                    <td>${new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR'}).format(item.nilai_retur)}</td>
                                </tr>
                            `;
                        });
                    }
                    $('#retur-tbody').html(html);
                    $('#modal-retur').modal('show');
                } else {
                    swal('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                swal('Error', 'Gagal mengambil daftar retur', 'error');
            }
        });
    }

    function processNett() {
        const selectedReturs = [];
        $('.retur-checkbox:checked').each(function() {
            selectedReturs.push($(this).val());
        });

        if (selectedReturs.length === 0) {
            swal('Peringatan', 'Pilih minimal satu invoice retur', 'warning');
            return;
        }

        swal({
            title: 'Konfirmasi',
            text: `Anda akan melakukan proses nett untuk invoice ${currentInvoiceForNett} dengan ${selectedReturs.length} invoice retur. Lanjutkan?`,
            icon: 'warning',
            buttons: {
                cancel: {
                    text: 'Batal',
                    visible: true,
                    className: 'btn btn-secondary'
                },
                confirm: {
                    text: 'Ya, Proses',
                    className: 'btn btn-primary'
                }
            }
        }).then((willProcess) => {
            if (willProcess) {
                $.ajax({
                    url: '{{ route('pnl.reguler.nett-invoice.process') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        no_invoice: currentInvoiceForNett,
                        retur_invoices: selectedReturs
                    },
                    success: function(response) {
                        if (response.status) {
                            swal('Berhasil', response.message, 'success').then(() => {
                                $('#modal-retur').modal('hide');
                                table.ajax.reload();
                            });
                        } else {
                            swal('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message ||
                            'Gagal melakukan proses netting';
                        swal('Error', message, 'error');
                    }
                });
            }
        });
    }

    function exportData(format) {
        swal({
            title: 'Konfirmasi',
            text: 'Anda akan export data nett invoice ke format ' + format.toUpperCase() + '. Lanjutkan?',
            icon: 'info',
            buttons: {
                cancel: {
                    text: 'Batal',
                    visible: true,
                    className: 'btn btn-secondary'
                },
                confirm: {
                    text: 'Ya, Export',
                    className: 'btn btn-success'
                }
            }
        }).then((willExport) => {
            if (willExport) {
                window.location.href = '{{ route('pnl.reguler.nett-invoice.export') }}?format=' + format;
                swal('Berhasil', 'Data sedang diexport...', 'success');
            }
        });
    }
</script>
