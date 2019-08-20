@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
<form class="sign-box reset-password-box" method="POST" action="{{ url('/password/email') }}">
    {{ csrf_field() }}

    @include('partials._status_messages')

    <header class="sign-title">Reset Password</header>

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" placeholder="E-Mail Address" required autofocus/>
        @if ($errors->has('email'))
            <small class="text-danger">
                <strong>{{ $errors->first('email') }}</strong>
            </small>
        @endif
    </div>

    <button type="submit" class="btn btn-success btn-block btn-rounded">
        Reset
    </button>

    Have an account? <a href="{{ url('/login') }}">Sign in</a>
</form>
@endsection
