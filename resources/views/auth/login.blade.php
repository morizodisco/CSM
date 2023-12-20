@extends('common.layout')

@push('css')
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
@endpush

@push('script')
    <script src="{{ asset('js/login.js') }}" defer></script>
@endpush

@section('content')
    <main>
        <div class="container">
            <div class="form">
                <div class="title">
                    <h1>CSM</h1>
                </div>
                <form method="POST" action="{{ route('login') }}">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" placeholder="メールアドレスを入力" required autocomplete="email" autofocus>

                    @error('email')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                           name="password" placeholder="パスワードを入力" required autocomplete="current-password">

                    @error('password')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked style="display: none;">

                    <button type="submit" class="btn btn-primary">{{ __('ログイン') }}</button>
                    @csrf
                </form>
            </div>
        </div>
    </main>
@endsection
