<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="/" class="logo">
                <h5 class="text-white">PNL</h5>
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                    <a href="/" class="collapsed" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">PNL</h4>
                </li>
                <li class="nav-item {{ Request::is('pnl/reguler*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#reguler">
                        <i class="fas fa-layer-group"></i>
                        <p>Reguler</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ Request::is('pnl/reguler*') ? 'show' : '' }}" id="reguler">
                        <ul class="nav nav-collapse">
                            <li class="{{ Request::is('pnl/reguler/pajak-keluaran*') ? 'active' : '' }}">
                                <a href="{{ route('pnl.reguler.pajak-keluaran.index') }}">
                                    <span class="sub-item">Pajak Keluaran</span>
                                </a>
                            </li>
                            <li class="{{ Request::is('pnl/reguler/pajak-masukan*') ? 'active' : '' }}">
                                <a href="{{ route('pnl.reguler.pajak-masukan.index') }}">
                                    <span class="sub-item">Pajak Masukan</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item {{ Request::is('pnl/non-reguler*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#nonreguler">
                        <i class="fas fa-layer-group"></i>
                        <p>Non-Reguler</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ Request::is('pnl/non-reguler*') ? 'show' : '' }}" id="nonreguler">
                        <ul class="nav nav-collapse">
                            <li class="{{ Request::is('pnl/non-reguler/pajak-keluaran*') ? 'active' : '' }}">
                                <a href="{{ route('pnl.non-reguler.pajak-keluaran.index') }}">
                                    <span class="sub-item">Pajak Keluaran</span>
                                </a>
                            </li>
                            <li class="{{ Request::is('pnl/non-reguler/pajak-masukan*') ? 'active' : '' }}">
                                <a href="{{ route('pnl.non-reguler.pajak-masukan.index') }}">
                                    <span class="sub-item">Pajak Masukan</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Master</h4>
                </li>
                <li class="nav-item {{ Request::is('pnl/master-data*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#import">
                        <i class="fas fa-file-import"></i>
                        <p>Import</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ Request::is('pnl/master-data*') ? 'show' : '' }}" id="import">
                        <ul class="nav nav-collapse">
                            <li class="{{ Request::is('pnl/master-data/import*') ? 'active' : '' }}">
                                <a href="{{ route('pnl.master-data.index.master-pkp') }}">
                                    <span class="sub-item">PKP</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @if (Auth::user()->role == 'superuser')
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Settings</h4>
                </li>
                <li class="nav-item {{ Request::is('pnl/setting/userman') ? 'active' : '' }}">
                    <a href="{{ route('pnl.setting.userman.index') }}" class="collapsed" aria-expanded="false">
                        <i class="fas fa-users"></i>
                        <p>User Manager</p>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
