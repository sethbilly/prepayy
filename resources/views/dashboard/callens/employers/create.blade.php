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
              action="{{ isset($employer) && $employer->id ? route('callens.employers.update', ['employer' => $employer]) : route('callens.employers.store') }}">
            {!! csrf_field() !!}
            @if (isset($employer) && $employer->id)
                <input type="hidden" name="_method" value="put"/>
            @endif

            {{--Employer details--}}
            <h5 class="with-border">Employer Details</h5>
            <div class="row">
                <div class="col-lg-8">
                    <fieldset class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        <label class="form-label">Employer Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $employer->name )}}" placeholder="E.g. Callens Solutions Limited" required>
                        @if ($errors->has('name'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('name') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <fieldset class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                        <label class="form-label">Address</label>
                        <textarea rows="4" class="form-control" name="address" placeholder="E.g. Hse. No 560N3, 1 Mango Tree Avenue Asylum Down, Accra - Ghana">{{ old('address', $employer->address) }}</textarea>
                        @if ($errors->has('address'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('address') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
            </div>

            {{--Administrator's details--}}
            @include('partials._admin_details', ['owner' => $employer->accountOwner])

        </form>
    </div>
@endsection