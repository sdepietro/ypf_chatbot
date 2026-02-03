<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- CoreUI CSS --}}
<link href="https://unpkg.com/@coreui/coreui@5.3.0/dist/css/coreui.min.css" rel="stylesheet">

{{-- Custom styles for sidebar layout --}}
<style>
    @media (min-width: 768px) {
        html:not([dir="rtl"]) .sidebar-fixed ~ .wrapper {
            padding-left: var(--cui-sidebar-width, 256px);
            transition: padding 0.15s;
        }
        html[dir="rtl"] .sidebar-fixed ~ .wrapper {
            padding-right: var(--cui-sidebar-width, 256px);
            transition: padding 0.15s;
        }
        html:not([dir="rtl"]) .sidebar-fixed.sidebar-narrow-unfoldable ~ .wrapper {
            padding-left: var(--cui-sidebar-narrow-width, 56px);
        }
        html[dir="rtl"] .sidebar-fixed.sidebar-narrow-unfoldable ~ .wrapper {
            padding-right: var(--cui-sidebar-narrow-width, 56px);
        }
        /* When sidebar is hidden */
        html:not([dir="rtl"]) .sidebar-fixed.hide ~ .wrapper {
            padding-left: 0;
        }
        html[dir="rtl"] .sidebar-fixed.hide ~ .wrapper {
            padding-right: 0;
        }
    }
</style>

{{-- SimpleBar CSS --}}
<link href="https://unpkg.com/simplebar@5.3.9/dist/simplebar.min.css" rel="stylesheet">

{{-- Plugins CSS from config --}}
@foreach(config('template.plugins', []) as $name => $plugin)
    @if($plugin['active'] ?? false)
        @foreach($plugin['files'] ?? [] as $file)
            @if(($file['type'] ?? '') === 'css')
                <link href="{{ ($file['asset'] ?? false) ? asset($file['location']) : $file['location'] }}" rel="stylesheet">
            @endif
        @endforeach
    @endif
@endforeach
