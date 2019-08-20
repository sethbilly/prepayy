<?php
/**
 * Created by PhpStorm.
 * User: francis
 * Date: 18/01/2017
 * Time: 22:49
 */
?>
@extends('layouts.errors')
@section('title', 'Unauthorized Action')
@section('content')
    <div class="error-code">403</div>
    <div class="error-title">You cannot perform this operation</div>
    <div>
        <p>{{$exception->getMessage()}}</p>
    </div>
@endsection
