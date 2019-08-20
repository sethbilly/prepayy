@extends('layouts.auth')
@section('title', 'Set New Password')
@section('content')
<form class="sign-box reset-password-box" method="POST" action="{{ url('/password/reset') }}">
    {{ csrf_field() }}

    @include('partials._status_messages')

    <input type="hidden" name="token" value="{{ $token }}">

    <header class="sign-title">Set New Password</header>

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <input id="email" type="email" class="form-control" name="email" value="{{  old('email', urldecode($email)) }}" placeholder="E-Mail Address" required autofocus>
        @if ($errors->has('email'))
            <small class="text-danger">
                <strong>{{ $errors->first('email') }}</strong>
            </small>
        @endif
    </div>

    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <input id="password-confirm" name="password" type="password" class="form-control" placeholder="New Password" required>
        @if ($errors->has('password'))
            <small class="text-danger">
                <strong>{{ $errors->first('password') }}</strong>
            </small>
        @endif
    </div>

    <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
        <input id="password-confirm" name="password_confirmation" type="password" class="form-control" placeholder="Confirm New Password" required>
        @if ($errors->has('password_confirmation'))
            <small class="text-danger">
                <strong>{{ $errors->first('password_confirmation') }}</strong>
            </small>
        @endif
    </div>
    <button type="submit" class="btn btn-success btn-rounded btn-block">
        Reset Password
    </button>
</form>
@endsection