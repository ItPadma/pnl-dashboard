<script>
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
</script>
