<header class="site-header">
    <div class="container-fluid">

        {{--App name or logo--}}
        <div class="site-logo">
            <a href="{{ route('home') }}">
                <h2 class="site-title hidden-xs-down hidden-sm-down hidden-md-down">
                    <strong>
                        @if(auth()->check())
                            @if($authUser->isBorrower() || $authUser->isApplicationOwner())
                                {{ config('app.name') }}
                            @else
                                {{ $authUser->institutable->name }}
                            @endif
                        @else
                            {{ config('app.name') }}
                        @endif
                    </strong>
                </h2>
            </a>
        </div>

        <button id="show-hide-sidebar-toggle" class="show-hide-sidebar" style="display: none">
            <span>toggle menu</span>
        </button>

        <button class="hamburger hamburger--htla">
            <span>toggle menu</span>
        </button>

        <div class="site-header-content">
            <div class="site-header-content-in">
                <div class="site-header-shown">
                    @if(auth()->guest())
                        <div class="site-header-shown">
                            <a href="{{ route('login.post') }}" class="btn btn-success-outline login">
                                <i class="fa fa-lock"></i> Login
                            </a>
                        </div>
                        <div class="site-header-shown">
                            <a href="{{ route('register.post') }}">
                                <h5 class="m-t-sm hidden-xs hidden-sm" style="color: #FFFFFF; margin-top: 8px">
                                    <i class="font-icon font-icon-user"></i>
                                    Register &nbsp; <small class="white-text">or</small> &nbsp;&nbsp;&nbsp;
                                </h5>
                            </a>
                        </div>
                    @else
                        <div class="dropdown user-menu">
                            <button class="dropdown-toggle" id="dd-user-menu" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="{{ asset('images/avatar.png') }}" alt="{{ $authUser->firstname }}">
                                {{ $authUser->firstname }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dd-user-menu">
                                <a class="dropdown-item" href="{{ route('user.profile.index') }}">
                                    <span class="font-icon glyphicon glyphicon-user"></span>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <span class="font-icon glyphicon glyphicon-log-out"></span>
                                    Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout.post') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>