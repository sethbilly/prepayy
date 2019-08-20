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
        <link rel="stylesheet" href="{{ asset('css/separate/elements/cards.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/lib/font-awesome/font-awesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/lib/bootstrap/bootstrap.min.css') }}">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
        @stack('additional_styles')
        @yield('extra_styles')
    </head>

    <body style="background-color: #FFFFFF">
        {{--Site header--}}
        @include('partials._header')

        {{--Page content--}}
        <div class="page-content">
            @yield('content')
        </div>

        {{--Javascript--}}
        <script src="{{ asset('js/lib/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/lib/bootstrap/bootstrap.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
        @yield('extra_scripts')
        <script type="text/javascript">
            $(document).ready(function() {
                $('.select2').select2();
            });
        </script>
        @stack('additional_scripts')
    </body>
</html>