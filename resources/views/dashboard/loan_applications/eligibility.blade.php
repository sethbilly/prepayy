@push('additional_styles')
<style>
    .blockquote {
        border-left: solid 4px #5dca73;
    }

    .center-box {
        margin: auto;
        float: none
    }
</style>
@endpush
@extends('layouts.loan_application')
@section('title', 'Verify Eligibility for The Loan')
@section('content')
    <div class="row">
        <div class="col-md-6 center-box">
            <div class="tab-content">
                <div class="text-center m-t-md">
                    <h3>
                        <strong>Hi, {{ $authUser->getFullName() }}</strong>
                    </h3>
                    <h5>
                        Please select your current employer from the list below
                    </h5>
                </div>
                {{--Loan application intro guide--}}
                <div role="tabpanel" class="tab-pane fade in active">
                    <form method="POST"
                          action="{{ route('loan_applications.eligibility.post', [
                            'partner' => $product->institution,
                            'product' => $product,
                            'amount' => $amount,
                            'tenure' => $tenure
                          ])
                         }}">
                        {!! csrf_field() !!}
                        <section class="box-typical box-typical-padding">
                            <div class="row">
                                <div class="col-md-8 m-t-md" style="margin-left: 20%">
                                    <fieldset class="form-group">
                                        <div class="form-group">
                                            <select name="employer_id" class="form-control select2"
                                                    id="select-employers">
                                                <option value="">-- Select Employer --</option>
                                                @if (isset($allEmployers))
                                                    @foreach ($allEmployers as $employer)
                                                        <option value="{{$employer->id}}" {{old('employer_id', $authUser->currentEmployer() ? $authUser->currentEmployer()->id : '') == $employer->id ? 'selected' : ''}}>{{ $employer->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="col-md-8 m-b-md" style="margin-left: 20%">
                                    <button type="submit" class="tbl-cell btn btn-block btn-success">
                                        Check availability
                                    </button>
                                </div>
                            </div>
                        </section>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
