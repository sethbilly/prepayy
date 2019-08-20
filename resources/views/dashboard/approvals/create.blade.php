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
        <form method="POST"
              action="{{$approvalLevel->id ? route("approval_levels.update", ['level' => $approvalLevel]) : route("approval_levels.store")}}">
            {!! csrf_field() !!}
            @if ($approvalLevel->id)
                <input type="hidden" name="_method" value="put">
            @endif

            {{--Approval level details--}}
            <h5 class="with-border">Approval Level Details</h5>
            <div class="row">
                <div class="col-lg-8">
                    <fieldset class="form-group">
                        <label class="form-label">Level Number</label>
                        <input type="text" readonly class="form-control" name="level" value="Level # {{$levelNumber}}">
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <fieldset class="form-group">
                        <label class="form-label">Approval Level Name</label>
                        <input type="text" class="form-control" name="name"
                               value="{{old('name', $approvalLevel->name)}}"
                               placeholder="E.g. Customer Information Verification" required>
                        @if ($errors->has('name'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('name') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
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