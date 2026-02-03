<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts.partials.head-css')
    <title>@yield('title', config('template.title', 'YPF Chat Station'))</title>
    @stack('css')
</head>
<body>
    <div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
        @include('layouts.partials.sidebar')
    </div>
    <div class="wrapper d-flex flex-column min-vh-100">
        @include('layouts.partials.header')
        <div class="body flex-grow-1">
            <div class="container-lg px-4">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
        @include('layouts.partials.footer')
    </div>

    {{-- CoreUI JS (Bundle includes Popper) --}}
    <script src="https://unpkg.com/@coreui/coreui@5.3.0/dist/js/coreui.bundle.min.js"></script>
    {{-- SimpleBar JS --}}
    <script src="https://unpkg.com/simplebar@5.3.9/dist/simplebar.min.js"></script>
    {{-- Color Modes --}}
    <script src="{{ asset('assets/js/color-modes.js') }}"></script>

    {{-- Plugins JS from config --}}
    @foreach(config('template.plugins', []) as $name => $plugin)
        @if($plugin['active'] ?? false)
            @foreach($plugin['files'] ?? [] as $file)
                @if(($file['type'] ?? '') === 'js')
                    <script src="{{ ($file['asset'] ?? false) ? asset($file['location']) : $file['location'] }}"></script>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- API helper with auth token --}}
    <script>
        const API_TOKEN = '{{ session("api_token", "") }}';

        async function apiFetch(url, options = {}) {
            const headers = {
                'Content-Type': 'application/json',
                'X-Auth-Token': API_TOKEN,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                ...options.headers
            };
            return fetch(url, { ...options, headers });
        }
    </script>

    @stack('scripts')
</body>
</html>
