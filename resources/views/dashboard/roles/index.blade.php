@extends('layouts.master')
@section('title', 'Roles')
@section('content')
    <header class="section-header">
        <div class="tbl">
            <div class="tbl-row">
                <div class="tbl-cell">
                    <div class="row">
                        <div class="col-xs-6 col-md-6">
                            <h4>@yield('title')</h4>
                        </div>
                        @if ($roles->count())
                            <div class="col-xs-6 col-md-6 text-right">
                                <a href="{{ route('roles.create') }}" class="btn btn-secondary">
                                    Add New Role
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>
    @if($roles->count())
        <section class="card">
            <div class="card-block">
                <div class="row m-b-md">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('roles.index') }}">
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
                        <th>Permissions</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>
                                <a href="{{route('roles.edit', ['role' => $role])}}">{{ $role->display_name }}</a>
                            </td>
                            <td>{{ $role->description }}</td>
                            <td>
                                {{-- Display up to 3 permissions for the role --}}
                                @foreach ($role->permissions->take(3) as $permission)
                                    {{$permission->display_name}}{{!$loop->last ? ', ' : ''}}
                                    @if ($loop->last && $role->permissions->count() > 3)
                                        &nbsp;etc
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                <a href="javascript:void(0)" data-form-id="#del-role-form-{{$role->id}}"
                                   class="delete-link">Delete</a>
                                <form action="{{route('roles.delete', ['role' => $role])}}"
                                      id="del-role-form-{{$role->id}}" method="post" style="display:none">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="_method" value="delete">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{$roles->appends(['search' => $search, 'limit' => $limit])->links()}}
        </section>
    @else
        <div class="box-typical box-typical-full-height">
            <div class="center-content tbl">
                <div class="center-content-in">
                    <div class="center-content-icon">
                        <i class="fa fa-list fa-2x"></i>
                    </div>
                    <h4>Your roles list</h4>
                    <p class="color-blue-grey-lighter">
                        Add roles and assign them to your users.
                    </p>
                    <a href="{{ route('roles.create') }}" class="btn btn-secondary">
                        Add New Role
                    </a>
                </div>
            </div>
        </div>
    @endif
@endsection