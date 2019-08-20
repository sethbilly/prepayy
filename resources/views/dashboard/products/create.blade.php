@push('additional_styles')
<style>
    #img-upload {
        width: 100%;
        height: 50%;
    }
</style>
@endpush
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
        <form class="form-horizontal" method="post" enctype="multipart/form-data"
              action="{{!empty($product->id) ? route('loan_products.update', ['product' => $product]) : route('loan_products.store')}}">
            {!! csrf_field() !!}
            @if (isset($product) && $product->id)
                <input type="hidden" name="_method" value="put"/>
            @endif
            {{--Loan product details--}}
            <h5 class="with-border">Loan Product Details</h5>
            <div class="row">
                <div class="col-lg-8">
                    <fieldset class="form-group{{ $errors->has('loan_type_id') ? ' has-error' : '' }}">
                        <label class="form-label">Loan Type</label>
                        <select name="loan_type_id" class="form-control select2">
                            <option value="">-- Loan Type --</option>
                            @if (isset($loanTypes))
                                @foreach ($loanTypes as $loanType)
                                    <option value="{{$loanType->id}}" {{old('loan_type_id', $product->loan_type_id) === $loanType->id ? 'selected' : ''}}>
                                        {{$loanType->name}}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <fieldset class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="E.g. Mortgage Loan" required
                               value="{{ old('name', $product->name)}}">
                        @if ($errors->has('name'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('name') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <fieldset class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="5" maxlength="250" data-autosize
                                  style="overflow: hidden; word-wrap: break-word; height: 98px;" name="description"
                                  placeholder="E.g. Whether you're buying a new home, or renovating or refinancing the home you live in now, Mortgage Loan is the ideal way to finance it. "
                                  required>{{ old('description', $product->description) }}</textarea>
                        @if ($errors->has('description'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('description') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <fieldset class="form-group {{ $errors->has('min_amount') ? ' has-error' : '' }}">
                        <label class="form-label">Minimum Amount</label>
                        <div class="input-group">
                            <div class="input-group-addon">GHS</div>
                            <input type="text" class="form-control" name="min_amount" placeholder="Amount"
                                   value="{{ old('min_amount', $product->min_amount)}}">
                            @if ($errors->has('min_amount'))
                                <small class="text-danger">
                                    <strong>{{ $errors->first('min_amount') }}</strong>
                                </small>
                            @endif
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-4 col-sm-12 ">
                    <fieldset class="form-group {{ $errors->has('max_amount') ? ' has-error' : '' }}">
                        <label class="form-label">Maximum Amount</label>
                        <div class="input-group">
                            <div class="input-group-addon">GHS</div>
                            <input type="text" class="form-control" name="max_amount" placeholder="Amount"
                                   value="{{old('max_amount', $product->max_amount)}}"
                                    {{ $errors->has('max_amount') ? ' has-error' : '' }}>
                            @if ($errors->has('max_amount'))
                                <small class="text-danger">
                                    <strong>{{ $errors->first('max_amount') }}</strong>
                                </small>
                            @endif
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <fieldset class="form-group{{ $errors->has('interest_per_year') ? ' has-error' : '' }}">
                        <label class="form-label">Annual Interest Rate</label>
                        <input type="text" class="form-control" name="interest_per_year" placeholder="E.g. 6 etc..."
                               required
                               value="{{ old('interest_per_year', $product->interest_per_year)}}">
                        @if ($errors->has('interest_per_year'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('interest_per_year') }}</strong>
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
@push('additional_scripts')
<script type="text/javascript">
    $(document).ready(function () {
        if (window.autosize) {
            autosize($('textarea[data-autosize]'));
        }
    });
</script>
@endpush