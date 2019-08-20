<!DOCTYPE html>
<html>
    <head lang="en">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        @include('partials._meta')
        <title>@yield('title') | {{ config('app.name') }}</title>
        @include('partials._favicon_links')
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        {{--Styles--}}
        <style>
            body {
                border-top: 3px solid #5dca73;
            }
            .app-title {
                color: #000000;
            }
            h1 a:hover, h1 a:focus {
                color: #5DCA73;
            }
        </style>
        <link rel="stylesheet" href="{{ asset('css/separate/pages/login.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/lib/font-awesome/font-awesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/lib/bootstrap/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    </head>
    <body>
        {{--Page content--}}
        <div class="page-center">
            <div class="page-center-in">
                <div class="container-fluid">
                    <h1 class="text-center m-t-lg">
                        <a href="{{ route('home') }}" class="app-title">{{ config('app.name', 'CloudLoan') }}</a>
                    </h1>
                    @yield('content')
                </div>
            </div>
        </div>

        {{--Javascript--}}
        <script src="{{ asset('js/lib/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/lib/tether/tether.min.js') }}"></script>
        <script src="{{ asset('js/lib/bootstrap/bootstrap.min.js') }}"></script>

    </body>
</html>