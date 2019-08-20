@extends('layouts.master')
@section('title', 'Financial Partners')
@section('content')
<header class="section-header">
    <div class="tbl">
        <div class="tbl-row">
            <div class="tbl-cell">
                <div class="row">
                    <div class="col-xs-6 col-md-6">
                        <h4>@yield('title')</h4>
                    </div>
                    @if($partners->count())
                    <div class="col-xs-6 col-md-6 text-right">
                        <a href="{{ route('callens.partners.create') }}" class="btn btn-secondary">
                            Add Partner
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
@if($partners->count())
    <section class="card">
        <div class="card-block">
            <div class="row m-b-md">
                <div class="col-md-12">
                    <form method="GET" action="{{ route('callens.partners.index') }}">
                        <div class="col-xs-12 col-md-6 text-right" style="margin-left: 10px">
                            <input type="search" name="search" value="{{$search ?? ''}}" class="form-control input-sm pull-right" placeholder="Search...">
                        </div>
                    </form>
                </div>
            </div>
            <table id="datatables" class="display table" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Abbreviation</th>
                    <th>Code</th>
                    <th>Address</th>
                    <th>Contact Number</th>
                </tr>
                </thead>
                <tbody>
                @foreach($partners as $partner)
                <tr>
                    <td><a href="{{route('callens.partners.edit', ['partner' => $partner])}}">{{ $partner->name }}</a></td>
                    <td>{{ $partner->abbr }}</td>
                    <td>{{ $partner->code }}</td>
                    <td>{{ $partner->address }}</td>
                    <td>{{ $partner->accountOwner ? $partner->accountOwner->contact_number : '' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{$partners->appends(['search' => $search, 'limit' => $limit])->links()}}
    </section>
@else
    <div class="box-typical box-typical-full-height">
        <div class="center-content tbl">
            <div class="center-content-in">
                <div class="center-content-icon">
                    <i class="fa fa-building fa-2x"></i>
                </div>
                <h4>Your financial partners list</h4>
                <p class="color-blue-grey-lighter">
                    Add partner, branding and administrator details.
                </p>
                <a href="{{ route('callens.partners.create') }}" class="btn btn-secondary">
                    Add Partner
                </a>
            </div>
        </div>
    </div>
@endif
@endsection