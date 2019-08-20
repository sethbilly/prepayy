@extends('layouts.master')
@section('title', 'Add Employers')
@section('content')
    <header class="section-header">
        <div class="tbl">
            <div class="tbl-row">
                <div class="tbl-cell">
                    <div class="row">
                        <div class="col-xs-6 col-md-6">
                            <h4>@yield('title')</h4>
                        </div>
                        @if($partnerEmployers->count())
                            <div class="col-xs-6 col-md-6 text-right">
                                <button type="button" class="btn btn-secondary"
                                        data-toggle="modal" data-target="#addEmployerModal">
                                    Add Employer
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>
    @if($partnerEmployers->count())
        <section class="card">
            <div class="card-block">
                <div class="row m-b-md">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('partner.employers.index') }}">
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
                        <th>Address</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($partnerEmployers as $employer)
                        <tr>
                            <td>{{ $employer->name }}</td>
                            <td>{{ $employer->address }}</td>
                            <td>
                                <form method="post"
                                      action="{{route("partner.employers.destroy", ['employer' => $employer])}}">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="_method" value="delete">
                                    <button class="btn btn-sm btn-default" type="submit"
                                            name="delete-employer-button-{{$loop->index}}">
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
                        <i class="fa fa-suitcase fa-2x"></i>
                    </div>
                    <h4>Your employers list</h4>
                    <p class="color-blue-grey-lighter">
                        Add employers you are partnering with.
                    </p>
                    <button type="button" class="btn btn-secondary"
                            data-toggle="modal" data-target="#addEmployerModal">
                        Add Employer
                    </button>
                </div>
            </div>
        </div>
    @endif
    <div class="modal fade" id="addEmployerModal" tabindex="-1" role="dialog" aria-labelledby="addEmployerModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('partner.employers.store') }}">
                    {!! csrf_field() !!}
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="modal-title" id="addEmployerModalLabel">
                                    Add Employer
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-sm btn-success">Save changes</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="row m-t-md">
                            <div class="col-md-12">
                                <fieldset class="form-group">
                                    <div class="form-group">
                                        <select name="employer_id" class="form-control select2"
                                                id="select-employers">
                                            <option value="">-- Select Employer --</option>
                                            @if (isset($allEmployers))
                                                @foreach ($allEmployers as $employer)
                                                    <option value="{{$employer->id}}">{{ $employer->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection