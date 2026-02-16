<script src="{{ asset('assets/js/plugin/moment/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/daterangepicker/daterangepicker.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>

<script>
    let table;
    let historyTable;
    let selectedReturData = [];
    let npkpInvoiceData = []; // Store loaded Non-PKP data for preview calculations
    let availableDates = [];
    let availableDateSet = new Set();
    let availableDatesRequest = null;
    let availableDatesDebounceTimer = null;

    const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);

    $(document).ready(function() {
        initializeFilters();
        scheduleFetchAvailableDates();
        initializeDataTable();
        initializeHistoryTable();

        $('#filter_pt, #filter_brand, #filter_depo').on('change', function() {
            scheduleFetchAvailableDates();
        });

        // Apply filter
        $('#btn-apply-filter').on('click', function() {
            if (table) {
                clearReturSelection();
                table.ajax.reload(null, false);
                swal('Filter Applied', 'Data sedang dimuat ulang...', 'info', {
                    buttons: false,
                    timer: 1500
                });
            } else {
                swal('Error', 'Table belum diinisialisasi', 'error');
            }
        });

        // Export buttons
        $('#btn-export-xlsx').on('click', function() {
            exportData('xlsx');
        });
        $('#btn-export-csv').on('click', function() {
            exportData('csv');
        });

        // Select all retur checkbox
        $('#select-all-retur').on('change', function() {
            $('.retur-checkbox').prop('checked', $(this).prop('checked'));
            updateReturSelection();
        });

        // Individual retur checkbox
        $('#table-nett-invoice tbody').on('change', '.retur-checkbox', function() {
            updateReturSelection();
        });

        // Pilih Invoice Non-PKP button
        $('#btn-pilih-invoice').on('click', function() {
            showNpkpModal();
        });

        // Filter Non-PKP in modal
        $('#btn-filter-npkp').on('click', function() {
            fetchNonPkpInvoices();
        });

        // Non-PKP checkbox selection
        $(document).on('change', '.npkp-checkbox', function() {
            updateNettingPreview();
        });

        // Non-PKP search filter
        $(document).on('keyup', '#npkp-search', function() {
            const keyword = $(this).val().toLowerCase();
            $('#npkp-tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(keyword) > -1);
            });
        });

        // Process nett button
        $('#btn-process-nett').on('click', function() {
            processNett();
        });
    });

    function initializeFilters() {
        // PT
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
            error: function() {
                $('#sp-filter-pt').hide();
            }
        });

        // BRAND
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
            error: function() {
                $('#sp-filter-brand').hide();
            }
        });

        // DEPO
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
            error: function() {
                $('#sp-filter-depo').hide();
            }
        });

        // Select2
        $('#filter_brand, #filter_depo, #filter_pt').select2({
            allowClear: true,
            width: '100%',
            placeholder: 'Pilih..',
        });

        // Brand reload on PT change
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
                    error: function() {
                        $('#sp-filter-brand').hide();
                    }
                });
            } else {
                $('#filter_brand').val(null).trigger('change');
            }
        });

        // Daterangepickers
        initializeDateRangePickers();
    }

    function fetchAvailableDates() {
        if (availableDatesRequest && availableDatesRequest.readyState !== 4) {
            availableDatesRequest.abort();
        }

        let ptVal = $('#filter_pt').val();
        let brandVal = $('#filter_brand').val();
        let depoVal = $('#filter_depo').val();

        availableDatesRequest =
        $.ajax({
            url: '{{ route('pnl.reguler.nett-invoice.available-dates') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                pt: (ptVal && ptVal.length > 0) ? ptVal : ['all'],
                brand: (brandVal && brandVal.length > 0) ? brandVal : ['all'],
                depo: (depoVal && depoVal.length > 0) ? depoVal : ['all']
            },
            success: function(response) {
                if (response.status) {
                    availableDates = response.data || [];
                    availableDateSet = new Set(availableDates);
                    initializeDateRangePickers();
                }
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    console.error('Error fetching available dates');
                }
            }
        });
    }

    function scheduleFetchAvailableDates() {
        if (availableDatesDebounceTimer) {
            clearTimeout(availableDatesDebounceTimer);
        }

        availableDatesDebounceTimer = setTimeout(function() {
            fetchAvailableDates();
        }, 300);
    }

    function initializeDateRangePickers() {
        initializeSingleDateRangePicker('#filter_periode');
        initializeSingleDateRangePicker('#modal_filter_periode', {
            parentEl: '#modal-npkp'
        });
    }

    function initializeSingleDateRangePicker(selector, extraOptions = {}) {
        const $input = $(selector);
        if ($input.length === 0) {
            return;
        }

        let startDate = moment();
        let endDate = moment();

        const existingPicker = $input.data('daterangepicker');
        if (existingPicker) {
            startDate = existingPicker.startDate.clone();
            endDate = existingPicker.endDate.clone();
            $input.data('daterangepicker').remove();
        } else {
            const existingValue = ($input.val() || '').trim();
            if (existingValue.includes(' - ')) {
                const [startText, endText] = existingValue.split(' - ');
                const parsedStart = moment(startText, 'DD/MM/YYYY', true);
                const parsedEnd = moment(endText, 'DD/MM/YYYY', true);
                if (parsedStart.isValid() && parsedEnd.isValid()) {
                    startDate = parsedStart;
                    endDate = parsedEnd;
                }
            }
        }

        const options = {
            locale: {
                format: 'DD/MM/YYYY'
            },
            autoUpdateInput: true,
            startDate: startDate,
            endDate: endDate,
            isCustomDate: function(date) {
                const dateStr = date.format('YYYY-MM-DD');
                return availableDateSet.has(dateStr) ? 'has-data' : '';
            },
            ...extraOptions
        };

        $input.daterangepicker(options);
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
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<input type="checkbox" class="retur-checkbox"
                            value="${row.no_invoice}"
                            data-kode="${row.kode_pelanggan}"
                            data-nama="${row.nama_pelanggan}"
                            data-tanggal="${row.tgl_faktur_pajak}"
                            data-nilai="${row.nilai_retur}"
                            data-partial="${row.is_partial ? '1' : '0'}">`;
                    }
                },
                {
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
                    data: 'tgl_faktur_pajak',
                    name: 'tgl_faktur_pajak',
                    render: function(data) {
                        return data ? moment(data).format('DD/MM/YYYY') : '-';
                    }
                },
                {
                    data: 'nilai_retur',
                    name: 'nilai_retur',
                    render: function(data) {
                        return formatCurrency(data);
                    }
                },
                {
                    data: 'is_partial',
                    name: 'is_partial',
                    render: function(data) {
                        return data ?
                            '<span class="badge bg-warning text-dark">Sisa Partial</span>' :
                            '<span class="badge bg-success">Tersedia</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<button class="btn btn-sm btn-info btn-detail" data-invoice="${row.no_invoice}">
                            <i class="fas fa-eye"></i> Detail
                        </button>`;
                    }
                }
            ],
            language: {
                processing: '<div><i class="fas fa-spinner fa-spin fa-2x"></i><div>Memuat data...</div></div>'
            },
            pageLength: 10,
            order: [
                [4, 'asc']
            ]
        });

        $('#table-nett-invoice tbody').on('click', '.btn-detail', function() {
            showDetail($(this).data('invoice'));
        });
    }

    function initializeHistoryTable() {
        historyTable = $('#table-nett-history').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('pnl.reguler.nett-invoice.history') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            columns: [{
                    data: 'id_transaksi',
                    name: 'id_transaksi',
                    render: function(data) {
                        return data ? `<span title="${data}">${data.substring(0, 20)}…</span>` : '-';
                    }
                },
                {
                    data: 'no_invoice_npkp',
                    name: 'no_invoice_npkp'
                },
                {
                    data: 'no_invoice_retur',
                    name: 'no_invoice_retur'
                },
                {
                    data: 'nilai_invoice_npkp',
                    name: 'nilai_invoice_npkp',
                    render: function(data) {
                        return formatCurrency(data);
                    }
                },
                {
                    data: 'nilai_retur_used',
                    name: 'nilai_retur_used',
                    render: function(data) {
                        return formatCurrency(data);
                    }
                },
                {
                    data: 'nilai_nett',
                    name: 'nilai_nett',
                    render: function(data) {
                        return formatCurrency(data);
                    }
                },
                {
                    data: 'remaining_value',
                    name: 'remaining_value',
                    render: function(data) {
                        return parseFloat(data) > 0 ?
                            `<span class="text-warning fw-bold">${formatCurrency(data)}</span>` :
                            formatCurrency(data);
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data) {
                        return `<span class="badge bg-info">${data}</span>`;
                    }
                },
                {
                    data: 'created_by',
                    name: 'created_by',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data) {
                        return data ? moment(data).format('DD/MM/YYYY HH:mm') : '-';
                    }
                }
            ],
            language: {
                processing: '<div><i class="fas fa-spinner fa-spin fa-2x"></i><div>Memuat data...</div></div>'
            },
            pageLength: 10,
            order: [
                [9, 'desc']
            ]
        });
    }

    // ── Selection management ──

    function updateReturSelection() {
        selectedReturData = [];
        $('.retur-checkbox:checked').each(function() {
            selectedReturData.push({
                no_invoice: $(this).val(),
                kode_pelanggan: $(this).data('kode'),
                nama_pelanggan: $(this).data('nama'),
                tgl_faktur_pajak: $(this).data('tanggal'),
                nilai_retur: parseFloat($(this).data('nilai')),
                is_partial: $(this).data('partial') === '1' || $(this).data('partial') === 1
            });
        });

        const count = selectedReturData.length;
        $('#selected-count').text(count);
        count > 0 ? $('.btn-pilih-invoice').fadeIn(200) : $('.btn-pilih-invoice').fadeOut(200);
    }

    function clearReturSelection() {
        selectedReturData = [];
        $('#select-all-retur').prop('checked', false);
        $('.retur-checkbox').prop('checked', false);
        $('#selected-count').text('0');
        $('.btn-pilih-invoice').fadeOut(200);
    }

    // ── Detail modal ──

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
                        html += `<tr>
                            <td>${item.kode_produk}</td>
                            <td>${item.qty_pcs}</td>
                            <td>${formatCurrency(item.dpp)}</td>
                            <td>${formatCurrency(item.ppn)}</td>
                            <td>${formatCurrency(item.disc)}</td>
                        </tr>`;
                    });
                    $('#detail-items-tbody').html(html);
                    $('#modal-detail').modal('show');
                } else {
                    swal('Error', response.message, 'error');
                }
            },
            error: function() {
                swal('Error', 'Gagal mengambil detail invoice', 'error');
            }
        });
    }

    // ── Non-PKP modal ──

    function showNpkpModal() {
        if (selectedReturData.length === 0) {
            swal('Peringatan', 'Pilih minimal satu invoice retur', 'warning');
            return;
        }

        // Populate retur summary table
        let summaryHtml = '';
        let totalRetur = 0;
        selectedReturData.forEach(function(item) {
            totalRetur += item.nilai_retur;
            const dateFormatted = item.tgl_faktur_pajak ? moment(item.tgl_faktur_pajak).format('DD/MM/YYYY') :
                '-';
            const partialBadge = item.is_partial ?
                ' <span class="badge bg-warning text-dark" style="font-size:.65rem;">Partial</span>' : '';
            summaryHtml += `<tr>
                <td>${item.no_invoice}${partialBadge}</td>
                <td>${item.kode_pelanggan}</td>
                <td>${item.nama_pelanggan}</td>
                <td>${dateFormatted}</td>
                <td class="text-end">${formatCurrency(item.nilai_retur)}</td>
            </tr>`;
        });

        $('#selected-retur-summary').html(summaryHtml);
        $('#total-retur-value').text(formatCurrency(totalRetur));

        // Reset Non-PKP section
        npkpInvoiceData = [];
        $('#npkp-tbody').html('');
        $('#npkp-table-wrapper').hide();
        $('#npkp-empty').show();
        $('#npkp-loading').hide();
        $('#netting-preview').removeClass('active');
        $('#btn-process-nett').prop('disabled', true);

        $('#modal-npkp').modal('show');
    }

    function fetchNonPkpInvoices() {
        let ptVal = $('#filter_pt').val();
        let brandVal = $('#filter_brand').val();
        let depoVal = $('#filter_depo').val();
        let periode = $('#modal_filter_periode').val(); // tetap gunakan periode dari modal

        // Get unique customer IDs from selected retur
        let returCustomerIds = [...new Set(selectedReturData.map(r => r.kode_pelanggan))];

        $('#npkp-empty').hide();
        $('#npkp-table-wrapper').hide();
        $('#npkp-loading').show();
        $('#netting-preview').removeClass('active');

        $.ajax({
            url: '{{ route('pnl.reguler.nett-invoice.npkp-list') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                pt: (ptVal && ptVal.length > 0) ? ptVal : ['all'],
                brand: (brandVal && brandVal.length > 0) ? brandVal : ['all'],
                depo: (depoVal && depoVal.length > 0) ? depoVal : ['all'],
                periode: periode,
                retur_customer_ids: returCustomerIds
            },
            success: function(response) {
                $('#npkp-loading').hide();

                if (response.status && response.data.length > 0) {
                    npkpInvoiceData = response.data;
                    let html = '';
                    response.data.forEach(function(item) {
                        const dateFormatted = item.tgl_faktur_pajak ? moment(item.tgl_faktur_pajak)
                            .format('DD/MM/YYYY') : '-';
                        const isMatch = item.is_matching_customer;
                        const rowClass = isMatch ? 'npkp-match-row' : '';
                        const matchBadge = isMatch ?
                            '<span class="badge bg-success npkp-match-badge">Customer Sama</span>' :
                            '';

                        html += `<tr class="${rowClass}">
                            <td class="text-center">
                                <input type="checkbox" class="npkp-checkbox"
                                    value="${item.no_invoice}"
                                    data-nilai="${item.nilai_invoice}">
                            </td>
                            <td>${item.kode_pelanggan} ${matchBadge}</td>
                            <td>${item.nama_pelanggan}</td>
                            <td>${item.no_invoice}</td>
                            <td>${dateFormatted}</td>
                            <td class="text-end">${formatCurrency(item.nilai_invoice)}</td>
                        </tr>`;
                    });
                    $('#npkp-tbody').html(html);
                    $('#npkp-table-wrapper').show();
                    $('#npkp-empty').hide();
                } else {
                    npkpInvoiceData = [];
                    $('#npkp-empty').html(
                        '<i class="fas fa-inbox d-block" style="font-size:2.5rem;color:#dadce0;margin-bottom:10px;"></i><p class="mb-0">Tidak ada invoice Non-PKP ditemukan</p>'
                    ).show();
                    $('#npkp-table-wrapper').hide();
                }
            },
            error: function() {
                $('#npkp-loading').hide();
                $('#npkp-empty').html(
                    '<i class="fas fa-exclamation-triangle d-block" style="font-size:2.5rem;color:#f44336;margin-bottom:10px;"></i><p class="mb-0">Gagal memuat data</p>'
                ).show();
            }
        });
    }

    // ── Netting preview (client-side simulation) ──

    function updateNettingPreview() {
        const checkedNpkp = [];
        $('.npkp-checkbox:checked').each(function() {
            checkedNpkp.push({
                no_invoice: $(this).val(),
                nilai_invoice: parseFloat($(this).data('nilai'))
            });
        });

        if (checkedNpkp.length === 0) {
            $('#netting-preview').removeClass('active');
            $('#btn-process-nett').prop('disabled', true);
            return;
        }

        // Clone retur data for simulation
        let returPool = selectedReturData.map(r => ({
            no_invoice: r.no_invoice,
            remaining: r.nilai_retur
        }));

        let previewHtml = '';
        let totalOriginal = 0;
        let totalReturUsed = 0;
        let totalNett = 0;

        checkedNpkp.forEach(function(npkp) {
            let remainingNpkp = npkp.nilai_invoice;
            let returUsedForThis = 0;

            for (let i = 0; i < returPool.length; i++) {
                if (returPool[i].remaining <= 0 || remainingNpkp <= 0) continue;

                if (remainingNpkp >= returPool[i].remaining) {
                    returUsedForThis += returPool[i].remaining;
                    remainingNpkp -= returPool[i].remaining;
                    returPool[i].remaining = 0;
                } else {
                    returUsedForThis += remainingNpkp;
                    returPool[i].remaining -= remainingNpkp;
                    remainingNpkp = 0;
                }

                if (remainingNpkp <= 0) break;
            }

            const nettValue = npkp.nilai_invoice - returUsedForThis;
            totalOriginal += npkp.nilai_invoice;
            totalReturUsed += returUsedForThis;
            totalNett += nettValue;

            const nettClass = nettValue > 0 ? 'nett-result-positive' : 'nett-result-negative';

            previewHtml += `<tr>
                <td>${npkp.no_invoice}</td>
                <td class="text-end">${formatCurrency(npkp.nilai_invoice)}</td>
                <td class="text-end">${formatCurrency(returUsedForThis)}</td>
                <td class="text-end ${nettClass}">${formatCurrency(nettValue)}</td>
            </tr>`;
        });

        const totalReturRemaining = returPool.reduce((sum, r) => sum + r.remaining, 0);

        $('#netting-preview-tbody').html(previewHtml);
        $('#preview-total-original').text(formatCurrency(totalOriginal));
        $('#preview-total-retur-used').text(formatCurrency(totalReturUsed));
        $('#preview-total-nett').text(formatCurrency(totalNett));
        $('#preview-retur-remaining').text(formatCurrency(totalReturRemaining));

        $('#netting-preview').addClass('active');
        $('#btn-process-nett').prop('disabled', false);
    }

    // ── Process nett ──

    function processNett() {
        const selectedNpkpInvoices = [];
        $('.npkp-checkbox:checked').each(function() {
            selectedNpkpInvoices.push($(this).val());
        });

        if (selectedNpkpInvoices.length === 0) {
            swal('Peringatan', 'Pilih minimal satu invoice Non-PKP', 'warning');
            return;
        }

        const returInvoices = selectedReturData.map(item => item.no_invoice);

        swal({
            title: 'Konfirmasi',
            text: `Proses netting ${selectedNpkpInvoices.length} invoice Non-PKP dengan ${returInvoices.length} invoice retur. Lanjutkan?`,
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
                        npkp_invoices: selectedNpkpInvoices,
                        retur_invoices: returInvoices
                    },
                    success: function(response) {
                        if (response.status) {
                            swal('Berhasil', response.message, 'success').then(() => {
                                $('#modal-npkp').modal('hide');
                                clearReturSelection();
                                table.ajax.reload();
                                historyTable.ajax.reload();
                            });
                        } else {
                            swal('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        swal('Error', xhr.responseJSON?.message || 'Gagal melakukan proses netting',
                            'error');
                    }
                });
            }
        });
    }

    // ── Export ──

    function exportData(format) {
        swal({
            title: 'Konfirmasi',
            text: 'Export data nett invoice ke ' + format.toUpperCase() + '. Lanjutkan?',
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
