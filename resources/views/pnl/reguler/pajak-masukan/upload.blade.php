@extends('layouts.master')

@section('title', 'Pajak Masukan - Upload CSV Coretax')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/js/plugin/sweetalert/sweetalert2.min.css') }}">
<script>
    // Use a TokenManager to handle tokens properly
    const TokenManager = {
        gatheringToken: '{{ csrf_token() }}',
        swalToken: '{{ csrf_token() }}',

        refreshGatheringToken: function() {
            return $.ajax({
                url: "{{ route('pnl.setting.generate.csrf.token') }}",
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.gatheringToken
                }
            }).then(response => {
                if(response.status && response.data && response.data.token) {
                    this.gatheringToken = response.data.token;
                    console.log('Gathering token refreshed:', this.gatheringToken);
                }
                return this.gatheringToken;
            }).catch(error => {
                console.error('Failed to refresh gathering token:', error);
                return this.gatheringToken;
            });
        },

        refreshSwalToken: function() {
            return $.ajax({
                url: "{{ route('pnl.setting.generate.csrf.token') }}",
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.swalToken
                }
            }).then(response => {
                if(response.status && response.data && response.data.token) {
                    this.swalToken = response.data.token;
                    console.log('SwalToken refreshed:', this.swalToken);
                }
                return this.swalToken;
            }).catch(error => {
                console.error('Failed to refresh swal token:', error);
                return this.swalToken;
            });
        }
    };
</script>
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Upload XLSX Coretax</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="#">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Reguler</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Pajak Masukan</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Upload XLSX Coretax</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="#" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="csv_file" class="form-label">Upload <b>.xlsx</b> File</label>
                                    <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv"
                                        required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-fw fa-upload"></i>
                                    Upload</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h3>CoreTax Data Gathering</h3>
                            <div class="mb-3">
                                <label for="tipe-pajak" class="form-label">Tipe Pajak</label>
                                <select id="jenis-pajak" class="form-select" aria-label="Tipe Pajak">
                                    <option selected>Pilih Tipe Pajak...</option>
                                    <option value="1">Pajak Keluaran</option>
                                    <option value="2">Pajak Masukan</option>
                                </select>
                            </div>
                            <div class="btn-group mb-3">
                                <button class="btn btn-primary btn-sm" id="start-gathering">
                                    <i class="fas fa-fw fa-sync icon-btn"></i>
                                    <span class="spinner-border spinner-border-sm sp-start" role="status" hidden></span>
                                    Start
                                </button>
                                <button class="btn btn-danger btn-sm" id="stop-gathering">
                                    <i class="fas fa-fw fa-stop icon-btn"></i>
                                    <span class="spinner-border spinner-border-sm sp-stop" role="status" hidden></span>
                                    Stop
                                </button>
                            </div>
                            <p class="ms-2">Progress: <span id="progress-text">idle</span></p>
                            <div id="progress-container">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('echo-script')
    const channelData = window.Echo.private(`App.User.Data.${userID}`);
    console.log('Channel User.Data created successfully');
    channelData.listen('.user.data', async function (response) {
        // convert data to javascript object
        switch (response.ntype) {
            case 'success':
                if (response.procname === 'CoreTax Captcha'){
                    // open new tab and focus to it.
                    let newtab = window.open("{{ route('pnl.setting.coretax.captcha.preview' ) }}?procname=" + response.procname + "&image=" + response.data, '_blank');
                    if (newtab) {
                        newtab.focus();
                    } else {
                        toastr.warning('Popup captcha gagal dibuka. Silakan periksa pengaturan popup browser Anda.');
                    }
                }
                break;
            case 'error':
                toastr.error(response.message, response.title);
                break;
            default:
                console.warn('Unknown notification type:', response.type);
        }
    });

    const channelProgress = window.Echo.private(`App.User.Progress.${userID}`);
    console.log('Channel User.Proc.Progress created successfully');
    channelProgress.listen('.user.proc.progress', (response) => {
        console.log('Progress update received:', response);
        if (response.ntype === 'info') {
            // Update progress bar
            $('.progress-bar').css('width', response.progress + '%');
            $('.progress-bar').attr('aria-valuenow', response.progress);
            $('.progress-bar').text(`${response.progress}%`);
            $('#progress-text').text(`${response.message}`);
        }
    });
@endsection

@section('script')
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert2.all.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            document.addEventListener('DOMContentLoaded', function(event) {
                // Show the progress container when the page loads
                $('#progress-container').removeAttr('hidden');
            });

            $('#start-gathering').on('click', function() {
                const $btn = $(this);

                $btn.attr('disabled', true);
                $('#stop-gathering').attr('disabled', false);
                $('.sp-start').removeAttr('hidden');
                $('.sp-stop').attr('hidden', true);
                $('.icon-btn').attr('hidden', true);
                $('#progress-container').removeAttr('hidden');
                $('.progress-bar').css('width', '0%');
                $('.progress-bar').attr('aria-valuenow', '0');
                $('.progress-bar').text('0%');


                // Get a fresh token for this request (doesn't block execution)
                TokenManager.refreshGatheringToken();

                $.ajax({
                    url: "{{ route('pnl.setting.coretax.gathering') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': TokenManager.gatheringToken
                    },
                    data: {
                        jenis_pajak: $('#jenis-pajak').val()
                    },
                    beforeSend: function() {
                        // Show spinner
                        $('.sp-start').removeAttr('hidden');
                        $('.icon-btn').attr('hidden', true);
                    },
                    success: function(response) {
                        // Do something with the response
                        // enable buttons
                        $btn.removeAttr('disabled');
                        $('#stop-gathering').attr('disabled', true);
                        // Show success message
                        toastr.success(response.message, 'Success');
                    },
                    error: function(xhr, status, error) {
                        // enable buttons
                        $btn.removeAttr('disabled');
                        $('#stop-gathering').attr('disabled', true);
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Data gathering failed.',
                        });
                    },
                    complete: function() {
                        // Stop the spinner
                        $('.sp-start').attr('hidden', true);
                        $('.icon-btn').removeAttr('hidden');
                    }
                });
            });

        });
    </script>
@endsection
