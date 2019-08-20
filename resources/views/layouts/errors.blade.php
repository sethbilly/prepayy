<?php
/**
 * Created by PhpStorm.
 * User: francis
 * Date: 18/01/2017
 * Time: 22:50
 */
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('title')</title>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="{{ asset('css/separate/pages/error.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/lib/font-awesome/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/lib/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>

<div class="page-error-box">
    @yield('content')
    <a href="/" class="btn btn-rounded">Go to the main page</a>
</div>
