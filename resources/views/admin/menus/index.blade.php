@extends('layouts.master')

@section('title', 'Menu Management | PNL')

@section('style')
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Menu Management</h3>
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
                        <a href="#">Admin</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Menu Management</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Menus
                                <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"
                                    data-bs-target="#newMenuModal">
                                    <i class="fas fa-plus fa-fw"></i><i class="fas fa-list fa-fw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="menuTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Slug</th>
                                            <th>Route</th>
                                            <th>Parent</th>
                                            <th>Order</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- New Menu Modal --}}
            <div class="modal fade" id="newMenuModal" tabindex="-1" aria-labelledby="newMenuModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="newMenuModalLabel">New Menu</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name">Menu Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="e.g. Dashboard">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="slug">Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug"
                                            placeholder="e.g. dashboard">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="route_name">Route Name</label>
                                        <input type="text" class="form-control" id="route_name" name="route_name"
                                            placeholder="e.g. dashboard.index">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="icon">Icon Class</label>
                                        <input type="text" class="form-control" id="icon" name="icon"
                                            placeholder="e.g. fas fa-home">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="order">Order</label>
                                        <input type="number" class="form-control" id="order" name="order" value="0">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="parent_id">Parent Menu</label>
                                        <select class="form-control" id="parent_id" name="parent_id" style="width: 100%">
                                            <option value="">No Parent (Root)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="is_active">Status</label>
                                        <select class="form-control" id="is_active" name="is_active">
                                            <option value="1" selected>Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i
                                    class="fas fa-times fa-fw"></i> Close</button>
                            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fas fa-save fa-fw"></i>
                                Save</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Update Menu Modal --}}
            <div class="modal fade" id="updateMenuModal" tabindex="-1" aria-labelledby="updateMenuModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="updateMenuModalLabel">Update Menu</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_name">Menu Name</label>
                                        <input type="text" class="form-control" id="update_name" name="update_name"
                                            placeholder="e.g. Dashboard">
                                        <input type="text" id="update_id" name="update_id" hidden>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_slug">Slug</label>
                                        <input type="text" class="form-control" id="update_slug" name="update_slug"
                                            placeholder="e.g. dashboard">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_route_name">Route Name</label>
                                        <input type="text" class="form-control" id="update_route_name" name="update_route_name"
                                            placeholder="e.g. dashboard.index">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="update_icon">Icon Class</label>
                                        <input type="text" class="form-control" id="update_icon" name="update_icon"
                                            placeholder="e.g. fas fa-home">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="update_order">Order</label>
                                        <input type="number" class="form-control" id="update_order" name="update_order" value="0">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_parent_id">Parent Menu</label>
                                        <select class="form-control" id="update_parent_id" name="update_parent_id" style="width: 100%">
                                            <option value="">No Parent (Root)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_is_active">Status</label>
                                        <select class="form-control" id="update_is_active" name="update_is_active">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
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
        // Global AJAX setup for CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            // Initialize DataTable
            const menuTable = $("#menuTable").DataTable({
                processing: true,
                ajax: {
                    url: "{{ route('admin.menus.index') }}",
                    dataSrc: 'data'
                },
                columns: [{
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row) {
                            const icon = row.icon ? `<i class="${row.icon} fa-fw me-1"></i>` : '';
                            return icon + data;
                        }
                    },
                    {
                        data: 'slug',
                        name: 'slug'
                    },
                    {
                        data: 'route_name',
                        name: 'route_name',
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'parent',
                        name: 'parent',
                        render: function(data) {
                            return data ? data.name : '-';
                        }
                    },
                    {
                        data: 'order',
                        name: 'order'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data) {
                            return data ? '<span class="badge badge-success">Active</span>' :
                                '<span class="badge badge-danger">Inactive</span>';
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <i class="fas fa-edit fa-fw text-primary" title="Edit" onclick="showUpdate(${row.id})"></i>
                                <i class="fas fa-trash fa-fw text-danger ms-2" title="Delete" onclick="deleteMenu(${row.id}, '${row.name}')"></i>
                            `;
                        }
                    }
                ],
                order: [[4, 'asc']] // Order by order column
            });

            // Initialize Select2 for parent select
            $("#parent_id").select2({
                dropdownParent: $('#newMenuModal'),
                placeholder: 'Select Parent Menu...',
                allowClear: true
            });

            $("#update_parent_id").select2({
                dropdownParent: $('#updateMenuModal'),
                placeholder: 'Select Parent Menu...',
                allowClear: true
            });

            // Load parent menus
            function loadParentMenus(selectedId = null, excludeId = null) {
                $.ajax({
                    url: "{{ route('admin.menus.index') }}",
                    type: 'GET',
                    success: function(data) {
                        if (data.success && data.data) {
                            const options = '<option value="">No Parent (Root)</option>';
                            $("#parent_id").html(options);
                            $("#update_parent_id").html(options);

                            data.data.forEach(function(menu) {
                                // Don't add if it's the menu being updated (prevent circular hierarchy)
                                if (excludeId && menu.id == excludeId) return;

                                const option = new Option(menu.name, menu.id);
                                $("#parent_id").append(option.cloneNode(true));
                                $("#update_parent_id").append(option);
                            });

                            if (selectedId) {
                                $("#update_parent_id").val(selectedId).trigger('change');
                            }
                        }
                    }
                });
            }

            // Load menus initially
            loadParentMenus();

            // Refresh menus when modal opens
            $('#newMenuModal').on('shown.bs.modal', function () {
                loadParentMenus();
            });

            // Reset modal on hide
            $("#newMenuModal").on('hidden.bs.modal', function() {
                $("#name").val('');
                $("#slug").val('');
                $("#route_name").val('');
                $("#icon").val('');
                $("#order").val('0');
                $("#parent_id").val('').trigger('change');
                $("#is_active").val('1');
            });

            $("#updateMenuModal").on('hidden.bs.modal', function() {
                $("#update_id").val('');
                $("#update_name").val('');
                $("#update_slug").val('');
                $("#update_route_name").val('');
                $("#update_icon").val('');
                $("#update_order").val('');
                $("#update_parent_id").val('').trigger('change');
                $("#update_is_active").val('');
            });

            // Save new menu
            $(".btn-save").click(function() {
                const button = $(this);
                const originalText = button.html();
                const name = $("#name").val();
                const slug = $("#slug").val();
                const route_name = $("#route_name").val();
                const icon = $("#icon").val();
                const order = $("#order").val();
                const parent_id = $("#parent_id").val();
                const is_active = $("#is_active").val();

                if (!name || !slug) {
                    swal("Error!", "Please fill in required fields (Name, Slug)", "error");
                    return;
                }

                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: "{{ route('admin.menus.store') }}",
                    type: 'POST',
                    data: {
                        name: name,
                        slug: slug,
                        route_name: route_name,
                        icon: icon,
                        order: order,
                        parent_id: parent_id,
                        is_active: is_active
                    },
                    success: function(data) {
                        button.prop('disabled', false).html(originalText);
                        if (data.success) {
                            $("#newMenuModal").modal('hide');
                            swal("Success!", data.message, "success");
                            menuTable.ajax.reload();
                            loadParentMenus(); // Reload parent options
                        } else {
                            swal("Error!", data.message, "error");
                        }
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html(originalText);
                        const message = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong";
                        swal("Error!", message, "error");
                    }
                });
            });

            // Update menu
            $(".btn-update").click(function() {
                const button = $(this);
                const originalText = button.html();
                const id = $("#update_id").val();
                const name = $("#update_name").val();
                const slug = $("#update_slug").val();
                const route_name = $("#update_route_name").val();
                const icon = $("#update_icon").val();
                const order = $("#update_order").val();
                const parent_id = $("#update_parent_id").val();
                const is_active = $("#update_is_active").val();

                if (!name || !slug) {
                    swal("Error!", "Please fill in required fields (Name, Slug)", "error");
                    return;
                }

                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

                $.ajax({
                    url: "{{ route('admin.menus.update', '__id__') }}".replace('__id__', id),
                    type: 'PUT',
                    data: {
                        name: name,
                        slug: slug,
                        route_name: route_name,
                        icon: icon,
                        order: order,
                        parent_id: parent_id,
                        is_active: is_active
                    },
                    success: function(data) {
                        button.prop('disabled', false).html(originalText);
                        if (data.success) {
                            $("#updateMenuModal").modal('hide');
                            swal("Success!", data.message, "success");
                            menuTable.ajax.reload();
                            loadParentMenus();
                        } else {
                            swal("Error!", data.message, "error");
                        }
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html(originalText);
                        const message = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong";
                        swal("Error!", message, "error");
                    }
                });
            });
        });

        // Show update modal
        function showUpdate(id) {
             // RE-WRITING showUpdate to handle parent loading independently to be safe
             $.ajax({
                url: "{{ route('admin.menus.index') }}",
                type: 'GET',
                success: function(res) {
                    if (res.success && res.data) {
                        const options = '<option value="">No Parent (Root)</option>';
                        $("#update_parent_id").html(options);
                        
                        // We need to fetch the specific menu details first
                        $.ajax({
                            url: "{{ route('admin.menus.show', '__id__') }}".replace('__id__', id),
                            type: 'GET',
                            success: function(data) {
                                if (data.success) {
                                    const menu = data.data;
                                    
                                    res.data.forEach(function(m) {
                                        if (m.id == id) return; // Exclude self
                                        $("#update_parent_id").append(new Option(m.name, m.id));
                                    });
                                    
                                    $("#update_id").val(menu.id);
                                    $("#update_name").val(menu.name);
                                    $("#update_slug").val(menu.slug);
                                    $("#update_route_name").val(menu.route_name);
                                    $("#update_icon").val(menu.icon);
                                    $("#update_order").val(menu.order);
                                    $("#update_is_active").val(menu.is_active ? '1' : '0');
                                    
                                    $("#update_parent_id").val(menu.parent_id).trigger('change');
                                    $("#updateMenuModal").modal('show');
                                }
                            }
                        });
                    }
                }
            });
        }

        // Delete menu
        function deleteMenu(id, name) {
            swal({
                title: "Are you sure?",
                text: "Delete menu: " + name + "? This will also delete all submenus if any.",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Cancel",
                        value: false,
                        visible: true,
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
                        url: "{{ route('admin.menus.destroy', '__id__') }}".replace('__id__', id),
                        type: 'DELETE',
                        success: function(data) {
                            if (data.success) {
                                swal("Success!", data.message, "success");
                                $("#menuTable").DataTable().ajax.reload();
                            } else {
                                swal("Error!", data.message, "error");
                            }
                        },
                        error: function(xhr) {
                            swal("Error!", "Something went wrong", "error");
                        }
                    });
                }
            });
        }
    </script>
@endsection
