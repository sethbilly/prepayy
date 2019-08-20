@extends('layouts.master')
@section('title', 'Loan Types')
@section('content')
    <header class="section-header">
        <div class="tbl">
            <div class="tbl-row">
                <div class="tbl-cell">
                    <div class="row">
                        <div class="col-xs-6 col-md-6">
                            <h4>@yield('title')</h4>
                            <br/>
                            <small>You are here:
                                <a href="{{route('loan_products.index')}}">Loan Products</a>&nbsp;&gt;&nbsp;Loan Types
                            </small>
                        </div>
                        @if ($loanTypes->count())
                            <div class="col-xs-6 col-md-6 text-right">
                                <a href="javascript:void(0)" class="btn btn-secondary" data-toggle="modal"
                                   data-target="#loan-type-modal">
                                    Add Loan Type
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>
    @if($loanTypes->count())
        <section class="card">
            <div class="card-block">
                <div class="row m-b-md">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('loan_products.types.index') }}">
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
                        <th style="width:80%">Name</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($loanTypes as $loanType)
                        <tr>
                            <td>{{ $loanType->name }}</td>
                            <td>
                                <a href="javascript:void(0)"
                                   data-name="{{$loanType->name}}"
                                   data-href="{{route('loan_products.types.update', ['type' => $loanType])}}"
                                   class="edit-loan-type">Edit</a>&nbsp;|
                                <a href="javascript:void(0)" data-form-id="#del-loan-type-form-{{$loanType->id}}"
                                   class="delete-link">Delete</a>
                                <form action="{{route('loan_products.types.delete', ['type' => $loanType])}}"
                                      id="del-loan-type-form-{{$loanType->id}}"
                                      method="post" style="display:none">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="_method" value="delete">
                                    <button>Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        {{$loanTypes->appends(['search' => $search ?? '', 'limit' => $limit ?? 20])->links()}}
    @else
        <div class="box-typical box-typical-full-height">
            <div class="center-content tbl">
                <div class="center-content-in">
                    <div class="center-content-icon">
                        <i class="font-icon font-icon-post fa-2x"></i>
                    </div>
                    <h4>
                        @if (isset($search))
                            No loan types matching the specified criteria were found
                        @elseif ($authUser->isApplicationOwner())
                            No loan types have been added
                        @else
                            No {{auth()->user()->institutable->name}} specific loan types added
                        @endif
                    </h4>
                    @if (isset($search))
                        <a href="{{route('loan_products.types.index')}}">Browse Loan Types</a>
                    @else
                        <a href="javascript:void(0)" class="btn btn-secondary" data-toggle="modal"
                           data-target="#loan-type-modal">
                            Add Loan Type
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection
@include('partials._loan_type_modal')
@push('additional_scripts')
<script src="{{asset('js/loan_types.js')}}"></script>
@endpush