@extends('layouts.auth')

@section('title', 'Login - YPF Chat Station')

@push('css')
<style>
    .login-card {
        border-radius: 1rem;
    }
    .login-card .card-body {
        padding: 3rem;
    }
    .login-logo {
        font-size: 4rem;
        color: var(--ypf-blue, #0033a0);
    }
    .btn-ypf {
        background-color: var(--ypf-blue, #0033a0);
        border-color: var(--ypf-blue, #0033a0);
        color: white;
    }
    .btn-ypf:hover {
        background-color: #002680;
        border-color: #002680;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card login-card shadow-lg">
            <div class="card-body text-center">
                <div class="login-logo mb-4">
                    <i class="fas fa-gas-pump"></i>
                </div>
                <h1 class="h3 mb-4 fw-normal text-body">YPF Chat Station</h1>

                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Contrasena" required autofocus>
                        <label for="password"><i class="fas fa-lock me-2"></i>Contrasena</label>
                    </div>
                    <button type="submit" class="btn btn-ypf w-100 py-2">
                        <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
