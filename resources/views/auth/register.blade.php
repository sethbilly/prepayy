@extends('layouts.auth')
@section('title', 'Register')
@section('content')
    <form class="sign-box" method="POST" action="{{ route('register.post') }}">
        {{ csrf_field() }}

        @include('partials._status_messages')

        <header class="sign-title">
            Create New Account
        </header>
        <input type="hidden" name="type" value="{{$type ?? ''}}">
        <div class="form-group{{ $errors->has('firstname') ? ' has-error' : '' }}">
            <input id="name" name="firstname" type="text" class="form-control" placeholder="First Name" value="{{ old('firstname') }}" required autofocus>
            @if ($errors->has('firstname'))
                <small class="text-danger">
                    <strong>{{ $errors->first('firstname') }}</strong>
                </small>
            @endif
        </div>
        <div class="form-group{{ $errors->has('lastname') ? ' has-error' : '' }}">
            <input id="name" name="lastname" type="text" class="form-control" placeholder="Last Name" value="{{ old('firstname') }}" required autofocus>
            @if ($errors->has('lastname'))
                <small class="text-danger">
                    <strong>{{ $errors->first('lastname') }}</strong>
                </small>
            @endif
        </div>

        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
            <input id="email" name="email" type="email" class="form-control" placeholder="E-Mail Address" value="{{ old('email') }}" required>
            @if ($errors->has('email'))
                <small class="text-danger">
                    <strong>{{ $errors->first('email') }}</strong>
                </small>
            @endif
        </div>

        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
            <input id="password" name="password" type="password" class="form-control" placeholder="Password" required>
            @if ($errors->has('password'))
                <small class="text-danger">
                    <strong>{{ $errors->first('password') }}</strong>
                </small>
            @endif
        </div>

        <div class="form-group">
            <input id="password-confirm" name="password_confirmation" type="password" class="form-control" placeholder="Repeat password" required>
        </div>

        <button type="submit" class="btn btn-success btn-block btn-rounded sign-up">
            Sign up
        </button>
        <p class="sign-note">Already have an account? <a href="{{ route('login.get') }}">Sign in</a></p>
    </form>
@endsection
