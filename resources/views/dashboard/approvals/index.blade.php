@extends('layouts.master')
@section('title', 'Loan Approval Levels')
@section('content')
    <header class="section-header">
        <div class="tbl">
            <div class="tbl-row">
                <div class="tbl-cell">
                    <div class="row">
                        <div class="col-xs-6 col-md-6">
                            <h4>@yield('title')</h4>
                        </div>
                        @if ($approvalLevels->count())
                            <div class="col-xs-6 col-md-6 text-right">
                                <a href="{{ route("approval_levels.create") }}"
                                   class="btn btn-secondary">
                                    Add Approval Level
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>
    @if($approvalLevels->count())
        <section class="card">
            <div class="card-block">
                <table id="datatables" class="display table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Level</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($approvalLevels as $rec)
                        <tr>
                            <td>
                                <a href="{{route("approval_levels.edit", ['level' => $rec])}}">
                                    {{$rec->name}}
                                </a>
                            </td>
                            <td>Level {{$loop->iteration}}</td>
                            <td>
                                <form method="post" action="{{route("approval_levels.destroy", ['level' => $rec])}}">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="_method" value="delete">
                                    <button class="btn btn-sm btn-default" type="submit" name="delete-app-level-button-{{$loop->iteration}}">
                                        <i class="font-icon font-icon-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
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
                        <i class="fa fa-check-circle fa-2x"></i>
                    </div>
                    <h4>List of Approval Levels</h4>
                    <p class="color-blue-grey-lighter">
                        Add loan approval levels for your organization
                    </p>
                    <a href="{{ route("approval_levels.create") }}" class="btn btn-secondary">
                        Add Approval Level
                    </a>
                </div>
            </div>
        </div>
    @endif
@endsection