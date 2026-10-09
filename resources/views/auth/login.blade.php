@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow-lg mt-5">
            <div class="card-body p-4">
                <h3 class="text-center mb-4"><i class="bi bi-shield-lock"></i> Login</h3>
                @include('partials.alerts')
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input id="username" name="username" value="{{ old('username') }}" class="form-control" required autofocus />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input id="password" name="password" type="password" class="form-control" required />
                    </div>
                    <button class="btn btn-primary w-100">Login</button>
                </form>
                <div class="text-muted mt-3 small text-center">
                    Default: <b>admin / admin123</b>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
