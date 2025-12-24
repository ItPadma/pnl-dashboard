@inject('accessControl', 'App\Services\AccessControlService')
@php
    $menuTree = $accessControl->getMenuHierarchy(Auth::user());
@endphp

<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="/" class="logo">
                <h5 class="text-white">PAJAK</h5>
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
                @foreach ($menuTree as $menu)
                    @php
                        $hasChildren = !empty($menu['children']);
                        $isActive = false;
                        
                        // Check active state
                        if ($menu['route_name'] && Request::routeIs($menu['route_name'])) {
                            $isActive = true;
                        } elseif ($hasChildren) {
                            foreach ($menu['children'] as $child) {
                                if ($child['route_name'] && Request::routeIs($child['route_name'])) {
                                    $isActive = true;
                                    break;
                                }
                            }
                        }
                    @endphp

                    @if (isset($menu['type']) && $menu['type'] === 'section')
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">{{ $menu['name'] }}</h4>
                        </li>
                    @elseif ($hasChildren)
                        <li class="nav-item {{ $isActive ? 'active submenu' : '' }}">
                            <a data-bs-toggle="collapse" href="#{{ $menu['slug'] }}">
                                <i class="{{ $menu['icon'] ?? 'fas fa-layer-group' }}"></i>
                                <p>{{ $menu['name'] }}</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse {{ $isActive ? 'show' : '' }}" id="{{ $menu['slug'] }}">
                                <ul class="nav nav-collapse">
                                    @foreach ($menu['children'] as $child)
                                        <li class="{{ $child['route_name'] && Request::routeIs($child['route_name']) ? 'active' : '' }}">
                                            <a href="{{ $child['route_name'] ? route($child['route_name']) : '#' }}">
                                                <span class="sub-item">{{ $child['name'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @else
                        <li class="nav-item {{ $isActive ? 'active' : '' }}">
                            <a href="{{ $menu['route_name'] ? route($menu['route_name']) : '#' }}" class="collapsed">
                                <i class="{{ $menu['icon'] ?? 'fas fa-home' }}"></i>
                                <p>{{ $menu['name'] }}</p>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</div>

