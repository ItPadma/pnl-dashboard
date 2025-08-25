<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Captcha Preview</title>
    <link rel="stylesheet" href="{{ asset('assets/js/plugin/sweetalert/sweetalert2.min.css') }}">
</head>

<body>
    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert2.all.min.js') }}"></script>
    <script>
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
                    if (response.status && response.data && response.data.token) {
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
                    if (response.status && response.data && response.data.token) {
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
    <script>
        $(document).ready(function() {
            // Refresh tokens on page load
            TokenManager.refreshSwalToken();

            Swal.fire({
                // get from url query
                text: "{{ request()->query('procname') }}",
                imageUrl: "{{ asset('') }}" + "{{ request()->query('image') }}",
                imageAlt: 'Captcha Image',
                input: 'text',
                inputPlaceholder: 'Enter captcha...',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: (captcha) => {
                    if (!captcha) {
                        Swal.showValidationMessage('Please enter the captcha');
                        return false;
                    }
                    $.ajax({
                        url: "{{ route('pnl.setting.coretax.captcha') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': TokenManager.swalToken
                        },
                        data: {
                            captcha: captcha
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Captcha submitted successfully',
                                    text: response.message,
                                    allowOutsideClick: false
                                }).then(() => {
                                    // close this tab
                                    window.close();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    allowOutsideClick: false
                                });
                            }
                        },
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Captcha submitted',
                        text: 'Waiting for process...',
                        allowOutsideClick: false
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Captcha submission canceled',
                        text: 'You can try again later.',
                        allowOutsideClick: false
                    });
                }
            });
        });
    </script>
</body>

</html>
