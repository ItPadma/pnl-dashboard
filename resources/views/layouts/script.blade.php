<script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/chart-circle/circles.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/jsvectormap/world.js') }}"></script>
<script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#changepass').on('click', function(e) {
            e.preventDefault();

            swal({
                title: "Change Password",
                text: "Please enter your new password",
                content: {
                    element: "input",
                    attributes: {
                        placeholder: "Type your new password",
                        type: "password",
                    },
                },
                buttons: {
                    cancel: {
                        text: "Cancel",
                        value: null,
                        visible: true,
                        className: "",
                        closeModal: true,
                    },
                    confirm: {
                        text: "Change Password",
                        value: true,
                        visible: true,
                        className: "btn-primary",
                        closeModal: false
                    }
                },
            }).then((password) => {
                if (password) {
                    // Validate password
                    if (password.length < 6) {
                        swal({
                            title: "Error!",
                            text: "Password must be at least 6 characters long",
                            icon: "error",
                            button: "OK",
                        });
                        return;
                    }

                    // Send AJAX request to change password
                    $.ajax({
                        url: "{{ route('pnl.setting.userman.changepassword') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            password: password
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.status) {
                                swal({
                                    title: "Success!",
                                    text: "Your password has been changed successfully",
                                    icon: "success",
                                    button: "OK",
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: response.message ||
                                        "Failed to change password",
                                    icon: "error",
                                    button: "OK",
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            swal({
                                title: "Error!",
                                text: "Something went wrong while changing your password",
                                icon: "error",
                                button: "OK",
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@yield('script')
