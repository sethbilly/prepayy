@extends('layouts.master')
@section('title', 'Users')
@section('content')
    <header class="section-header">
        <div class="tbl">
            <div class="tbl-row">
                <div class="tbl-cell">
                    <div class="row">
                        <div class="col-xs-6 col-md-6">
                            <h4>@yield('title')</h4>
                        </div>
                        @if($users->count())
                            <div class="col-xs-6 col-md-6 text-right">
                                <a href="{{ route("users.create") }}" class="btn btn-secondary">
                                    Add New User
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>
    @if($users->count())
        <section class="card">
            <div class="card-block">
                <div class="row m-b-md">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('users.index') }}">
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
                        <th>Email</th>
                        <th>Roles</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            {{-- Account owner's account should only be editable by the account owner --}}
                            <td>
                                @if ($authUser->canEditUser($user))
                                    <a href="{{route("users.edit", ['$user' => $user])}}">{{ $user->getFullName() }}</a>
                                @else
                                    {{$user->getFullName()}}
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    {{$role->display_name}}&nbsp;{{!$loop->last ? ', ' : ''}}
                                @endforeach
                            </td>
                            <td>
                                @if (!$user->isAccountOwner())
                                    <a href="javascript:void(0)" data-form-id="#del-user-form-{{$user->id}}"
                                       class="delete-link">Delete</a>
                                    <form action="{{route('users.delete', ['user' => $user])}}"
                                          id="del-user-form-{{$user->id}}" method="post" style="display:none">
                                        {!! csrf_field() !!}
                                        <input type="hidden" name="_method" value="delete">
                                        <button type="submit">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{$users->appends(['search' => $search, 'limit' => $limit])->links()}}
        </section>
    @else
        <div class="box-typical box-typical-full-height">
            <div class="center-content tbl">
                <div class="center-content-in">
                    <div class="center-content-icon">
                        <i class="fa font-icon-users fa-2x"></i>
                    </div>
                    <h4>List of Users</h4>
                    <p class="color-blue-grey-lighter">
                        Add users and assign roles & permissions
                    </p>
                    <a href="{{ route("users.create") }}" class="btn btn-secondary">
                        Add New User
                    </a>
                </div>
            </div>
        </div>
    @endif
@endsection