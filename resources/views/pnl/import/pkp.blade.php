@extends('layouts.master')

@section('title', 'TAX - Import PKP')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Import PKP</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="/">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Import</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">PKP</a>
                    </li>
                </ul>
            </div>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card shadow-sm pkp-card">
                        <div class="card-header pkp-card-header d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-uppercase text-muted mb-1 small">Import</p>
                                <h4 class="card-title mb-0">Master PKP</h4>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">XLSX</span>
                        </div>
                        <div class="card-body"
                             data-update-url="{{ route('pnl.master-data.update.master-pkp', ['id' => '__ID__']) }}"
                             data-toggle-url="{{ route('pnl.master-data.toggle.master-pkp', ['id' => '__ID__']) }}">
                            @include('layouts.alert')
                            <form action="{{ route('pnl.master-data.import.master-pkp') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                @method('post')
                                <div class="form-group">
                                    <label for="file" class="form-label">Upload File</label>
                                    <input type="file" name="file" class="form-control" id="file" accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required @if (! $canEdit) disabled @endif>
                                    <small class="text-muted">Gunakan template resmi agar format konsisten.</small>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif><i class="fas fa-file-import fa-fw"></i> Import</button>
                                    <a href="{{ asset('assets/TemplateMasterPKP.xlsx') }}" class="btn btn-success"><i class="fas fa-download fa-fw"></i> Download Template</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm mt-4 pkp-card">
                        <div class="card-header pkp-card-header">
                            <p class="text-uppercase text-muted mb-1 small">Detail</p>
                            <h4 class="card-title mb-0">Lihat & Edit</h4>
                        </div>
                        <div class="card-body">
                            <form id="pkp-edit-form" method="post" action="#">
                                @csrf
                                @method('put')
                                <input type="hidden" name="id" id="pkp-id">
                                <div class="form-group">
                                    <label for="pkp-idpelanggan" class="form-label">ID Pelanggan</label>
                                    <input type="text" id="pkp-idpelanggan" class="form-control" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="pkp-nama" class="form-label">Nama PKP</label>
                                    <input type="text" name="NamaPKP" id="pkp-nama" class="form-control" required @if (! $canEdit) disabled @endif>
                                </div>
                                <div class="form-group">
                                    <label for="pkp-alamat" class="form-label">Alamat PKP</label>
                                    <input type="text" name="AlamatPKP" id="pkp-alamat" class="form-control" @if (! $canEdit) disabled @endif>
                                </div>
                                <div class="form-group">
                                    <label for="pkp-no" class="form-label">No PKP</label>
                                    <input type="text" name="NoPKP" id="pkp-no" class="form-control" @if (! $canEdit) disabled @endif>
                                </div>
                                <div class="form-group">
                                    <label for="pkp-type" class="form-label">Type Pajak</label>
                                    <input type="text" name="TypePajak" id="pkp-type" class="form-control" @if (! $canEdit) disabled @endif>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary" @if (! $canEdit) disabled @endif>
                                        <i class="fas fa-save fa-fw"></i> Simpan Perubahan
                                    </button>
                                    <button type="button" class="btn btn-light" id="pkp-reset">Reset</button>
                                </div>
                            </form>
                            <div class="mt-3">
                                <p class="text-muted mb-0 small">Pilih data pada tabel untuk menampilkan detail di sini.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card shadow-sm pkp-card">
                        <div class="card-header pkp-card-header d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-uppercase text-muted mb-1 small">Data</p>
                                <h4 class="card-title mb-0">Master PKP</h4>
                            </div>
                            <span class="badge bg-dark-subtle text-dark">{{ $pkpList->count() }} Data</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive pkp-table-compact">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted small">Tampilkan</span>
                                        <select id="pkp-page-length" class="form-select form-select-sm" style="width: auto;">
                                            <option value="5">5</option>
                                            <option value="8" selected>8</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                        <span class="text-muted small">baris</span>
                                    </div>
                                    <div class="text-muted small">Gunakan pencarian untuk mempercepat.</div>
                                </div>
                                <table class="table table-striped table-hover" id="pkp-table">
                                    <thead>
                                        <tr>
                                            <th>ID Pelanggan</th>
                                            <th>Nama PKP</th>
                                            <th>Type Pajak</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pkpList as $pkp)
                                            <tr data-id="{{ $pkp->id }}"
                                                data-idpelanggan='@json($pkp->IDPelanggan)'
                                                data-nama='@json($pkp->NamaPKP)'
                                                data-alamat='@json($pkp->AlamatPKP)'
                                                data-no='@json($pkp->NoPKP)'
                                                data-type='@json($pkp->TypePajak)'>
                                                <td class="fw-semibold">{{ $pkp->IDPelanggan }}</td>
                                                <td>{{ $pkp->NamaPKP }}</td>
                                                <td>
                                                    <span class="badge bg-info-subtle text-info">{{ $pkp->TypePajak ?? '-' }}</span>
                                                    @if ($pkp->is_active === false)
                                                        <span class="badge bg-secondary-subtle text-secondary ms-1">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-primary pkp-show">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if ($pkp->is_active ?? true)
                                                        <button type="button" class="btn btn-sm btn-outline-danger pkp-toggle" data-active="0" @if (! $canEdit) disabled @endif>
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-success pkp-toggle" data-active="1" @if (! $canEdit) disabled @endif>
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Belum ada data PKP.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <style>
        .pkp-card {
            border: 1px solid rgba(17, 24, 39, 0.08);
            border-radius: 16px;
            overflow: hidden;
        }
        .pkp-card-header {
            background: linear-gradient(135deg, rgba(14, 116, 144, 0.12), rgba(37, 99, 235, 0.06));
            border-bottom: 1px solid rgba(17, 24, 39, 0.08);
        }
        .pkp-card .form-label {
            font-weight: 600;
        }
        .pkp-card .table thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .pkp-table-compact .table td,
        .pkp-table-compact .table th {
            padding: 0.35rem 0.5rem;
            vertical-align: middle;
            line-height: 1.15;
        }
        .pkp-table-compact .table td {
            font-size: 0.82rem;
        }
        .pkp-table-compact .table th {
            font-size: 0.74rem;
        }
        .pkp-table-compact .badge {
            font-size: 0.62rem;
        }
        .pkp-table-compact .btn {
            padding: 0.18rem 0.35rem;
        }
        .pkp-table-compact .dataTables_info,
        .pkp-table-compact .dataTables_paginate {
            font-size: 0.8rem;
            margin-top: 0.75rem;
        }
        .pkp-card .btn-outline-primary,
        .pkp-card .btn-outline-danger {
            border-radius: 10px;
        }
    </style>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editForm = document.getElementById('pkp-edit-form');
            const dataCard = document.querySelector('[data-update-url]');
            const updateUrlTemplate = dataCard?.dataset.updateUrl || '';
            const toggleUrlTemplate = dataCard?.dataset.toggleUrl || '';
            const pageLengthSelect = document.getElementById('pkp-page-length');
            const resetButton = document.getElementById('pkp-reset');
            const idInput = document.getElementById('pkp-id');
            const idPelangganInput = document.getElementById('pkp-idpelanggan');
            const namaInput = document.getElementById('pkp-nama');
            const alamatInput = document.getElementById('pkp-alamat');
            const noInput = document.getElementById('pkp-no');
            const typeInput = document.getElementById('pkp-type');

            const safeParse = (value) => {
                try {
                    const parsed = JSON.parse(value);
                    return parsed ?? '';
                } catch (error) {
                    return '';
                }
            };

            const fillForm = (row) => {
                idInput.value = row.dataset.id || '';
                idPelangganInput.value = row.dataset.idpelanggan ? safeParse(row.dataset.idpelanggan) : '';
                namaInput.value = row.dataset.nama ? safeParse(row.dataset.nama) : '';
                alamatInput.value = row.dataset.alamat ? safeParse(row.dataset.alamat) : '';
                noInput.value = row.dataset.no ? safeParse(row.dataset.no) : '';
                typeInput.value = row.dataset.type ? safeParse(row.dataset.type) : '';

                if (row.dataset.id && updateUrlTemplate) {
                    editForm.action = updateUrlTemplate.replace('__ID__', row.dataset.id);
                }
            };

            const bindRowHandlers = () => {
                $('#pkp-table').off('click', '.pkp-show');
                $('#pkp-table').off('click', '.pkp-toggle');

                $('#pkp-table').on('click', '.pkp-show', function () {
                    const row = this.closest('tr');
                    if (row) {
                        fillForm(row);
                        row.classList.add('table-active');
                        document.querySelectorAll('#pkp-table tbody tr').forEach((item) => {
                            if (item !== row) {
                                item.classList.remove('table-active');
                            }
                        });
                    }
                });

                $('#pkp-table').on('click', '.pkp-toggle', function () {
                    const row = this.closest('tr');
                    if (!row) {
                        return;
                    }
                    const id = row.dataset.id;
                    if (!id) {
                        return;
                    }
                    if (!toggleUrlTemplate) {
                        alert('Toggle URL tidak tersedia.');
                        return;
                    }

                    const nextState = this.dataset.active === '1';
                    const label = nextState ? 'Aktifkan' : 'Nonaktifkan';
                    const tone = nextState ? 'success' : 'warning';

                    const submitToggle = () => {
                        const form = document.createElement('form');
                        form.method = 'post';
                        form.action = toggleUrlTemplate.replace('__ID__', id);

                        const token = document.createElement('input');
                        token.type = 'hidden';
                        token.name = '_token';
                        token.value = '{{ csrf_token() }}';

                        const method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'patch';

                        const isActive = document.createElement('input');
                        isActive.type = 'hidden';
                        isActive.name = 'is_active';
                        isActive.value = nextState ? '1' : '0';

                        form.appendChild(token);
                        form.appendChild(method);
                        form.appendChild(isActive);
                        document.body.appendChild(form);
                        form.submit();
                    };

                    if (typeof swal !== 'function') {
                        if (confirm(`Yakin ${label.toLowerCase()} data PKP ini?`)) {
                            submitToggle();
                        }
                        return;
                    }

                    swal({
                        title: `${label} data PKP?`,
                        text: nextState
                            ? 'Data akan digunakan kembali untuk proses PKP.'
                            : 'Data tidak akan digunakan untuk proses PKP.',
                        icon: tone,
                        buttons: {
                            cancel: {
                                text: 'Batal',
                                visible: true,
                                className: 'btn btn-light',
                                closeModal: true,
                            },
                            confirm: {
                                text: label,
                                value: true,
                                visible: true,
                                className: nextState ? 'btn btn-success' : 'btn btn-warning',
                                closeModal: true,
                            },
                        },
                    }).then((confirmed) => {
                        if (!confirmed) {
                            return;
                        }
                        submitToggle();
                    });
                });
            };

            resetButton.addEventListener('click', function () {
                editForm.reset();
                idInput.value = '';
                idPelangganInput.value = '';
                editForm.action = '#';
                document.querySelectorAll('#pkp-table tbody tr').forEach((row) => {
                    row.classList.remove('table-active');
                });
            });

            editForm.addEventListener('submit', function (event) {
                if (!idInput.value) {
                    event.preventDefault();
                    alert('Pilih data terlebih dahulu.');
                }
            });

            if (window.jQuery && $.fn.DataTable) {
                const dataTable = $('#pkp-table').DataTable({
                    pageLength: parseInt(pageLengthSelect?.value || '8', 10),
                    lengthChange: false,
                    ordering: true,
                    info: true,
                    language: {
                        search: 'Cari:',
                        paginate: {
                            previous: 'Prev',
                            next: 'Next'
                        }
                    }
                });
                bindRowHandlers();

                if (pageLengthSelect) {
                    pageLengthSelect.addEventListener('change', function () {
                        dataTable.page.len(parseInt(this.value, 10)).draw();
                    });
                }
            } else if (window.jQuery) {
                bindRowHandlers();
            }
        });
    </script>
@endsection
