@extends('layouts.master')
@section('title', 'Loan Products')
@section('content')
    <header class="section-header">
        <div class="tbl">
            <div class="tbl-row">
                <div class="tbl-cell">
                    <div class="row">
                        <div class="col-xs-6 col-md-6">
                            <h4>@yield('title')</h4>
                        </div>
                        <div class="col-xs-6 col-md-6 text-right">
                            <a href="{{route('loan_products.types.index')}}" class="btn btn-secondary">Loan Types</a>
                            @if ($products->count())
                                <a href="{{ route('loan_products.create') }}"
                                   class="btn btn-secondary">
                                    Add New Product
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    @if($products->count())
        <section class="card">
            <div class="card-block">
                <div class="row m-b-md">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('loan_products.index') }}">
                            <div class="col-xs-12 col-md-6 text-right" style="margin-left: 10px">
                                <input type="search" name="search" value="{{$search ?? ''}}"
                                       class="form-control input-sm pull-right" placeholder="Search...">
                            </div>
                        </form>
                    </div>
                </div>
                <table class="display table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Min Amount (GHS)</th>
                        <th>Max Amount (GHS)</th>
                        <th>Annual Interest Rate</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <a href="{{route('loan_products.edit', ['product' => $product])}}">
                                    {{ $product->name }}</a>
                            </td>
                            <td>{{ $product->description }}</td>
                            <td>{{ $product->min_amount }}</td>
                            <td>{{ $product->max_amount }}</td>
                            <td>{{ $product->interest_per_year }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{$products->appends(['search' => $search, 'limit' => $limit])->links()}}
        </section>
    @else
        <div class="box-typical box-typical-full-height">
            <div class="center-content tbl">
                <div class="center-content-in">
                    <div class="center-content-icon">
                        <i class="font-icon font-icon-post fa-2x"></i>
                    </div>
                    <h4>Your loan products list</h4>
                    <p class="color-blue-grey-lighter">
                        Create products with names, photos and prices to speed-up checkout.
                    </p>
                    <a href="{{ route('loan_products.create') }}" class="btn btn-secondary">
                        Add New Product
                    </a>
                </div>
            </div>
        </div>
    @endif
@endsection