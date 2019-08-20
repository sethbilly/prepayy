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
          action="{{ isset($partner) && $partner->id ? route('callens.partners.update', ['partner' => $partner]) : route('callens.partners.store') }}">
        {!! csrf_field() !!}
        @if (isset($partner) && $partner->id)
            <input type="hidden" name="_method" value="put"/>
        @endif
        {{--Partner details--}}
        <h5 class="with-border">Partner Details</h5>
        <div class="row">
            <div class="col-lg-4">
                <fieldset class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                    <label class="form-label">Partner Name</label>
                    <input type="text" class="form-control" name="name" placeholder="E.g. QLS Bank" required
                           value="{{ old('name', $partner->name)}}">
                    @if ($errors->has('name'))
                        <small class="text-danger">
                            <strong>{{ $errors->first('name') }}</strong>
                        </small>
                    @endif
                </fieldset>
            </div>
            <div class="col-lg-4">
                <fieldset class="form-group{{ $errors->has('abbr') ? ' has-error' : '' }}">
                    <label class="form-label">Abbreviation</label>
                    <input type="text" class="form-control" name="abbr" placeholder="E.g. Qls" required
                           value="{{ old('abbr', $partner->abbr) }}">
                    @if ($errors->has('abbr'))
                        <small class="text-danger">
                            <strong>{{ $errors->first('abbr') }}</strong>
                        </small>
                    @endif
                </fieldset>
            </div>
            <div class="col-lg-4">
                <fieldset class="form-group{{ $errors->has('code') ? ' has-error' : '' }}">
                    <label class="form-label">Code</label>
                    <input type="text" class="form-control" name="code" placeholder="E.g. 001" required
                           value="{{ old('code', $partner->code) }}">
                    @if ($errors->has('code'))
                        <small class="text-danger">
                            <strong>{{ $errors->first('code') }}</strong>
                        </small>
                    @endif
                </fieldset>
            </div>
            <div class="col-md-8 col-sm-6">
                <fieldset class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                    <label class="form-label">Address</label>
                    <textarea rows="5" class="form-control" name="address"
                              placeholder="E.g. Hse. No 560N3, 1 Mango Tree Avenue Asylum Down, Accra - Ghana">{{old('address', $partner->address)}}</textarea>
                    @if ($errors->has('address'))
                        <small class="text-danger">
                            <strong>{{ $errors->first('address') }}</strong>
                        </small>
                    @endif
                </fieldset>
            </div>
        </div>

        {{--Branding settings--}}
        @include('partials._branding', compact('brandStyle'))

        {{--Administrator's details--}}
        @include('partials._admin_details', ['owner' => $partner->accountOwner])

    </form>
</div>
@endsection