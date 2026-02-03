<div class="sidebar-header border-bottom">
    <div class="sidebar-brand">
        <i class="fas fa-gas-pump me-2"></i>
        <span class="sidebar-brand-full">YPF Chat Station</span>
        <span class="sidebar-brand-narrow">YPF</span>
    </div>
</div>
<ul class="sidebar-nav" data-coreui="navigation" data-simplebar>
    @foreach(config('template.template_menu', []) as $item)
        @php
            $hasSubmenu = isset($item['submenu']) && count($item['submenu']) > 0;
            $isActive = false;

            if ($item['url']) {
                $isActive = request()->routeIs($item['url'] . '*');
            } elseif ($hasSubmenu) {
                foreach ($item['submenu'] as $sub) {
                    if (request()->routeIs($sub['url'] . '*')) {
                        $isActive = true;
                        break;
                    }
                }
            }
        @endphp

        @if(is_null($item['can']) || (auth()->check() && auth()->user()->can($item['can'])))
            @if($hasSubmenu)
                {{-- Nav Group (with submenu) --}}
                <li class="nav-group {{ $isActive ? 'show' : '' }}">
                    <a class="nav-link nav-group-toggle" href="#">
                        @if(isset($item['icon']))
                            <i class="{{ $item['icon'] }} nav-icon"></i>
                        @endif
                        {{ $item['text'] }}
                    </a>
                    <ul class="nav-group-items compact">
                        @foreach($item['submenu'] as $sub)
                            @if(is_null($sub['can'] ?? null) || (auth()->check() && auth()->user()->can($sub['can'])))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs($sub['url'] . '*') ? 'active' : '' }}"
                                       href="{{ route($sub['url']) }}">
                                        <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                                        {{ $sub['text'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </li>
            @else
                {{-- Simple Nav Item --}}
                <li class="nav-item">
                    <a class="nav-link {{ $isActive ? 'active' : '' }}"
                       href="{{ $item['url'] ? route($item['url']) : '#' }}">
                        @if(isset($item['icon']))
                            <i class="{{ $item['icon'] }} nav-icon"></i>
                        @endif
                        {{ $item['text'] }}
                    </a>
                </li>
            @endif
        @endif
    @endforeach
</ul>
<div class="sidebar-footer border-top d-none d-md-flex">
    <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
</div>
