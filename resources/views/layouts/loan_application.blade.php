<!DOCTYPE html>
<html>
    <head lang="en">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        @include('partials._meta')
        <title>@yield('title') | {{ config('app.name', 'CloudLoan') }}</title>
        @include('partials._favicon_links')
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        {{--Styles--}}
        <link rel="stylesheet" href="{{ asset('css/lib/lobipanel/lobipanel.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/separate/vendor/lobipanel.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/lib/jqueryui/jquery-ui.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/separate/pages/widgets.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/separate/pages/profile.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/separate/vendor/slick.css') }}">
        <link rel="stylesheet" href="{{ asset('css/separate/vendor/bootstrap-datetimepicker.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/separate/vendor/bootstrap-select/bootstrap-select.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/lib/font-awesome/font-awesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/lib/bootstrap/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/separate/pages/mail.min.css') }}">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
        @stack('additional_styles')
        @yield('extra_styles')
    </head>

    <body>
        {{--Site header--}}
        @include('partials._header')

        {{--Page content--}}
        <div class="page-content">

            <header class="page-content-header">
                <div class="container-fluid">
                    <div class="tbl">
                        <div class="tbl-row">
                            <div class="tbl-cell">
                                <h5>
                                    <a href="javascript: history.back()">
                                        <small class="text-muted">
                                            <i class="fa fa-long-arrow-left"></i> Return to previous page
                                        </small>
                                    </a>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        @include('partials._status_messages')
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>

        {{--Javascript--}}
        <script src="{{ asset('js/lib/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/lib/tether/tether.min.js') }}"></script>
        <script src="{{ asset('js/lib/bootstrap/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/plugins.js') }}"></script>
        <script src="{{ asset('js/lib/moment/moment.min.js') }}"></script>
        <script src="{{ asset('js/lib/eonasdan-bootstrap-datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
        <script src="{{ asset('js/lib/bootstrap-select/bootstrap-select.min.js') }}"></script>
        <script src="{{ asset('js/lib/jqueryui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('js/lib/lobipanel/lobipanel.min.js') }}"></script>
        <script src="{{ asset('js/lib/match-height/jquery.matchHeight.min.js') }}"></script>
        <script src="{{ asset('js/app.js') }}"></script>
        <script src="{{ asset('js/lib/autosize/autosize.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.select2').select2();
                $('.date').datetimepicker({
                    format: 'DD-MM-YYYY'
                });
            });
        </script>
        @yield('extra_scripts')
        @stack('additional_scripts')
    </body>
</html>