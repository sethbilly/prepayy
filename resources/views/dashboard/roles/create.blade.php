@extends('layouts.master')
@section('title', $title)
@section('content')
    <header class="section-header">
        <div class="row">
            <div class="col-md-12">
                <h4>@yield('title')</h4>
            </div>
        </div>
    </header>
    <div class="box-typical box-typical-padding">
        <form class="form-horizontal" method="post"
              action="{{!empty($role->id) ? route('roles.update', ['role' => $role->name]) : route('roles.store')}}">
            {!! csrf_field() !!}
            @if (isset($role) && $role->id)
                <input type="hidden" name="_method" value="put"/>
            @endif
            {{--Role details--}}
            <h5 class="with-border">Role Details</h5>
            <div class="row">
                <div class="col-lg-8">
                    <fieldset class="form-group{{ $errors->has('role.display_name') ? ' has-error' : '' }}">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="role[display_name]" placeholder="E.g. Reports Manager, Accounts Manager etc..." required
                               value="{{ old('role.display_name', $role->display_name)}}">
                        @if ($errors->has('name'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('role.display_name') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <fieldset class="form-group{{ $errors->has('role.description') ? ' has-error' : '' }}">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="5" maxlength="255" name="role[description]"
                                  placeholder="E.g. User can generate and print branch and staff order reports"
                                  required>{{ old('role.description', $role->description) }}</textarea>
                        @if ($errors->has('role.description'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('role.description') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
            </div>

            {{-- Permissions for this role --}}
            <h5 class="m-t-lg with-border">Permissions for {{'the '. $role->display_name. ' '}} Role</h5>
            <div class="row">
                @foreach($permissions as $groupName => $perms)
                    <div class="col-lg-6">
                        <section class="panel panel-default m-b-lg">
                            <header class="panel-heading font-bold">{{$groupName}}</header>
                            <table class="table table-striped m-t-md">
                                <thead>
                                <tr>
                                    <th>Permission</th>
                                    <th>Allow</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($perms as $aPerm)
                                    <tr>
                                        <td>
                                            <label for="{{$aPerm->name}}" data-toggle="tooltip"
                                                   title="{{$aPerm->description}}">
                                                {{$aPerm->display_name}}
                                            </label>
                                        </td>
                                        <td>
                                            <input type="checkbox" id="{{$aPerm->name}}" name="permissions[]"
                                                   data-tooltip="{{$aPerm->description}}"
                                                   {{isset($addedPermissions) && in_array($aPerm->name, $addedPermissions) ? 'checked' : ''}}
                                                   value="{{$aPerm->name}}"/>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </section>
                    </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-lg-12 m-t-lg text-right">
                    <fieldset class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save changes
                        </button>
                    </fieldset>
                </div>
            </div>

        </form>
    </div>
@endsection