@push('additional_styles')
<style>
    .site-logo {
        margin-left: 15px;
    }

    .card-typical-content {
        height: 120px;
    }
</style>
@endpush
@extends('layouts.home')
@section('title', 'Browse All Loans')
@section('content')

    @if(auth()->check())
        <div class="col-md-12">
            @include('partials._status_messages')
        </div>
    @endif

    <div class="col-md-10 col-sm-12" style="margin-left:8.5%;margin-right:auto;">
        <h4>@yield('title')</h4>

        <section class="card card-default m-t-lg m-b-lg" style="background-color: #5DCA73; border-color: #FFFFFF;">
            <div class="card-block" style="margin-bottom: -20px;">
                {{--Include loan filter--}}
                @include('partials._loan_filter')
            </div>
        </section>

        <br>

        <div class="row">
            @if ($products->count())
                {{--Loan products--}}
                @include('partials._loan_products')
            @else
                <div class="col-sm-12 col-md-12">
                    <h3 class="text-center">
                        No loan products were found
                    </h3>
                </div>
            @endif
        </div>
    </div>

    @include('partials._copyright')

@endsection
@include('partials.bootstrap-slider._slider')