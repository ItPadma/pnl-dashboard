@extends('layouts.master')

@section('title', 'PNL - User Management')

@section('style')
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">User Management</h3>
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
                        <a href="#">Setting</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">User Manager</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Users
                                <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"
                                    data-bs-target="#newUserModal">
                                    <i class="fas fa-plus fa-fw"></i><i class="fas fa-user fa-fw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Depo</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- New User Modal --}}
            <div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="newUserModalLabel">User Baru</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name">Nama</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Your name">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="text" class="form-control" id="email" name="email"
                                            placeholder="mail@example.com">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Password">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <select class="form-control" id="role" name="role">
                                            <option value="user">User</option>
                                            <option value="superuser">Superuser</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="depo">Depo</label>
                                        <select class="form-control" id="depo" name="depo[]" multiple="multiple"
                                            style="width: 100%">
                                            <option value="all">--ALL--</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i
                                    class="fas fa-times fa-fw"></i> Close</button>
                            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fas fa-save fa-fw"></i>
                                Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Update User Modal --}}
            <div class="modal fade" id="updateUserModal" tabindex="-1" aria-labelledby="updateUserModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="updateUserModalLabel">Update User</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_name">Nama</label>
                                        <input type="text" class="form-control" id="update_name" name="update_name"
                                            placeholder="Your name">
                                        <input type="text" id="update_id" name="update_id" hidden>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_email">Email</label>
                                        <input type="text" class="form-control" id="update_email" name="update_email"
                                            placeholder="mail@example.com">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_password">Password</label>
                                        <input type="password" class="form-control" id="update_password"
                                            name="update_password" placeholder="Password">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_role">Role</label>
                                        <select class="form-control" id="update_role" name="update_role">
                                            <option value="user">User</option>
                                            <option value="superuser">Superuser</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_depo">Depo</label>
                                        <select class="form-control" id="update_depo" name="update_depo[]"
                                            multiple="multiple" style="width: 100%">
                                            <option value="all">--ALL--</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i
                                    class="fas fa-times fa-fw"></i> Close</button>
                            <button type="button" class="btn btn-primary btn-sm btn-update"><i
                                    class="fas fa-save fa-fw"></i>
                                Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#userTable").DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('pnl.master-data.users') }}",
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'role',
                        name: 'role'
                    },
                    {
                        data: null,
                        name: 'depo',
                        render: function(data, type, row) {
                            if (row.depo && typeof row.depo === 'string') {
                                var depo = row.depo.split("|");
                                var depoName = '';
                                $.each(depo, function(key, value) {
                                    depoName += value + ', ';
                                });
                                return depoName.slice(0, -2);
                            } else {
                                return '-';
                            }
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var currentUser = "{{ Auth::user()->id }}";
                            var actions =
                                '<i class="fas fa-edit fa-fw text-primary" data-bs-toggle="modal" data-bs-target="#editUserModal" data-id="' +
                                row.id + '" onclick="showUpdate(\'' + row.id + '\')"></i>';

                            // Only show delete button if the current user is NOT a superuser
                            if (currentUser != row.id) {
                                actions +=
                                    '<i class="fas fa-trash fa-fw text-danger ms-2" data-id="' + row
                                    .id +
                                    '" onclick="deleteUser(\'' + row.id + '\', \'' + row.name +
                                    '\')"></i>';
                            }

                            return actions;
                        }
                    }
                ]
            });

            $("#depo").select2({
                dropdownParent: $('#newUserModal')
            });

            $("#update_depo").select2({
                dropdownParent: $('#updateUserModal')
            });

            $.ajax({
                url: "{{ route('pnl.master-data.depos') }}",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $.each(data.data, function(key, value) {
                        $("#depo").append('<option value="' + value.code + '">' + value.name +
                            '</option>');
                        $("#update_depo").append('<option value="' + value.code + '">' + value
                            .name +
                            '</option>');
                    });
                },
                error: function(data) {
                    console.log(data);
                }
            });

            // reset input for modal newUserModal
            $("#newUserModal").on('hidden.bs.modal', function() {
                $("#name").val('');
                $("#email").val('');
                $("#password").val('');
                $("#role").val('user');
                $("#depo").val(null).trigger('change');
            });

            // reset input for modal updateUserModal
            $("#updateUserModal").on('hidden.bs.modal', function() {
                $("#update_id").val('');
                $("#update_name").val('');
                $("#update_email").val('');
                $("#update_password").val('');
                $("#update_role").val('');
                $("#update_depo").val(null).trigger('change');
            });

            // save data
            $(".btn-save").click(function() {
                var name = $("#name").val();
                var email = $("#email").val();
                var password = $("#password").val();
                var role = $("#role").val();
                var depo = $("#depo").val();

                if (name == "" || email == "" || password == "" || role == "" || depo == "") {
                    swal({
                        title: "Error!",
                        text: "Please fill all fields",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                $.ajax({
                    url: "{{ route('pnl.setting.userman.store') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        name: name,
                        email: email,
                        password: password,
                        role: role,
                        depo: depo
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.status) {
                            $("#newUserModal").modal('hide');
                            swal({
                                title: "Success!",
                                text: "User has been created successfully",
                                icon: "success",
                                button: "OK",
                            }).then(function() {
                                $("#userTable").DataTable().ajax.reload();
                            });
                        } else {
                            swal({
                                title: "Error!",
                                text: data.message,
                                icon: "error",
                                button: "OK",
                            });
                        }
                    },
                    error: function(data) {
                        console.log(data);
                        swal({
                            title: "Error!",
                            text: "Something went wrong while creating the user",
                            icon: "error",
                            button: "OK",
                        });
                    }
                });
            });

            // update data
            $(".btn-update").click(function() {
                var id = $("#update_id").val();
                var name = $("#update_name").val();
                var email = $("#update_email").val();
                var password = $("#update_password").val();
                var role = $("#update_role").val();
                var originalRole = $("#update_role").data('original-value');
                var depo = $("#update_depo").val();

                if (name == "" || email == "" || role == "" || depo == "") {
                    swal({
                        title: "Error!",
                        text: "Please fill all fields",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                // Check if role is being changed
                if (role !== originalRole) {
                    swal({
                        title: "Warning!",
                        text: "You are about to change the user's role. This may affect their permissions and access rights. Are you sure you want to continue?",
                        icon: "warning",
                        buttons: {
                            cancel: {
                                text: "Cancel",
                                value: false,
                                visible: true,
                                className: "",
                                closeModal: true,
                            },
                            confirm: {
                                text: "Yes, change it!",
                                value: true,
                                visible: true,
                                className: "btn-warning",
                                closeModal: false
                            }
                        },
                        dangerMode: true,
                    }).then((willUpdate) => {
                        if (willUpdate) {
                            updateUser(id, name, email, password, role, depo);
                        }
                    });
                } else {
                    // If role is not changed, proceed with update
                    updateUser(id, name, email, password, role, depo);
                }
            });

            // Extract the update functionality to a separate function
            function updateUser(id, name, email, password, role, depo) {
                $.ajax({
                    url: "{{ route('pnl.setting.userman.update') }}",
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        id: id,
                        name: name,
                        email: email,
                        password: password,
                        role: role,
                        depo: depo
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.status) {
                            $("#updateUserModal").modal('hide');
                            swal({
                                title: "Success!",
                                text: "User has been updated successfully",
                                icon: "success",
                                button: "OK",
                            }).then(function() {
                                $("#userTable").DataTable().ajax.reload();
                            });
                        } else {
                            swal({
                                title: "Error!",
                                text: data.message,
                                icon: "error",
                                button: "OK",
                            });
                        }
                    },
                    error: function(data) {
                        console.log(data);
                        swal({
                            title: "Error!",
                            text: "Something went wrong while updating the user",
                            icon: "error",
                            button: "OK",
                        });
                    }
                });
            }
        });

        // showUpdate()
        function showUpdate(id) {
            $.ajax({
                url: "{{ route('pnl.setting.userman.show') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(data) {
                    $("#update_id").val(data.data.id);
                    $("#update_name").val(data.data.name);
                    $("#update_email").val(data.data.email);
                    $("#update_role").val(data.data.role).data('original-value', data.data.role);
                    // split data.data.depo to array then apply to select2 as selected multiple, skip if null
                    if (data.data.depo && typeof data.data.depo === 'string') {
                        var depo = data.data.depo.split("|");
                        $("#update_depo").val(null);
                        $("#update_depo").val(depo).trigger('change');
                    } else {
                        $("#update_depo").val(null).trigger('change');
                    }
                    $("#updateUserModal").modal('show');
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }

        // delete data
        function deleteUser(id, name) {
            swal({
                title: "Are you sure?",
                text: "Are you sure you want to delete this user: " + name + "?",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Cancel",
                        value: false,
                        visible: true,
                        className: "",
                        closeModal: true,
                    },
                    confirm: {
                        text: "Yes, delete it!",
                        value: true,
                        visible: true,
                        className: "btn-danger",
                        closeModal: false
                    }
                },
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('pnl.setting.userman.destroy') }}",
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.status) {
                                swal("Success!", "User has been deleted successfully.", "success");
                                $("#userTable").DataTable().ajax.reload();
                            } else {
                                swal("Error!", data.message, "error");
                            }
                        },
                        error: function(data) {
                            console.log(data);
                            swal("Error!", "Something went wrong while deleting the user.", "error");
                        }
                    });
                }
            });
        }
    </script>
@endsection
