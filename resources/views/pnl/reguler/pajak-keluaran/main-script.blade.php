<script>
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
            $('input[name="filter_periode"]').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                opens: 'left',
                startDate: moment(),
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
            $('input[name="filter_periode"]').on('apply.daterangepicker', function(ev, picker) {
                // Reload all tables when date changes
                if (tablePkp) tablePkp.ajax.reload();
                if (tablePkpNppn) tablePkpNppn.ajax.reload();
                if (tableNonPkp) tableNonPkp.ajax.reload();
                if (tableNonPkpNppn) tableNonPkpNppn.ajax.reload();
                if (tableRetur) tableRetur.ajax.reload();

                // Update counters
                setDownloadCounter('pkp');
                setDownloadCounter('pkpnppn');
                setDownloadCounter('npkp');
                setDownloadCounter('npkpnppn');
                setDownloadCounter('retur');
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
                    setDownloadCounter('pkp');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('pkp');
                }
            });
            showCheckedSummary('pkp', pkp_data);
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
                    setDownloadCounter('pkpnppn');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('pkpnppn');
                }
            });
            showCheckedSummary('pkpnppn', pkpnppn_data);
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
                    setDownloadCounter('npkp');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('npkp');
                }
            });
            showCheckedSummary('npkp', npkp_data);
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
                    setDownloadCounter('npkpnppn');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('npkpnppn');
                }
            });
            showCheckedSummary('npkpnppn', npkpnppn_data);
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
                    setDownloadCounter('retur');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    setDownloadCounter('retur');
                }
            });
            showCheckedSummary('retur', retur_data);
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
            // Hapus summary sebelumnya
            $('#table-' + tipe + ' tbody tr.summary-row').remove();

            if (checkedRows.length === 0) return;

            // Summary harga_total, disc, dpp, dpp_lain, ppn by customer id
            const summaryData = checkedRows.toArray().reduce((acc, row) => {
                const customerId = src_data.find(item => item.id == $(row).data('id'))?.customer_id ||
                    'Unknown';
                const hargaTotal = parseFloat(src_data.find(item => item.id == $(row).data('id'))
                    ?.hargatotal_sblm_ppn || 0);
                const disc = parseFloat(src_data.find(item => item.id == $(row).data('id'))?.disc || 0);
                const dpp = parseFloat(src_data.find(item => item.id == $(row).data('id'))?.dpp || 0);
                const dppLain = parseFloat(src_data.find(item => item.id == $(row).data('id'))
                    ?.dpp_lain || 0);
                const ppn = parseFloat(src_data.find(item => item.id == $(row).data('id'))?.ppn || 0);

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
            let summaryTable = '';
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
            summaryTable += `
                    <table class="table table-bordered table-sm bg-primary" style="width: 20%; font-size: 12px;">
                        <thead>
                            <th style="padding:2px;">Customer ID</th>
                            <th style="padding:2px;">Total Harga</th>
                            <th style="padding:2px;">Total Disc</th>
                            <th style="padding:2px;">Total DPP</th>
                            <th style="padding:2px;">Total DPP Lain</th>
                            <th style="padding:2px;">Total PPN</th>
                        </thead>
                        <tbody>
                            ${summaryTableRows}
                        </tbody>
                    </table>
                `;

            // Ambil baris terakhir yang dicheck
            const lastChecked = checkedRows.last().closest('tr');
            // Buat elemen summary, generate table
            const summaryHtml = `
                    <tr class="summary-row bg-light">
                        <td colspan="33">
                            <b>Summary:</b>
                            ${summaryTable}
                        </td>
                    </tr>
                `;
            // Sisipkan summary setelah baris terakhir yang dicheck
            lastChecked.after(summaryHtml);
        }

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
</script>
