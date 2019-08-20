@extends('layouts.master')
@section('title', 'Employers')
@section('content')
<header class="section-header">
    <div class="tbl">
        <div class="tbl-row">
            <div class="tbl-cell">
                <div class="row">
                    <div class="col-xs-6 col-md-6">
                        <h4>@yield('title')</h4>
                    </div>
                    @if($employers->count())
                    <div class="col-xs-6 col-md-6 text-right">
                        <a href="{{ route('callens.employers.create') }}" class="btn btn-secondary">
                            Add Employer
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
@if($employers->count())
    <section class="card">
        <div class="card-block">
            <div class="row m-b-md">
                <div class="col-md-12">
                    <form method="GET" action="{{ route('callens.employers.index') }}">
                        <div class="col-xs-12 col-md-6 text-right" style="margin-left: 10px">
                            <input type="search" name="search" value="{{$search ?? ''}}" class="form-control input-sm pull-right" placeholder="Search...">
                        </div>
                    </form>
                </div>
            </div>
            <table class="display table" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Administrator</th>
                    <th>Address</th>
                    <th>Contact Number</th>
                </tr>
                </thead>
                <tbody>
                @foreach($employers as $employer)
                    <tr>
                        <td><a href="{{route('callens.employers.edit', ['$employer' => $employer])}}">{{ $employer->name }}</a></td>
                        <td>{{ $employer->accountOwner ? $employer->accountOwner->getFullName() : '' }}</td>
                        <td>{{ $employer->address }}</td>
                        <td>{{ $employer->accountOwner ? $employer->accountOwner->contact_number : '' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
@else
    <div class="box-typical box-typical-full-height">
        <div class="center-content tbl">
            <div class="center-content-in">
                <div class="center-content-icon">
                    <i class="fa fa-suitcase fa-2x"></i>
                </div>
                <h4>Your employers list</h4>
                <p class="color-blue-grey-lighter">
                    Add employer, branding and administrator details.
                </p>
                <a href="{{ route('callens.employers.create') }}" class="btn btn-secondary">
                    Add Employer
                </a>
            </div>
        </div>
    </div>
@endif
@endsection