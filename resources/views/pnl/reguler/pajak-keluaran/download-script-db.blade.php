<script>
    function downloadCheckedData(tipe) {
        $.ajax({
            url: "{{ route('pnl.reguler.pajak-keluaran-db.download') }}?tipe=" + tipe,
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
                var blob = new Blob([response], {
                    type: xhr.getResponseHeader('Content-Type')
                });

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
                $('#sp-' + tipe).hide();
                $('.fa-download').show();

                // reload the table and refresh all counters in a single request
                switch (tipe) {
                    case 'pkp':
                        if (typeof tablePkpDb !== 'undefined' && tablePkpDb) {
                            tablePkpDb.ajax.reload();
                        }
                        break;

                    case 'pkpnppn':
                        if (typeof tablePkpDbNppn !== 'undefined' && tablePkpDbNppn) {
                            tablePkpDbNppn.ajax.reload();
                        }
                        break;

                    case 'npkp':
                        if (typeof tableNonPkpDb !== 'undefined' && tableNonPkpDb) {
                            tableNonPkpDb.ajax.reload();
                        }
                        break;

                    case 'npkpnppn':
                        if (typeof tableNonPkpDbNppn !== 'undefined' && tableNonPkpDbNppn) {
                            tableNonPkpDbNppn.ajax.reload();
                        }
                        break;

                    case 'retur':
                        if (typeof tableReturDb !== 'undefined' && tableReturDb) {
                            tableReturDb.ajax.reload();
                        }
                        break;

                    case 'nonstandar':
                        if (typeof tableNonStandarDb !== 'undefined' && tableNonStandarDb) {
                            tableNonStandarDb.ajax.reload();
                        }
                        break;

                    case 'pembatalan':
                        if (typeof tablePembatalanDb !== 'undefined' && tablePembatalanDb) {
                            tablePembatalanDb.ajax.reload();
                        }
                        break;

                    case 'koreksi':
                        if (typeof tableKoreksiDb !== 'undefined' && tableKoreksiDb) {
                            tableKoreksiDb.ajax.reload();
                        }
                        break;

                    case 'pending':
                        if (typeof tablePendingDb !== 'undefined' && tablePendingDb) {
                            tablePendingDb.ajax.reload();
                        }
                        break;

                    default:
                        if (typeof tablePkpDb !== 'undefined' && tablePkpDb) tablePkpDb.ajax.reload();
                        if (typeof tablePkpDbNppn !== 'undefined' && tablePkpDbNppn) tablePkpDbNppn.ajax.reload();
                        if (typeof tableNonPkpDb !== 'undefined' && tableNonPkpDb) tableNonPkpDb.ajax.reload();
                        if (typeof tableNonPkpDbNppn !== 'undefined' && tableNonPkpDbNppn) tableNonPkpDbNppn.ajax.reload();
                        if (typeof tableReturDb !== 'undefined' && tableReturDb) tableReturDb.ajax.reload();
                        if (typeof tableNonStandarDb !== 'undefined' && tableNonStandarDb) tableNonStandarDb.ajax.reload();
                        if (typeof tablePembatalanDb !== 'undefined' && tablePembatalanDb) tablePembatalanDb.ajax.reload();
                        if (typeof tableKoreksiDb !== 'undefined' && tableKoreksiDb) tableKoreksiDb.ajax.reload();
                        if (typeof tablePendingDb !== 'undefined' && tablePendingDb) tablePendingDb.ajax.reload();
                        break;
                }

                // Single request for all counters instead of 9 separate calls
                setAllDownloadCounters();
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                $('#sp-' + tipe).hide();
                $('.fa-download').show();
                toastr.error('Gagal mendownload data. Silakan coba lagi.', 'Error');
            }
        });
    }

    /**
     * Fetch ALL tipe counters in a single request (replaces 9 separate setDownloadCounter calls).
     */
    function setAllDownloadCounters() {
        var tipes = ['pkp', 'pkpnppn', 'npkp', 'npkpnppn', 'retur', 'nonstandar', 'pembatalan', 'koreksi', 'pending'];
        tipes.forEach(function(tipe) {
            toggleSpinnerDownload(tipe, true);
        });

        $.ajax({
            url: "{{ route('pnl.reguler.pajak-keluaran.count-all') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                pt: $('#filter_pt').val(),
                brand: $('#filter_brand').val(),
                depo: $('#filter_depo').val(),
                periode: $('#filter_periode').val(),
                chstatus: 'checked-ready2download'
            },
            success: function(response) {
                if (!response.status || !response.data) {
                    tipes.forEach(function(tipe) {
                        toggleSpinnerDownload(tipe, false);
                    });
                    return;
                }

                var data = response.data;
                tipes.forEach(function(tipe) {
                    var counts = data[tipe] || {};
                    var ready = counts.ready2download_count || 0;
                    var downloaded = counts.downloaded_count || 0;

                    $('#total_ready2download_' + tipe).text(ready);
                    $('#total_downloaded_' + tipe).text(downloaded);

                    if (parseInt(ready) > 0) {
                        $('#btn-download-' + tipe).prop('hidden', false);
                    } else {
                        $('#btn-download-' + tipe).prop('hidden', true);
                    }

                    toggleSpinnerDownload(tipe, false);
                });
            },
            error: function(error) {
                console.error('Error:', error);
                tipes.forEach(function(tipe) {
                    toggleSpinnerDownload(tipe, false);
                });
            }
        });
    }

    /**
     * Fetch a single tipe counter (kept for backward compatibility).
     */
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
                if (parseInt(response.data[0].ready2download_count ?? 0) > 0) {
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

    function downloadFilteredData() {
        // Use getSelectedTipeValues() from main-script-db.blade.php
        const tipe = typeof getSelectedTipeValues === 'function' ? getSelectedTipeValues() : ['all'];
        const params = {
            tipe: tipe,
            pt: $('#filter_pt').val(),
            brand: $('#filter_brand').val(),
            depo: $('#filter_depo').val(),
            periode: $('#filter_periode').val(),
            chstatus: $('#filter_chstatus').val()
        };

        $.ajax({
            url: "{{ route('pnl.reguler.pajak-keluaran-db.download') }}",
            method: 'GET',
            data: params,
            xhrFields: {
                responseType: 'blob'
            },
            beforeSend: function() {
                $('#btn-download-filtered').prop('disabled', true);
                $('#sp-download-filtered').prop('hidden', false);
            },
            success: function(response, status, xhr) {
                const blob = new Blob([response], {
                    type: xhr.getResponseHeader('Content-Type')
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;

                let filename = 'pajak_keluaran_filtered.xlsx';
                const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                if (contentDisposition && contentDisposition.indexOf('attachment') !== -1) {
                    const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }
                a.download = filename;

                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            },
            error: function(error) {
                console.error('Error:', error);
                toastr.error('Gagal mendownload data. Silakan coba lagi.', 'Error');
            },
            complete: function() {
                $('#btn-download-filtered').prop('disabled', false);
                $('#sp-download-filtered').prop('hidden', true);
            }
        });
    }
</script>
