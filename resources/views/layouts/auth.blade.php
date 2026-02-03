<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts.partials.head-css')
    <title>@yield('title', 'Login - ' . config('template.title', 'YPF Chat Station'))</title>
    <style>
        :root {
            --ypf-blue: #0033a0;
            --ypf-red: #e30613;
        }
    </style>
    @stack('css')
</head>
<body class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
        @yield('content')
    </div>

    {{-- CoreUI JS (Bundle includes Popper) --}}
    <script src="https://unpkg.com/@coreui/coreui@5.3.0/dist/js/coreui.bundle.min.js"></script>
    {{-- Color Modes --}}
    <script src="{{ asset('assets/js/color-modes.js') }}"></script>

    @stack('scripts')
</body>
</html>
