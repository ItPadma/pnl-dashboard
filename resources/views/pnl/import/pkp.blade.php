@extends('layouts.master')

@section('title', 'PNL - Import PKP')

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
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Import PKP</h4>
                        </div>
                        <div class="card-body">
                            @include('layouts.alert')
                            <form action="{{ route('pnl.master-data.import.master-pkp') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                @method('post')
                                <div class="form-group">
                                    <label for="file">Upload File</label>
                                    <input type="file" name="file" class="form-control" id="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-file-import fa-fw"></i> Import</button>
                                <a href="{{ asset('assets/TemplateMasterPKP.xlsx') }}" class="btn btn-success float-end"><i class="fas fa-download fa-fw"></i> Download Template</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
