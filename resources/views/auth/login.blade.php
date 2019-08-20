@extends('layouts.auth')
@section('title', 'Login')
@section('content')
<form class="sign-box" method="POST" action="{{ url('/login') }}">
    {{ csrf_field() }}

    @include('partials._status_messages')

    <header class="sign-title">Sign In</header>

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" placeholder="E-Mail Address" required autofocus/>
        @if ($errors->has('email'))
            <small class="text-danger">
                <strong>{{ $errors->first('email') }}</strong>
            </small>
        @endif
    </div>

    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <input id="password" name="password" type="password" class="form-control" placeholder="Password" required/>
        @if ($errors->has('password'))
            <small class="text-danger">
                <strong>{{ $errors->first('password') }}</strong>
            </small>
        @endif
    </div>

    <div class="form-group">
        <div class="checkbox float-left">
            <input type="checkbox" id="signed-in" name="remember" {{ old('remember') ? 'checked' : ''}}/>
            <label for="signed-in">Keep me signed in</label>
        </div>
        <div class="float-right reset">
            <a href="{{ route('password.forgot.get') }}">Reset Password</a>
        </div>
    </div>

    <button type="submit" class="btn btn-success btn-block btn-rounded">
        Sign in
    </button>
    <p class="sign-note">Don't have an account? <a href="{{ route('register.get') }}">Register</a></p>
</form>
@endsection
