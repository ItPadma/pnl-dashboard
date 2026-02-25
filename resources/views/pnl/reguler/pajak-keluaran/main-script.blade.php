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
                initializeDataTablePkp();
            } else if (tipe === 'pkpnppn') {
                initializeDataTablePkpNppn();
            } else if (tipe === 'npkp') {
                initializeDataTableNonPkp();
            } else if (tipe === 'npkpnppn') {
                initializeDataTableNonPkpNppn();
            } else if (tipe === 'retur') {
                initializeDataTableRetur();
            } else if (tipe === 'nonstandar') {
                initializeDataTableNonStandar();
            } else if (tipe === 'pembatalan') {
                initializeDataTablePembatalan();
            } else if (tipe === 'koreksi') {
                initializeDataTableKoreksi();
            } else if (tipe === 'pending') {
                initializeDataTablePending();
            }
            tableInitialized[tipe] = true;
        };

        // Event listener untuk tab switching
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('href');
            if (target === '#tabpanel-pkp' && tableInitialized.pkp && tablePkp) {
                tablePkp.columns.adjust();
                $('.dataTables_scrollBody thead').remove();
                $('.dataTables_scrollBody tfoot').remove();
                showCheckedSummary('pkp', pkp_data);
            }
            if (target === '#tabpanel-nonpkp' && tableInitialized.npkp && tableNonPkp) {
                tableNonPkp.columns.adjust();
                $('.dataTables_scrollBody thead').remove();
                $('.dataTables_scrollBody tfoot').remove();
                showCheckedSummary('npkp', npkp_data);
            }
            if (target === '#tabpanel-pkpnppn' && tableInitialized.pkpnppn && tablePkpNppn) {
                tablePkpNppn.columns.adjust();
                $('.dataTables_scrollBody thead').remove();
                $('.dataTables_scrollBody tfoot').remove();
                showCheckedSummary('pkpnppn', pkpnppn_data);
            }
            if (target === '#tabpanel-nonpkpnppn' && tableInitialized.npkpnppn && tableNonPkpNppn) {
                tableNonPkpNppn.columns.adjust();
                $('.dataTables_scrollBody thead').remove();
                $('.dataTables_scrollBody tfoot').remove();
                showCheckedSummary('npkpnppn', npkpnppn_data);
            }
            if (target === '#tabpanel-retur' && tableInitialized.retur && tableRetur) {
                tableRetur.columns.adjust();
                $('.dataTables_scrollBody thead').remove();
                $('.dataTables_scrollBody tfoot').remove();
                showCheckedSummary('retur', retur_data);
            }
            if (target === '#tabpanel-nonstandar' && tableInitialized.nonstandar && tableNonStandar) {
                tableNonStandar.columns.adjust();
                $('.dataTables_scrollBody thead').remove();
                $('.dataTables_scrollBody tfoot').remove();
                showCheckedSummary('nonstandar', nonstandar_data);
            }
            if (target === '#tabpanel-pembatalan' && tableInitialized.pembatalan && tablePembatalan) {
                tablePembatalan.columns.adjust();
                $('.dataTables_scrollBody thead').remove();
                $('.dataTables_scrollBody tfoot').remove();
                showCheckedSummary('pembatalan', pembatalan_data);
            }
            if (target === '#tabpanel-koreksi' && tableInitialized.koreksi && tableKoreksi) {
                tableKoreksi.columns.adjust();
                $('.dataTables_scrollBody thead').remove();
                $('.dataTables_scrollBody tfoot').remove();
                showCheckedSummary('koreksi', koreksi_data);
            }
            if (target === '#tabpanel-pending' && tableInitialized.pending && tablePending) {
                tablePending.columns.adjust();
                $('.dataTables_scrollBody thead').remove();
                $('.dataTables_scrollBody tfoot').remove();
                showCheckedSummary('pending', pending_data);
            }
        });

        // Add change event listeners to filters
        $('#btn-apply-filter').on('click', function() {
            let appllied_tab = $('#inputGroupFilter').val();

            const reloadTable = (tipe) => {
                initTableIfNeeded(tipe);
                if (tipe === 'pkp' && tablePkp) {
                    tablePkp.ajax.reload();
                } else if (tipe === 'pkpnppn' && tablePkpNppn) {
                    tablePkpNppn.ajax.reload();
                } else if (tipe === 'npkp' && tableNonPkp) {
                    tableNonPkp.ajax.reload();
                } else if (tipe === 'npkpnppn' && tableNonPkpNppn) {
                    tableNonPkpNppn.ajax.reload();
                } else if (tipe === 'retur' && tableRetur) {
                    tableRetur.ajax.reload();
                } else if (tipe === 'nonstandar' && tableNonStandar) {
                    tableNonStandar.ajax.reload();
                } else if (tipe === 'pembatalan' && tablePembatalan) {
                    tablePembatalan.ajax.reload();
                } else if (tipe === 'koreksi' && tableKoreksi) {
                    tableKoreksi.ajax.reload();
                } else if (tipe === 'pending' && tablePending) {
                    tablePending.ajax.reload();
                }

                if (tableInitialized[tipe]) {
                    setDownloadCounter(tipe);
                }
            };

            if (appllied_tab == 'pkp') {
                reloadTable('pkp');
            } else if (appllied_tab === 'pkpnppn') {
                reloadTable('pkpnppn');
            } else if (appllied_tab === 'npkp') {
                reloadTable('npkp');
            } else if (appllied_tab === 'npkpnppn') {
                reloadTable('npkpnppn');
            } else if (appllied_tab === 'retur') {
                reloadTable('retur');
            } else if (appllied_tab === 'nonstandar') {
                reloadTable('nonstandar');
            } else if (appllied_tab === 'pembatalan') {
                reloadTable('pembatalan');
            } else if (appllied_tab === 'koreksi') {
                reloadTable('koreksi');
            } else if (appllied_tab === 'pending') {
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
                        // Reinitialize daterangepicker with new available dates
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
                    // Check if this date has data
                    const dateStr = date.format('YYYY-MM-DD');
                    if (availableDates.includes(dateStr)) {
                        return 'has-data';
                    }
                    return '';
                }
            }, function(start, end, label) {
                // Callback when date is selected
                console.log('Date selected:', start.format('DD/MM/YYYY'));
            });

            // Add event listener for date change
            $periodeInput.on('apply.daterangepicker', function(ev, picker) {
                // Reload tables only if they've been initialized
                if (tableInitialized.pkp && tablePkp) {
                    tablePkp.ajax.reload();
                    setDownloadCounter('pkp');
                }
                if (tableInitialized.pkpnppn && tablePkpNppn) {
                    tablePkpNppn.ajax.reload();
                    setDownloadCounter('pkpnppn');
                }
                if (tableInitialized.npkp && tableNonPkp) {
                    tableNonPkp.ajax.reload();
                    setDownloadCounter('npkp');
                }
                if (tableInitialized.npkpnppn && tableNonPkpNppn) {
                    tableNonPkpNppn.ajax.reload();
                    setDownloadCounter('npkpnppn');
                }
                if (tableInitialized.retur && tableRetur) {
                    tableRetur.ajax.reload();
                    setDownloadCounter('retur');
                }
                if (tableInitialized.nonstandar && tableNonStandar) {
                    tableNonStandar.ajax.reload();
                    setDownloadCounter('nonstandar');
                }
                if (tableInitialized.pembatalan && tablePembatalan) {
                    tablePembatalan.ajax.reload();
                    setDownloadCounter('pembatalan');
                }
                if (tableInitialized.koreksi && tableKoreksi) {
                    tableKoreksi.ajax.reload();
                    setDownloadCounter('koreksi');
                }
                if (tableInitialized.pending && tablePending) {
                    tablePending.ajax.reload();
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
            handleSelectAll('pkp', this);
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
                    setDownloadCounter('pkp');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('pkp');
                }
            });
            showCheckedSummary('pkp', pkp_data);
        });

        //////// Checkbox PKP Non-PPN ////////
        // Event listener untuk checkbox select all
        $('#select-all-pkpnppn').on('change', function() {
            handleSelectAll('pkpnppn', this);
        });
        // Event listener untuk checkbox individual
        $('#table-pkpnppn tbody').on('change', '.row-checkbox-pkpnppn', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const id = $(this).data('id');
            const allChecked = $('.row-checkbox-pkpnppn').length === $('.row-checkbox-pkpnppn:checked')
                .length;
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
                    setDownloadCounter('pkpnppn');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('pkpnppn');
                }
            });
            showCheckedSummary('pkpnppn', pkpnppn_data);
        });

        //////// Checkbox Non-PKP //////////
        // Event listener untuk checkbox select all
        $('#select-all-npkp').on('change', function() {
            handleSelectAll('npkp', this);
        });
        // Event listener untuk checkbox individual
        $('#table-npkp tbody').on('change', '.row-checkbox-npkp', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const id = $(this).data('id');
            const allChecked = $('.row-checkbox-npkp').length === $('.row-checkbox-npkp:checked')
                .length;
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
                    setDownloadCounter('npkp');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('npkp');
                }
            });
            showCheckedSummary('npkp', npkp_data);
        });

        //////// Checkbox Non-PKP Non-PPN ////////
        // Event listener untuk checkbox select all
        $('#select-all-npkpnppn').on('change', function() {
            handleSelectAll('npkpnppn', this);
        });
        // Event listener untuk checkbox individual
        $('#table-npkpnppn tbody').on('change', '.row-checkbox-npkpnppn', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const id = $(this).data('id');
            const allChecked = $('.row-checkbox-npkpnppn').length === $(
                '.row-checkbox-npkpnppn:checked').length;
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
                    setDownloadCounter('npkpnppn');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('npkpnppn');
                }
            });
            showCheckedSummary('npkpnppn', npkpnppn_data);
        });

        //////// Checkbox RETUR ////////
        // Event listener untuk checkbox select all
        $('#select-all-retur').on('change', function() {
            handleSelectAll('retur', this);
        });
        // Event listener untuk checkbox individual
        $('#table-retur tbody').on('change', '.row-checkbox-retur', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const id = $(this).data('id');
            const allChecked = $('.row-checkbox-retur').length === $('.row-checkbox-retur:checked')
                .length;
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
                    setDownloadCounter('retur');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('retur');
                }
            });
            showCheckedSummary('retur', retur_data);
        });

        //////// Checkbox Non Standar ////////
        $('#select-all-nonstandar').on('change', function() {
            handleSelectAll('nonstandar', this);
        });
        $('#table-nonstandar tbody').on('change', '.row-checkbox-nonstandar', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const id = $(this).data('id');
            const allChecked = $('.row-checkbox-nonstandar').length === $(
                    '.row-checkbox-nonstandar:checked')
                .length;
            $('#select-all-nonstandar').prop('checked', allChecked);
            toggleMoveToSelect(id, isChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
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
            showCheckedSummary('nonstandar', nonstandar_data);
        });

        //////// Checkbox Pembatalan ////////
        $('#select-all-pembatalan').on('change', function() {
            handleSelectAll('pembatalan', this);
        });
        $('#table-pembatalan tbody').on('change', '.row-checkbox-pembatalan', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const id = $(this).data('id');
            const allChecked = $('.row-checkbox-pembatalan').length === $(
                '.row-checkbox-pembatalan:checked').length;
            $('#select-all-pembatalan').prop('checked', allChecked);
            toggleMoveToSelect(id, isChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
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
            showCheckedSummary('pembatalan', pembatalan_data);
        });

        //////// Checkbox Koreksi ////////
        $('#select-all-koreksi').on('change', function() {
            handleSelectAll('koreksi', this);
        });
        $('#table-koreksi tbody').on('change', '.row-checkbox-koreksi', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const id = $(this).data('id');
            const allChecked = $('.row-checkbox-koreksi').length === $('.row-checkbox-koreksi:checked')
                .length;
            $('#select-all-koreksi').prop('checked', allChecked);
            toggleMoveToSelect(id, isChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
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
            showCheckedSummary('koreksi', koreksi_data);
        });

        //////// Checkbox Pending ////////
        $('#select-all-pending').on('change', function() {
            handleSelectAll('pending', this);
        });
        $('#table-pending tbody').on('change', '.row-checkbox-pending', function() {
            const isChecked = $(this).is(':checked') ? 1 : 0;
            const id = $(this).data('id');
            const allChecked = $('.row-checkbox-pending').length === $('.row-checkbox-pending:checked')
                .length;
            $('#select-all-pending').prop('checked', allChecked);
            toggleMoveToSelect(id, isChecked);
            $.ajax({
                url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
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
            showCheckedSummary('pending', pending_data);
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

    function toDecimal4(num) {
        if (isNaN(num) || num === null) return '0.0000';
        let val = parseFloat(num);
        // Jika hasilnya sangat kecil, set ke 0
        if (Math.abs(val) < 0.00005) val = 0;
        return val.toLocaleString('en-US', {
            minimumFractionDigits: 4,
            maximumFractionDigits: 4
        });
    }

    function showCheckedSummary(tipe, src_data) {
        const checkedRows = $('#table-' + tipe + ' tbody .row-checkbox-' + tipe + ':checked');
        // Hapus summary row lama di table jika masih ada (cleanup)
        $('#table-' + tipe + ' tbody tr.summary-row').remove();

        const summaryContainer = $('#summary-content');

        if (checkedRows.length === 0) {
            summaryContainer.html('<div class="alert alert-info">Belum ada data yang dipilih.</div>');
            return;
        }

        // Summary harga_total, disc, dpp, dpp_lain, ppn by customer id
        const summaryData = checkedRows.toArray().reduce((acc, row) => {
            const item = src_data.find(d => d.id == $(row).data('id'));
            if (!item) return acc;

            const customerId = item.customer_id || 'Unknown';
            const hargaTotal = parseFloat(item.hargatotal_sblm_ppn || 0);
            const disc = parseFloat(item.disc || 0);
            const dpp = parseFloat(item.dpp || 0);
            const dppLain = parseFloat(item.dpp_lain || 0);
            const ppn = parseFloat(item.ppn || 0);

            if (!acc[customerId]) {
                acc[customerId] = {
                    total_harga: 0,
                    total_disc: 0,
                    total_dpp: 0,
                    total_dpp_lain: 0,
                    total_ppn: 0
                };
            }

            acc[customerId].total_harga += hargaTotal;
            acc[customerId].total_disc += disc;
            acc[customerId].total_dpp += dpp;
            acc[customerId].total_dpp_lain += dppLain;
            acc[customerId].total_ppn += ppn;

            return acc;
        }, {});

        // generate table from summary
        let summaryTableRows = '';
        for (const [customerId, totals] of Object.entries(summaryData)) {
            summaryTableRows += `
                    <tr>
                        <td>${customerId}</td>
                        <td>${toDecimal4(totals.total_harga)}</td>
                        <td>${toDecimal4(totals.total_disc)}</td>
                        <td>${toDecimal4(totals.total_dpp)}</td>
                        <td>${toDecimal4(totals.total_dpp_lain)}</td>
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
                                <th>Total Harga</th>
                                <th>Total Disc</th>
                                <th>Total DPP</th>
                                <th>Total DPP Lain</th>
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

    function reloadTableMoveFromMove2(move_from, move_to) {
        // Mapping tipe ke variabel DataTable
        const tableMap = {
            pkp: tablePkp,
            pkpnppn: tablePkpNppn,
            npkp: tableNonPkp,
            npkpnppn: tableNonPkpNppn,
            retur: tableRetur,
            nonstandar: tableNonStandar,
            pembatalan: tablePembatalan,
            koreksi: tableKoreksi,
            pending: tablePending
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
    $(document).on('change', '.move-to', function() {
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

    // Variabel state untuk menyimpan status move sementara modal PKP diajukan
    let pendingMoveParams = null;

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
                if (move_to === 'pkp') {
                    // Check missing PKP first
                    $.ajax({
                        url: "{{ route('pnl.reguler.pajak-keluaran.checkMissingPkp') }}",
                        type: "POST",
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: ids
                        },
                        success: function(response) {
                            if (response.status && response.data.length > 0) {
                                // Tampilkan Modal
                                pendingMoveParams = {
                                    ids: ids,
                                    move_from: move_from,
                                    move_to: move_to
                                };

                                let tbodyHtml = '';
                                response.data.forEach(function(item, index) {
                                    tbodyHtml += `
                                        <tr>
                                            <td><input type="text" class="form-control form-control-sm" name="pkp_list[${index}][IDPelanggan]" value="${item.IDPelanggan}" readonly></td>
                                            <td><input type="text" class="form-control form-control-sm" name="pkp_list[${index}][NamaPKP]" value="${item.NamaPKP}" required></td>
                                            <td><input type="text" class="form-control form-control-sm" name="pkp_list[${index}][NoPKP]" value="${item.NoPKP || ''}"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="pkp_list[${index}][AlamatPKP]" value="${item.AlamatPKP || ''}"></td>
                                            <td>
                                                <select class="form-select form-select-sm" name="pkp_list[${index}][TypePajak]">
                                                    <option value="PPN" ${item.TypePajak === 'PPN' ? 'selected' : ''}>PPN</option>
                                                    <option value="NON-PPN" ${item.TypePajak === 'NON-PPN' ? 'selected' : ''}>NON-PPN</option>
                                                </select>
                                            </td>
                                        </tr>
                                    `;
                                });

                                $('#tbody-missing-pkp').html(tbodyHtml);
                                $('#modalInputMasterPkp').modal('show');
                            } else {
                                // Proceed direct move
                                executeMoveTo(ids, move_from, move_to);
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Terjadi kesalahan saat mengecek data Master PKP.');
                        }
                    });
                } else {
                    executeMoveTo(ids, move_from, move_to);
                }
            } else {
                toastr.warning('Tidak ada data yang dipilih untuk dipindahkan.');
            }
        } else {
            toastr.warning('Silakan pilih tipe pajak dan tujuan pemindahan.');
        }
    }

    function executeMoveTo(ids, move_from, move_to) {
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
                if (response.status) {
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
    }

    // Modal save button handler
    $(document).on('click', '#btn-save-master-pkp', function() {
        const pkpList = [];
        let isValid = true;

        $('#tbody-missing-pkp tr').each(function() {
            const idPelanggan = $(this).find('input[name*="[IDPelanggan]"]').val();
            const namaPKP = $(this).find('input[name*="[NamaPKP]"]').val();
            const noPKP = $(this).find('input[name*="[NoPKP]"]').val();
            const alamatPKP = $(this).find('input[name*="[AlamatPKP]"]').val();
            const typePajak = $(this).find('select[name*="[TypePajak]"]').val();

            if (!namaPKP) {
                isValid = false;
                $(this).find('input[name*="[NamaPKP]"]').addClass('is-invalid');
            } else {
                $(this).find('input[name*="[NamaPKP]"]').removeClass('is-invalid');
            }

            pkpList.push({
                IDPelanggan: idPelanggan,
                NamaPKP: namaPKP,
                NoPKP: noPKP,
                AlamatPKP: alamatPKP,
                TypePajak: typePajak
            });
        });

        if (!isValid) {
            toastr.error('Pastikan semua form mandatory terisi.');
            return;
        }

        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<div class="spinner-border spinner-border-sm" role="status"></div> Menyimpan...').prop(
            'disabled', true);

        $.ajax({
            url: "{{ route('pnl.reguler.pajak-keluaran.saveMasterPkpBulk') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                pkp_list: pkpList
            },
            success: function(response) {
                if (response.status) {
                    $('#modalInputMasterPkp').modal('hide');
                    if (pendingMoveParams) {
                        executeMoveTo(pendingMoveParams.ids, pendingMoveParams.move_from,
                            pendingMoveParams.move_to);
                        pendingMoveParams = null; // reset
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan saat menyimpan data Master PKP.');
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });

    function handleSelectAll(tipe, element) {
        const isChecked = $(element).is(':checked');
        const tableDesc = {
            'pkp': 'PKP',
            'pkpnppn': 'PKP (Non-PPN)',
            'npkp': 'Non-PKP',
            'npkpnppn': 'Non-PKP (Non-PPN)',
            'retur': 'Retur',
            'nonstandar': 'Non Standar'
        } [tipe];

        // Update UI Visuals immediately
        $(`.row-checkbox-${tipe}`).prop('checked', isChecked);
        const ids = $(`.row-checkbox-${tipe}`).map(function() {
            return $(this).data('id');
        }).get();
        for (const id of ids) {
            toggleMoveToSelect(id, isChecked);
        }

        // Helper to update summary
        const updateSummary = () => {
            let dataSrc;
            if (tipe == 'pkp') dataSrc = pkp_data;
            else if (tipe == 'pkpnppn') dataSrc = pkpnppn_data;
            else if (tipe == 'npkp') dataSrc = npkp_data;
            else if (tipe == 'npkpnppn') dataSrc = npkpnppn_data;
            else if (tipe == 'retur') dataSrc = retur_data;
            else if (tipe == 'nonstandar') dataSrc = nonstandar_data;
            if (typeof showCheckedSummary === 'function') {
                showCheckedSummary(tipe, dataSrc);
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
                if (tipe == 'pkp') table = tablePkp;
                else if (tipe == 'pkpnppn') table = tablePkpNppn;
                else if (tipe == 'npkp') table = tableNonPkp;
                else if (tipe == 'npkpnppn') table = tableNonPkpNppn;
                else if (tipe == 'retur') table = tableRetur;
                else if (tipe == 'nonstandar') table = tableNonStandar;

                let params = table.ajax.params();
                // Ensure params exist (might be null if no ajax made yet?)
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
                // IDs based update
                $.ajax({
                    url: "{{ route('pnl.reguler.pajak-keluaran.updateChecked') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids,
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
                // Cancelled - Revert
                $(element).prop('checked', !isChecked);
                $(`.row-checkbox-${tipe}`).prop('checked', !isChecked);
                ids.forEach(id => toggleMoveToSelect(id, !isChecked));
                updateSummary();
            }
        });
    }
</script>
