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
    <link rel="stylesheet" href="{{ asset('css/separate/pages/profile-2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/lib/font-awesome/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/separate/vendor/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/separate/vendor/bootstrap-select/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/lib/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @stack('additional_styles')
    @yield('extra_styles')
</head>

<body>
{{--Site header--}}
@include('partials._header')

{{--Page content--}}
@yield('profile_content')

{{--Javascript--}}
<script src="{{ asset('js/lib/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('js/lib/tether/tether.min.js') }}"></script>
<script src="{{ asset('js/lib/bootstrap/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/plugins.js') }}"></script>
<script src="{{ asset('js/lib/moment/moment.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/lib/eonasdan-bootstrap-datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/lib/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script>
    $('.date').datetimepicker({
        format: 'DD-MM-YYYY'
    });
</script>
@yield('extra_scripts')
@stack('additional_scripts')
</body>
</html>