<script>
    $(document).ready(function() {
        const tableInitialized = {
            pkp: false,
            pkpnppn: false,
            npkp: false,
            npkpnppn: false,
            retur: false,
            nonstandar: false,
            pembatalan: false,
            koreksi: false,
            pending: false
        };

        const initTableIfNeeded = (tipe) => {
            if (tableInitialized[tipe]) {
                return;
            }
            if (tipe === 'pkp') {
                initializeDataTablePkpDb();
            } else if (tipe === 'pkpnppn') {
                initializeDataTablePkpNppnDb();
            } else if (tipe === 'npkp') {
                initializeDataTableNonPkpDb();
            } else if (tipe === 'npkpnppn') {
                initializeDataTableNonPkpNppnDb();
            } else if (tipe === 'retur') {
                initializeDataTableReturDb();
            } else if (tipe === 'nonstandar') {
                initializeDataTableNonStandarDb();
            } else if (tipe === 'pembatalan') {
                initializeDataTablePembatalanDb();
            } else if (tipe === 'koreksi') {
                initializeDataTableKoreksiDb();
            } else if (tipe === 'pending') {
                initializeDataTablePendingDb();
            }
            tableInitialized[tipe] = true;
        };
        // Tables will only be initialized when filter button is clicked
        // No auto-initialization on page load

        // Event listener untuk tab switching - only adjust columns if already initialized
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('href');
            if (target === '#tabpanel-pkp') {
                if (tableInitialized['pkp'] && tablePkpDb) {
                    tablePkpDb.columns.adjust();
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                    showCheckedSummaryDb('pkp', pkp_data_db);
                }
            }
            if (target === '#tabpanel-pkpnppn') {
                if (tableInitialized['pkpnppn'] && tablePkpDbNppn) {
                    tablePkpDbNppn.columns.adjust();
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                    showCheckedSummaryDb('pkpnppn', pkpnppn_data_db);
                }
            }
            if (target === '#tabpanel-npkp') {
                if (tableInitialized['npkp'] && tableNonPkpDb) {
                    tableNonPkpDb.columns.adjust();
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                    showCheckedSummaryDb('npkp', npkp_data_db);
                }
            }
            if (target === '#tabpanel-npkpnppn') {
                if (tableInitialized['npkpnppn'] && tableNonPkpDbNppn) {
                    tableNonPkpDbNppn.columns.adjust();
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                    showCheckedSummaryDb('npkpnppn', npkpnppn_data_db);
                }
            }
            if (target === '#tabpanel-retur') {
                if (tableInitialized['retur'] && tableReturDb) {
                    tableReturDb.columns.adjust();
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                    showCheckedSummaryDb('retur', retur_data_db);
                }
            }
            if (target === '#tabpanel-nonstandar') {
                if (tableInitialized['nonstandar'] && tableNonStandarDb) {
                    tableNonStandarDb.columns.adjust();
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                    showCheckedSummaryDb('nonstandar', nonstandar_data_db);
                }
            }
            if (target === '#tabpanel-pembatalan') {
                if (tableInitialized['pembatalan'] && tablePembatalanDb) {
                    tablePembatalanDb.columns.adjust();
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                    showCheckedSummaryDb('pembatalan', pembatalan_data_db);
                }
            }
            if (target === '#tabpanel-koreksi') {
                if (tableInitialized['koreksi'] && tableKoreksiDb) {
                    tableKoreksiDb.columns.adjust();
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                    showCheckedSummaryDb('koreksi', koreksi_data_db);
                }
            }
            if (target === '#tabpanel-pending') {
                if (tableInitialized['pending'] && tablePendingDb) {
                    tablePendingDb.columns.adjust();
                    $('.dataTables_scrollBody thead, .dataTables_scrollBody tfoot').remove();
                    showCheckedSummaryDb('pending', pending_data_db);
                }
            }
        });

        // Add change event listeners to filters
        $('#btn-apply-filter').on('click', function() {
            let applied_tab = $('#inputGroupFilter').val();

            const reloadTable = (tipe) => {
                initTableIfNeeded(tipe);
                if (tipe === 'pkp' && tablePkpDb) {
                    tablePkpDb.ajax.reload();
                } else if (tipe === 'pkpnppn' && tablePkpDbNppn) {
                    tablePkpDbNppn.ajax.reload();
                } else if (tipe === 'npkp' && tableNonPkpDb) {
                    tableNonPkpDb.ajax.reload();
                } else if (tipe === 'npkpnppn' && tableNonPkpDbNppn) {
                    tableNonPkpDbNppn.ajax.reload();
                } else if (tipe === 'retur' && tableReturDb) {
                    tableReturDb.ajax.reload();
                } else if (tipe === 'nonstandar' && tableNonStandarDb) {
                    tableNonStandarDb.ajax.reload();
                } else if (tipe === 'pembatalan' && tablePembatalanDb) {
                    tablePembatalanDb.ajax.reload();
                } else if (tipe === 'koreksi' && tableKoreksiDb) {
                    tableKoreksiDb.ajax.reload();
                } else if (tipe === 'pending' && tablePendingDb) {
                    tablePendingDb.ajax.reload();
                }

                if (tableInitialized[tipe]) {
                    setDownloadCounter(tipe);
                }
            };

            if (applied_tab == 'pkp') {
                reloadTable('pkp');
            } else if (applied_tab === 'pkpnppn') {
                reloadTable('pkpnppn');
            } else if (applied_tab === 'npkp') {
                reloadTable('npkp');
            } else if (applied_tab === 'npkpnppn') {
                reloadTable('npkpnppn');
            } else if (applied_tab === 'retur') {
                reloadTable('retur');
            } else if (applied_tab === 'nonstandar') {
                reloadTable('nonstandar');
            } else if (applied_tab === 'pembatalan') {
                reloadTable('pembatalan');
            } else if (applied_tab === 'koreksi') {
                reloadTable('koreksi');
            } else if (applied_tab === 'pending') {
                reloadTable('pending');
            } else {
                ['pkp', 'pkpnppn', 'npkp', 'npkpnppn', 'retur', 'nonstandar', 'pembatalan', 'koreksi',
                    'pending'
                ].forEach(reloadTable);
            }
        });

        // Variable to store available dates
        let availableDates = [];

        // Function to fetch available dates from server
        function fetchAvailableDates() {
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.available-dates') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    pt: $('#filter_pt').val(),
                    brand: $('#filter_brand').val(),
                    depo: $('#filter_depo').val()
                },
                success: function(response) {
                    if (response.status) {
                        availableDates = response.data;
                        initializeDateRangePicker();
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching available dates:', xhr.responseText);
                }
            });
        }

        // Function to initialize daterangepicker
        function initializeDateRangePicker() {
            const $periodeInput = $('input[name="filter_periode"]');

            if ($periodeInput.data('daterangepicker')) {
                $periodeInput.off('apply.daterangepicker');
                $periodeInput.data('daterangepicker').remove();
            }

            $periodeInput.daterangepicker({
                showDropdowns: true,
                autoApply: true,
                opens: 'left',
                startDate: moment(),
                endDate: moment(),
                minDate: moment('2024-01-01'),
                maxDate: moment().add(1, 'year'),
                locale: {
                    format: 'DD/MM/YYYY',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                        'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ]
                },
                isCustomDate: function(date) {
                    const dateStr = date.format('YYYY-MM-DD');
                    if (availableDates.includes(dateStr)) {
                        return 'has-data';
                    }
                    return '';
                }
            }, function(start, end, label) {
                console.log('Date selected:', start.format('DD/MM/YYYY'));
            });

            // Add event listener for date change
            $periodeInput.on('apply.daterangepicker', function(ev, picker) {
                if (tableInitialized.pkp && tablePkpDb) {
                    tablePkpDb.ajax.reload();
                    setDownloadCounter('pkp');
                }
                if (tableInitialized.pkpnppn && tablePkpDbNppn) {
                    tablePkpDbNppn.ajax.reload();
                    setDownloadCounter('pkpnppn');
                }
                if (tableInitialized.npkp && tableNonPkpDb) {
                    tableNonPkpDb.ajax.reload();
                    setDownloadCounter('npkp');
                }
                if (tableInitialized.npkpnppn && tableNonPkpDbNppn) {
                    tableNonPkpDbNppn.ajax.reload();
                    setDownloadCounter('npkpnppn');
                }
                if (tableInitialized.retur && tableReturDb) {
                    tableReturDb.ajax.reload();
                    setDownloadCounter('retur');
                }
                if (tableInitialized.nonstandar && tableNonStandarDb) {
                    tableNonStandarDb.ajax.reload();
                    setDownloadCounter('nonstandar');
                }
                if (tableInitialized.pembatalan && tablePembatalanDb) {
                    tablePembatalanDb.ajax.reload();
                    setDownloadCounter('pembatalan');
                }
                if (tableInitialized.koreksi && tableKoreksiDb) {
                    tableKoreksiDb.ajax.reload();
                    setDownloadCounter('koreksi');
                }
                if (tableInitialized.pending && tablePendingDb) {
                    tablePendingDb.ajax.reload();
                    setDownloadCounter('pending');
                }
            });
        }

        // Initialize daterangepicker on page load
        initializeDateRangePicker();

        // Fetch available dates on page load
        fetchAvailableDates();

        // Update available dates when filters change
        $('#filter_pt, #filter_brand, #filter_depo').on('change', function() {
            fetchAvailableDates();
        });

        /////// Checkbox PKP //////////
        // Event listener untuk checkbox select all
        $('#select-all-pkp').on('change', function() {
            handleSelectAllDb('pkp', this);
        });

        // Event listener untuk checkbox individual
        $('#table-pkp tbody').on('change', '.row-checkbox-pkp', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const invoice = $(this).data('invoice');
            const allChecked = $('.row-checkbox-pkp').length === $('.row-checkbox-pkp:checked').length;
            $('#select-all-pkp').prop('checked', allChecked);

            // Kirim AJAX request untuk memperbarui status di database
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice: invoice,
                    is_checked: isChecked
                },
                success: function(response) {
                    setDownloadCounter('pkp');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('pkp');
                }
            });
            showCheckedSummaryDb('pkp', pkp_data_db);
        });

        //////// Checkbox PKP Non-PPN ////////
        $('#select-all-pkpnppn').on('change', function() {
            handleSelectAllDb('pkpnppn', this);
        });
        $('#table-pkpnppn tbody').on('change', '.row-checkbox-pkpnppn', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const invoice = $(this).data('invoice');
            const allChecked = $('.row-checkbox-pkpnppn').length === $('.row-checkbox-pkpnppn:checked')
                .length;
            $('#select-all-pkpnppn').prop('checked', allChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice: invoice,
                    is_checked: isChecked
                },
                success: function(response) {
                    setDownloadCounter('pkpnppn');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('pkpnppn');
                }
            });
            showCheckedSummaryDb('pkpnppn', pkpnppn_data_db);
        });

        //////// Checkbox Non-PKP //////////
        $('#select-all-npkp').on('change', function() {
            handleSelectAllDb('npkp', this);
        });
        $('#table-npkp tbody').on('change', '.row-checkbox-npkp', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const invoice = $(this).data('invoice');
            const allChecked = $('.row-checkbox-npkp').length === $('.row-checkbox-npkp:checked')
                .length;
            $('#select-all-npkp').prop('checked', allChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice: invoice,
                    is_checked: isChecked
                },
                success: function(response) {
                    setDownloadCounter('npkp');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('npkp');
                }
            });
            showCheckedSummaryDb('npkp', npkp_data_db);
        });

        //////// Checkbox Non-PKP Non-PPN ////////
        $('#select-all-npkpnppn').on('change', function() {
            handleSelectAllDb('npkpnppn', this);
        });
        $('#table-npkpnppn tbody').on('change', '.row-checkbox-npkpnppn', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const invoice = $(this).data('invoice');
            const allChecked = $('.row-checkbox-npkpnppn').length === $(
                '.row-checkbox-npkpnppn:checked').length;
            $('#select-all-npkpnppn').prop('checked', allChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice: invoice,
                    is_checked: isChecked
                },
                success: function(response) {
                    setDownloadCounter('npkpnppn');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('npkpnppn');
                }
            });
            showCheckedSummaryDb('npkpnppn', npkpnppn_data_db);
        });

        //////// Checkbox RETUR ////////
        $('#select-all-retur').on('change', function() {
            handleSelectAllDb('retur', this);
        });
        $('#table-retur tbody').on('change', '.row-checkbox-retur', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const invoice = $(this).data('invoice');
            const allChecked = $('.row-checkbox-retur').length === $('.row-checkbox-retur:checked')
                .length;
            $('#select-all-retur').prop('checked', allChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice: invoice,
                    is_checked: isChecked
                },
                success: function(response) {
                    setDownloadCounter('retur');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('retur');
                }
            });
            showCheckedSummaryDb('retur', retur_data_db);
        });

        //////// Checkbox Non Standar ////////
        $('#select-all-nonstandar').on('change', function() {
            handleSelectAllDb('nonstandar', this);
        });
        $('#table-nonstandar tbody').on('change', '.row-checkbox-nonstandar', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const invoice = $(this).data('invoice');
            const allChecked = $('.row-checkbox-nonstandar').length === $(
                    '.row-checkbox-nonstandar:checked')
                .length;
            $('#select-all-nonstandar').prop('checked', allChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice: invoice,
                    is_checked: isChecked
                },
                success: function(response) {
                    setDownloadCounter('nonstandar');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('nonstandar');
                }
            });
            showCheckedSummaryDb('nonstandar', nonstandar_data_db);
        });

        //////// Checkbox Pembatalan ////////
        $('#select-all-pembatalan').on('change', function() {
            handleSelectAllDb('pembatalan', this);
        });
        $('#table-pembatalan tbody').on('change', '.row-checkbox-pembatalan', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const invoice = $(this).data('invoice');
            const allChecked = $('.row-checkbox-pembatalan').length === $(
                '.row-checkbox-pembatalan:checked').length;
            $('#select-all-pembatalan').prop('checked', allChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice: invoice,
                    is_checked: isChecked
                },
                success: function(response) {
                    setDownloadCounter('pembatalan');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('pembatalan');
                }
            });
            showCheckedSummaryDb('pembatalan', pembatalan_data_db);
        });

        //////// Checkbox Koreksi ////////
        $('#select-all-koreksi').on('change', function() {
            handleSelectAllDb('koreksi', this);
        });
        $('#table-koreksi tbody').on('change', '.row-checkbox-koreksi', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const invoice = $(this).data('invoice');
            const allChecked = $('.row-checkbox-koreksi').length === $('.row-checkbox-koreksi:checked')
                .length;
            $('#select-all-koreksi').prop('checked', allChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice: invoice,
                    is_checked: isChecked
                },
                success: function(response) {
                    setDownloadCounter('koreksi');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('koreksi');
                }
            });
            showCheckedSummaryDb('koreksi', koreksi_data_db);
        });

        //////// Checkbox Pending ////////
        $('#select-all-pending').on('change', function() {
            handleSelectAllDb('pending', this);
        });
        $('#table-pending tbody').on('change', '.row-checkbox-pending', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const invoice = $(this).data('invoice');
            const allChecked = $('.row-checkbox-pending').length === $('.row-checkbox-pending:checked')
                .length;
            $('#select-all-pending').prop('checked', allChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice: invoice,
                    is_checked: isChecked
                },
                success: function(response) {
                    setDownloadCounter('pending');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('pending');
                }
            });
            showCheckedSummaryDb('pending', pending_data_db);
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
                @php
                    $userInfo = getLoggedInUserInfo();
                    $userDepos = $userInfo ? $userInfo->depo : [];
                    $hasAllAccess = in_array('all', $userDepos);
                @endphp

                let hasAllAccess = {{ $hasAllAccess ? 'true' : 'false' }};
                let currentUserDepo = @json($userDepos);

                if (!hasAllAccess) {
                    $.each(data.data, function(index, depo) {
                        if (currentUserDepo.includes(depo.code)) {
                            $('#filter_depo').append($('<option>', {
                                value: depo.code,
                                text: depo.code + ' - ' + depo.name
                            }));
                        }
                    });
                } else {
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
                        text: pt.code + ' - ' + pt.name
                    }));
                });
                $('#sp-filter-pt').hide();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                toastr.error('Gagal memuat data PT.');
            }
        });

        // select2 initialization
        $('#filter_brand, #filter_depo, #filter_pt').select2({
            allowClear: true,
            width: '100%',
            placeholder: 'Pilih..',
        });

        // get brand when filter_pt changes
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

    function toDecimal4(num) {
        if (isNaN(num) || num === null) return '0.0000';
        let val = parseFloat(num);
        if (Math.abs(val) < 0.00005) val = 0;
        return val.toLocaleString('en-US', {
            minimumFractionDigits: 4,
            maximumFractionDigits: 4
        });
    }

    function showCheckedSummaryDb(tipe, src_data) {
        const checkedRows = $('#table-' + tipe + ' tbody .row-checkbox-' + tipe + ':checked');
        $('#table-' + tipe + ' tbody tr.summary-row').remove();

        const summaryContainer = $('#summary-content');

        if (checkedRows.length === 0) {
            summaryContainer.html('<div class="alert alert-info">Belum ada data yang dipilih.</div>');
            return;
        }

        // Summary by customer id for invoices
        const summaryData = checkedRows.toArray().reduce((acc, row) => {
            const invoice = $(row).data('invoice');
            const item = src_data.find(d => d.no_invoice == invoice);
            if (!item) return acc;

            const customerId = item.customer_id || 'Unknown';
            const dpp = parseFloat(item.total_dpp || 0);
            const ppn = parseFloat(item.total_ppn || 0);

            if (!acc[customerId]) {
                acc[customerId] = {
                    total_dpp: 0,
                    total_ppn: 0
                };
            }

            acc[customerId].total_dpp += dpp;
            acc[customerId].total_ppn += ppn;

            return acc;
        }, {});

        let summaryTableRows = '';
        for (const [customerId, totals] of Object.entries(summaryData)) {
            summaryTableRows += `
                    <tr>
                        <td>${customerId}</td>
                        <td>${toDecimal4(totals.total_dpp)}</td>
                        <td>${toDecimal4(totals.total_ppn)}</td>
                    </tr>
                `;
        }

        const summaryTable = `
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-striped table-hover">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Customer ID</th>
                                <th>Total DPP</th>
                                <th>Total PPN</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${summaryTableRows}
                        </tbody>
                    </table>
                </div>
            `;

        summaryContainer.html(summaryTable);
    }

    function handleSelectAllDb(tipe, element) {
        const isChecked = $(element).is(':checked');
        const tableDesc = {
            'pkp': 'PKP',
            'pkpnppn': 'PKP Non-PPN',
            'npkp': 'Non-PKP',
            'npkpnppn': 'Non-PKP Non-PPN',
            'retur': 'Retur',
            'nonstandar': 'Non Standar',
            'pembatalan': 'Pembatalan',
            'koreksi': 'Koreksi',
            'pending': 'Pending'
        } [tipe];

        $(`.row-checkbox-${tipe}`).prop('checked', isChecked);
        const invoices = $(`.row-checkbox-${tipe}`).map(function() {
            return $(this).data('invoice');
        }).get();

        const updateSummary = () => {
            let dataSrc;
            if (tipe == 'pkp') dataSrc = pkp_data_db;
            else if (tipe == 'pkpnppn') dataSrc = pkpnppn_data_db;
            else if (tipe == 'npkp') dataSrc = npkp_data_db;
            else if (tipe == 'npkpnppn') dataSrc = npkpnppn_data_db;
            else if (tipe == 'retur') dataSrc = retur_data_db;
            else if (tipe == 'nonstandar') dataSrc = nonstandar_data_db;
            else if (tipe == 'pembatalan') dataSrc = pembatalan_data_db;
            else if (tipe == 'koreksi') dataSrc = koreksi_data_db;
            else if (tipe == 'pending') dataSrc = pending_data_db;
            if (typeof showCheckedSummaryDb === 'function') {
                showCheckedSummaryDb(tipe, dataSrc);
            }
        };
        updateSummary();

        swal({
            title: isChecked ? "Pilih Data" : "Hapus Pilihan",
            text: isChecked ?
                `Pilih semua data ${tableDesc} yang sesuai filter (Database) atau hanya halaman ini?` :
                `Hapus pilihan semua data ${tableDesc} (Database) atau hanya halaman ini?`,
            icon: isChecked ? "info" : "warning",
            buttons: {
                cancel: {
                    text: "Batal",
                    visible: true,
                    closeModal: true,
                    value: null
                },
                page: {
                    text: "Halaman Ini",
                    value: "page",
                    visible: true,
                    className: "btn-secondary",
                    closeModal: true
                },
                all: {
                    text: "Semua Data",
                    value: "all",
                    visible: true,
                    className: isChecked ? "btn-primary" : "btn-danger",
                    closeModal: true
                }
            }
        }).then((value) => {
            if (value === 'all') {
                let table;
                if (tipe == 'pkp') table = tablePkpDb;
                else if (tipe == 'pkpnppn') table = tablePkpDbNppn;
                else if (tipe == 'npkp') table = tableNonPkpDb;
                else if (tipe == 'npkpnppn') table = tableNonPkpDbNppn;
                else if (tipe == 'retur') table = tableReturDb;
                else if (tipe == 'nonstandar') table = tableNonStandarDb;
                else if (tipe == 'pembatalan') table = tablePembatalanDb;
                else if (tipe == 'koreksi') table = tableKoreksiDb;
                else if (tipe == 'pending') table = tablePendingDb;
                if (!table || !table.ajax) {
                    toastr.error('Table belum siap. Silakan apply filter dulu.');
                    return;
                }
                let params = table.ajax.params();

                if (!params) {
                    toastr.error('Table not ready.');
                    return;
                }

                params.is_checked = isChecked ? 1 : 0;
                params.select_all = 1;
                params._token = '{{ csrf_token() }}';

                toastr.info('Sedang memproses...');

                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                    type: "POST",
                    data: params,
                    success: function(response) {
                        table.ajax.reload();
                        setDownloadCounter(tipe);
                        toastr.success(response.message || 'Berhasil memperbarui data.');
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memproses data.');
                    }
                });

            } else if (value === 'page') {
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        invoices: invoices,
                        is_checked: isChecked ? 1 : 0
                    },
                    success: function() {
                        setDownloadCounter(tipe);
                    },
                    error: function() {
                        console.error('Error updating checked');
                    }
                });
            } else {
                $(element).prop('checked', !isChecked);
                $(`.row-checkbox-${tipe}`).prop('checked', !isChecked);
                updateSummary();
            }
        });
    }
</script>
