@extends('layouts.master')

@section('title', 'PNL - Access Groups')

@section('style')
<style>
    /* Select2 Bootstrap 5 compatibility */
    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 4px 8px;
    }
    .select2-container--default .select2-selection--multiple:focus,
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd;
        border: none;
        color: #fff;
        border-radius: 0.25rem;
        padding: 2px 8px;
        margin: 2px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 5px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
        background: transparent;
    }
    .select2-dropdown {
        border-color: #ced4da;
        z-index: 1060;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #0d6efd;
    }
    .select2-container--default .select2-search--inline .select2-search__field {
        margin-top: 6px;
    }
</style>
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Access Groups</h3>
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
                        <a href="#">Access Groups</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Access Groups
                                <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"
                                    data-bs-target="#newGroupModal">
                                    <i class="fas fa-plus fa-fw"></i><i class="fas fa-users-cog fa-fw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="groupTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Default Level</th>
                                            <th>Users</th>
                                            <th>Menus</th>
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

            {{-- New Group Modal --}}
            <div class="modal fade" id="newGroupModal" tabindex="-1" aria-labelledby="newGroupModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="newGroupModalLabel">New Access Group</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name">Group Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="e.g. Finance Team">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"
                                            placeholder="Describe the purpose of this group"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="default_access_level">Default Access Level</label>
                                        <select class="form-control" id="default_access_level" name="default_access_level">
                                            <option value="0">No Access</option>
                                            <option value="1" selected>Read Only</option>
                                            <option value="2">Read & Write</option>
                                            <option value="3">Full Access</option>
                                            <option value="4">Admin</option>
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

            {{-- Update Group Modal --}}
            <div class="modal fade" id="updateGroupModal" tabindex="-1" aria-labelledby="updateGroupModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="updateGroupModalLabel">Update Access Group</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_name">Group Name</label>
                                        <input type="text" class="form-control" id="update_name" name="update_name"
                                            placeholder="e.g. Finance Team">
                                        <input type="text" id="update_id" name="update_id" hidden>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_description">Description</label>
                                        <textarea class="form-control" id="update_description" name="update_description" rows="3"
                                            placeholder="Describe the purpose of this group"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="update_default_access_level">Default Access Level</label>
                                        <select class="form-control" id="update_default_access_level" name="update_default_access_level">
                                            <option value="0">No Access</option>
                                            <option value="1">Read Only</option>
                                            <option value="2">Read & Write</option>
                                            <option value="3">Full Access</option>
                                            <option value="4">Admin</option>
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

            {{-- Manage Users Modal --}}
            <div class="modal fade" id="manageUsersModal" tabindex="-1" aria-labelledby="manageUsersModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="manageUsersModalLabel">Manage Users</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="manage_group_id">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <select class="form-control" id="user_select" style="width: 100%">
                                        <option value="">Select User...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" id="custom_level">
                                        <option value="">Use Group Default</option>
                                        <option value="0">No Access</option>
                                        <option value="1">Read Only</option>
                                        <option value="2">Read & Write</option>
                                        <option value="3">Full Access</option>
                                        <option value="4">Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-primary btn-sm btn-add-user">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped" id="groupUsersTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Custom Level</th>
                                            <th>Effective Level</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                <i class="fas fa-times fa-fw"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Manage Menus Modal --}}
            <div class="modal fade" id="manageMenusModal" tabindex="-1" aria-labelledby="manageMenusModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="manageMenusModalLabel">Manage Menus</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="manage_menu_group_id">
                            <div class="row mb-3">
                                <div class="col-md-11">
                                    <select class="form-control" id="menu_select" style="width: 100%" multiple="multiple">
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-primary btn-sm btn-add-menu">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped" id="groupMenusTable">
                                    <thead>
                                        <tr>
                                            <th>Menu Name</th>
                                            <th>Slug</th>
                                            <th>Route</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                <i class="fas fa-times fa-fw"></i> Close
                            </button>
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
        // Global CSRF token management
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        // Function to refresh CSRF token
        function refreshCsrfToken() {
            return $.ajax({
                url: "{{ route('pnl.setting.generate.csrf.token') }}",
                type: 'GET',
                async: false,
                success: function(data) {
                    if (data.success && data.data.csrf_token) {
                        csrfToken = data.data.csrf_token;
                        $('meta[name="csrf-token"]').attr('content', csrfToken);
                        // Update global AJAX setup
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                    }
                }
            });
        }

        // Function to get current CSRF token
        function getCsrfToken() {
            return csrfToken;
        }

        // Global AJAX setup with error handling
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            },
            error: function(xhr, status, error) {
                if (xhr.status === 419) {
                    // CSRF token mismatch, refresh and retry
                    refreshCsrfToken();
                }
            }
        });

        // Request throttling
        let requestQueue = [];
        let isProcessingQueue = false;
        
        function throttledAjax(options) {
            return new Promise((resolve, reject) => {
                requestQueue.push({ options, resolve, reject });
                processQueue();
            });
        }
        
        function processQueue() {
            if (isProcessingQueue || requestQueue.length === 0) return;
            
            isProcessingQueue = true;
            const { options, resolve, reject } = requestQueue.shift();
            
            ajaxWithCsrfRetry(options)
                .done(resolve)
                .fail(reject)
                .always(() => {
                    setTimeout(() => {
                        isProcessingQueue = false;
                        processQueue();
                    }, 100); // 100ms delay between requests
                });
        }

        // Helper function for AJAX requests with CSRF retry
        function ajaxWithCsrfRetry(options, retryCount = 0) {
            const maxRetries = 2;
            
            return $.ajax($.extend({}, options, {
                headers: $.extend({}, options.headers, {
                    'X-CSRF-TOKEN': getCsrfToken()
                }),
                data: $.extend({}, options.data, {
                    _token: getCsrfToken()
                })
            })).fail(function(xhr) {
                if (xhr.status === 419 && retryCount < maxRetries) {
                    // Refresh token and retry
                    refreshCsrfToken();
                    return ajaxWithCsrfRetry(options, retryCount + 1);
                }
                // If not CSRF error or max retries reached, call original error handler
                if (options.error) {
                    options.error(xhr);
                }
            });
        }

        // Cache for loaded data
        let usersCache = null;
        let menusCache = null;

        $(document).ready(function() {
            // Initialize DataTable
            const groupTable = $("#groupTable").DataTable({
                processing: true,
                ajax: {
                    url: "{{ route('admin.access-groups.index') }}",
                    dataSrc: 'data'
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description',
                        render: function(data) {
                            return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '-';
                        }
                    },
                    {
                        data: 'access_level_name',
                        name: 'access_level_name'
                    },
                    {
                        data: 'users_count',
                        name: 'users_count'
                    },
                    {
                        data: 'menus_count',
                        name: 'menus_count'
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
                                <i class="fas fa-users fa-fw text-info" title="Manage Users" onclick="showManageUsers(${row.id})"></i>
                                <i class="fas fa-list fa-fw text-success ms-2" title="Manage Menus" onclick="showManageMenus(${row.id})"></i>
                                <i class="fas fa-edit fa-fw text-primary ms-2" title="Edit" onclick="showUpdate(${row.id})"></i>
                                <i class="fas fa-trash fa-fw text-danger ms-2" title="Delete" onclick="deleteGroup(${row.id}, '${row.name}')"></i>
                            `;
                        }
                    }
                ]
            });

            // Initialize/Refresh Select2 when Manage Users modal is SHOWN
            $('#manageUsersModal').on('shown.bs.modal', function() {
                loadUsers(); // Load users only when needed
                $("#user_select").select2({
                    dropdownParent: $('#manageUsersModal'),
                    placeholder: 'Select User...',
                    width: '100%'
                });
            });

            // Load users for select (lazy loading with caching)
            function loadUsers() {
                if (usersCache) {
                    // Use cached data
                    usersCache.forEach(function(user) {
                        if ($("#user_select option[value='" + user.id + "']").length === 0) {
                            $("#user_select").append(new Option(user.name + ' (' + user.email + ')', user.id));
                        }
                    });
                    return;
                }
                
                if ($("#user_select option").length <= 1) {
                    throttledAjax({
                        url: "{{ route('pnl.master-data.users') }}",
                        type: 'GET',
                        success: function(data) {
                            if (data.data) {
                                usersCache = data.data; // Cache the data
                                data.data.forEach(function(user) {
                                    $("#user_select").append(new Option(user.name + ' (' + user.email + ')', user.id));
                                });
                            }
                        }
                    });
                }
            }

            // Load menus for select (lazy loading with caching)
            function loadMenus() {
                if (menusCache) {
                    // Use cached data
                    menusCache.forEach(function(menu) {
                        if ($("#menu_select option[value='" + menu.id + "']").length === 0) {
                            $("#menu_select").append(new Option(menu.name, menu.id));
                        }
                    });
                    return;
                }
                
                if ($("#menu_select option").length === 0) {
                    throttledAjax({
                        url: "{{ route('admin.menus.index') }}",
                        type: 'GET',
                        success: function(data) {
                            if (data.success && data.data) {
                                menusCache = data.data; // Cache the data
                                data.data.forEach(function(menu) {
                                    $("#menu_select").append(new Option(menu.name, menu.id));
                                });
                            }
                        }
                    });
                }
            }

            // Reset modal on hide
            $("#newGroupModal").on('hidden.bs.modal', function() {
                $("#name").val('');
                $("#description").val('');
                $("#default_access_level").val('1');
                $("#is_active").val('1');
            });

            $("#updateGroupModal").on('hidden.bs.modal', function() {
                $("#update_id").val('');
                $("#update_name").val('');
                $("#update_description").val('');
                $("#update_default_access_level").val('');
                $("#update_is_active").val('');
            });

            // Save new group
            $(".btn-save").click(function() {
                const button = $(this);
                const originalText = button.html();
                const name = $("#name").val();
                const description = $("#description").val();
                const default_access_level = $("#default_access_level").val();
                const is_active = $("#is_active").val();

                if (!name) {
                    swal("Error!", "Please fill in the group name", "error");
                    return;
                }

                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                ajaxWithCsrfRetry({
                    url: "{{ route('admin.access-groups.store') }}",
                    type: 'POST',
                    data: {
                        name: name,
                        description: description,
                        default_access_level: default_access_level,
                        is_active: is_active
                    },
                    success: function(data) {
                        button.prop('disabled', false).html(originalText);
                        if (data.success) {
                            $("#newGroupModal").modal('hide');
                            swal("Success!", data.message, "success");
                            groupTable.ajax.reload();
                        } else {
                            swal("Error!", data.message, "error");
                        }
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html(originalText);
                        let errorMessage = "Something went wrong";
                        if (xhr.status === 419) {
                            errorMessage = "CSRF token mismatch. Please refresh the page and try again.";
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        swal("Error!", errorMessage, "error");
                    }
                });
            });

            // Update group
            $(".btn-update").click(function() {
                const button = $(this);
                const originalText = button.html();
                const id = $("#update_id").val();
                const name = $("#update_name").val();
                const description = $("#update_description").val();
                const default_access_level = $("#update_default_access_level").val();
                const is_active = $("#update_is_active").val();

                if (!name) {
                    swal("Error!", "Please fill in the group name", "error");
                    return;
                }

                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

                ajaxWithCsrfRetry({
                    url: "{{ route('admin.access-groups.update', '__id__') }}".replace('__id__', id),
                    type: 'PUT',
                    data: {
                        name: name,
                        description: description,
                        default_access_level: default_access_level,
                        is_active: is_active
                    },
                    success: function(data) {
                        button.prop('disabled', false).html(originalText);
                        if (data.success) {
                            $("#updateGroupModal").modal('hide');
                            swal("Success!", data.message, "success");
                            groupTable.ajax.reload();
                        } else {
                            swal("Error!", data.message, "error");
                        }
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html(originalText);
                        swal("Error!", "Failed to update group", "error");
                    }
                });
            });

            // Add user to group
            $(".btn-add-user").click(function() {
                const button = $(this);
                const originalText = button.html();
                const groupId = $("#manage_group_id").val();
                const userId = $("#user_select").val();
                const customLevel = $("#custom_level").val();

                if (!userId) {
                    swal("Error!", "Please select a user", "error");
                    return;
                }

                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('admin.access-groups.assign-user', '__id__') }}".replace('__id__', groupId),
                    type: 'POST',
                    data: {
                        user_id: userId,
                        custom_access_level: customLevel || null,
                        _token: getCsrfToken()
                    },
                    success: function(data) {
                        button.prop('disabled', false).html(originalText);
                        if (data.success) {
                            swal("Success!", data.message, "success");
                            $("#user_select").val(null).trigger('change');
                            $("#custom_level").val('');
                            loadGroupUsers(groupId);
                            // Refresh main table to update counts
                            $("#groupTable").DataTable().ajax.reload(null, false);
                        } else {
                            swal("Error!", data.message, "error");
                        }
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html(originalText);
                        let errorMessage = "Something went wrong";
                        if (xhr.status === 419) {
                            errorMessage = "CSRF token mismatch. Please refresh the page and try again.";
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        swal("Error!", errorMessage, "error");
                    }
                });
            });

            // Add menu to group
            $(".btn-add-menu").click(function() {
                const button = $(this);
                const originalText = button.html();
                const groupId = $("#manage_menu_group_id").val();
                const menuIds = $("#menu_select").val();

                if (!menuIds || menuIds.length === 0) {
                    swal("Error!", "Please select at least one menu", "error");
                    return;
                }

                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('admin.access-groups.assign-menu', '__id__') }}".replace('__id__', groupId),
                    type: 'POST',
                    data: {
                        menu_ids: menuIds,
                        _token: getCsrfToken()
                    },
                    success: function(data) {
                        button.prop('disabled', false).html(originalText);
                        if (data.success) {
                            swal("Success!", data.message, "success");
                            $("#menu_select").val(null).trigger('change');
                            loadGroupMenus(groupId);
                            // Refresh main table to update counts
                            $("#groupTable").DataTable().ajax.reload(null, false);
                        } else {
                            swal("Error!", data.message, "error");
                        }
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html(originalText);
                        let errorMessage = "Something went wrong";
                        if (xhr.status === 419) {
                            errorMessage = "CSRF token mismatch. Please refresh the page and try again.";
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        swal("Error!", errorMessage, "error");
                    }
                });
            });
            

        });

        // Cache for menus (moved outside document.ready for global access)
        let menusLoaded = false;

        // Load menus for select
        function loadMenusForSelect() {
            if (menusLoaded) return;
            
            $.ajax({
                url: "{{ route('admin.menus.index') }}",
                type: 'GET',
                success: function(data) {
                    if (data.success && data.data) {
                        $("#menu_select").empty();
                        data.data.forEach(function(menu) {
                            const optionText = menu.name + ' (' + menu.slug + ')';
                            $("#menu_select").append(new Option(optionText, menu.id, false, false));
                        });
                        menusLoaded = true;
                    }
                }
            });
        }

        // Initialize/Refresh Select2 when manageMenusModal is SHOWN
        $('#manageMenusModal').on('shown.bs.modal', function() {
            // Destroy existing Select2 instance if exists
            if ($("#menu_select").hasClass('select2-hidden-accessible')) {
                $("#menu_select").select2('destroy');
            }
            
            // Initialize Select2
            $("#menu_select").select2({
                dropdownParent: $('#manageMenusModal'),
                placeholder: 'Select Menus...',
                allowClear: true,
                width: '100%'
            });
            
            // Load menus after Select2 is initialized
            loadMenusForSelect();
        });

        // Clear selection on hide
        $("#manageMenusModal").on('hidden.bs.modal', function() {
            if ($("#menu_select").hasClass('select2-hidden-accessible')) {
                $("#menu_select").val(null).trigger('change');
            }
        });
        $("#manageUsersModal").on('hidden.bs.modal', function() {
            if ($("#user_select").hasClass('select2-hidden-accessible')) {
                $("#user_select").val(null).trigger('change');
            }
        });

        // Show update modal
        function showUpdate(id) {
            $.ajax({
                url: "{{ route('admin.access-groups.show', '__id__') }}".replace('__id__', id),
                type: 'GET',
                success: function(data) {
                    if (data.success) {
                        const group = data.data.group;
                        $("#update_id").val(group.id);
                        $("#update_name").val(group.name);
                        $("#update_description").val(group.description);
                        $("#update_default_access_level").val(group.default_access_level);
                        $("#update_is_active").val(group.is_active ? '1' : '0');
                        $("#updateGroupModal").modal('show');
                    }
                }
            });
        }

        // Delete group
        function deleteGroup(id, name) {
            swal({
                title: "Are you sure?",
                text: "Delete access group: " + name + "?",
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
                    ajaxWithCsrfRetry({
                        url: "{{ route('admin.access-groups.destroy', '__id__') }}".replace('__id__', id),
                        type: 'DELETE',
                        success: function(data) {
                            if (data.success) {
                                swal("Success!", data.message, "success");
                                $("#groupTable").DataTable().ajax.reload();
                            } else {
                                swal("Error!", data.message, "error");
                            }
                        },
                        error: function(xhr) {
                            swal("Error!", "Failed to delete group", "error");
                        }
                    });
                }
            });
        }

        // Show manage users modal
        function showManageUsers(groupId) {
            $("#manage_group_id").val(groupId);
            loadGroupUsers(groupId);
            $("#manageUsersModal").modal('show');
        }

        // Load group users
        function loadGroupUsers(groupId) {
            const tbody = $("#groupUsersTable tbody");
            tbody.html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading users...</td></tr>');
            
            $.ajax({
                url: "{{ route('admin.access-groups.show', '__id__') }}".replace('__id__', groupId),
                type: 'GET',
                success: function(data) {
                    if (data.success) {
                        tbody.empty();
                        
                        if (data.data.users.length === 0) {
                            tbody.html('<tr><td colspan="5" class="text-center">No users assigned</td></tr>');
                        } else {
                            data.data.users.forEach(function(user) {
                                tbody.append(`
                                    <tr>
                                        <td>${user.name}</td>
                                        <td>${user.email}</td>
                                        <td>${user.custom_access_level !== null ? user.custom_access_level : '-'}</td>
                                        <td>${user.access_level_name}</td>
                                        <td>
                                            <button class="btn btn-link btn-danger btn-sm p-0" onclick="removeUserFromGroup(${groupId}, ${user.id}, this)">
                                                <i class="fas fa-trash fa-fw"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `);
                            });
                        }
                    }
                },
                error: function(xhr) {
                    tbody.html('<tr><td colspan="5" class="text-center text-danger">Failed to load users</td></tr>');
                }
            });
        }

        // Remove user from group
        function removeUserFromGroup(groupId, userId, btnElement) {
            swal({
                title: "Remove user?",
                text: "Remove this user from the group?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willRemove) => {
                if (willRemove) {
                    const btn = $(btnElement);
                    const originalContent = btn.html();
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: "{{ route('admin.access-groups.remove-user', ['id' => '__g_id__', 'userId' => '__u_id__']) }}"
                            .replace('__g_id__', groupId).replace('__u_id__', userId),
                        type: 'DELETE',
                        data: {
                            _token: getCsrfToken()
                        },
                        success: function(data) {
                            if (data.success) {
                                swal("Success!", data.message, "success");
                                loadGroupUsers(groupId);
                                // Refresh main table to update counts
                                $("#groupTable").DataTable().ajax.reload(null, false);
                            } else {
                                swal("Error!", data.message, "error");
                                btn.prop('disabled', false).html(originalContent);
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = "Something went wrong";
                            if (xhr.status === 419) {
                                errorMessage = "CSRF token mismatch. Please refresh the page and try again.";
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            swal("Error!", errorMessage, "error");
                            btn.prop('disabled', false).html(originalContent);
                        }
                    });
                }
            });
        }

        // Show manage menus modal
        function showManageMenus(groupId) {
            $("#manage_menu_group_id").val(groupId);
            loadGroupMenus(groupId);
            $("#manageMenusModal").modal('show');
        }

        // Load group menus
        function loadGroupMenus(groupId) {
            const tbody = $("#groupMenusTable tbody");
            tbody.html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading menus...</td></tr>');

            $.ajax({
                url: "{{ route('admin.access-groups.show', '__id__') }}".replace('__id__', groupId),
                type: 'GET',
                success: function(data) {
                    if (data.success) {
                        tbody.empty();
                        
                        if (data.data.menus.length === 0) {
                            tbody.html('<tr><td colspan="4" class="text-center">No menus assigned</td></tr>');
                        } else {
                            data.data.menus.forEach(function(menu) {
                                tbody.append(`
                                    <tr id="menu-row-${menu.id}">
                                        <td>${menu.name}</td>
                                        <td>${menu.slug}</td>
                                        <td>${menu.route_name || '-'}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link btn-danger btn-sm p-0" onclick="removeMenuFromGroup(${groupId}, ${menu.id}, this)">
                                                <i class="fas fa-trash fa-fw"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `);
                            });
                        }
                    }
                },
                error: function(xhr) {
                    let msg = "Failed to load menus";
                    if (xhr.status === 429) msg = "Too many requests. Please wait a moment.";
                    tbody.html(`<tr><td colspan="4" class="text-center text-danger">${msg}</td></tr>`);
                }
            });
        }

        // Remove menu from group
        function removeMenuFromGroup(groupId, menuId, btnElement) {
            swal({
                title: "Remove menu?",
                text: "Remove this menu from the group?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willRemove) => {
                if (willRemove) {
                    // Add loading state to the button
                    const btn = $(btnElement);
                    const originalContent = btn.html();
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: "{{ route('admin.access-groups.remove-menu', ['id' => '__g_id__', 'menuId' => '__m_id__']) }}"
                            .replace('__g_id__', groupId).replace('__m_id__', menuId),
                        type: 'DELETE',
                        data: {
                            _token: getCsrfToken()
                        },
                        success: function(data) {
                            if (data.success) {
                                swal("Success!", data.message, "success");
                                loadGroupMenus(groupId);
                                // Refresh main table to update counts
                                $("#groupTable").DataTable().ajax.reload(null, false);
                            } else {
                                swal("Error!", data.message, "error");
                                btn.prop('disabled', false).html(originalContent);
                            }
                        },
                         error: function(xhr) {
                            let errorMessage = "Something went wrong";
                            if (xhr.status === 419) {
                                errorMessage = "CSRF token mismatch. Please refresh the page and try again.";
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            swal("Error!", errorMessage, "error");
                            btn.prop('disabled', false).html(originalContent);
                        }
                    });
                }
            });
        }
    </script>
@endsection
