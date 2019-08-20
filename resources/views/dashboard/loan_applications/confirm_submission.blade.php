@push('additional_styles')
<style>
    .center-box {
        margin: auto;
        float: none;
    }
</style>
@endpush
@extends('layouts.loan_application')
@section('title', 'Confirm Loan Application Submission')
@section('content')
    <div class="row m-t-md m-b-md text-center">
        <h3>
            <strong>
                Application for {{isset($application) ? $application->loanProduct->name : $product->name }}
                from {{isset($application) ? $application->loanProduct->institution->name : $product->institution->name}}
            </strong>
        </h3>
    </div>
    <div class="row">
        <form method="POST"
              action="{{route('loan_applications.confirm_submission.post', ['application' => $application])}}">
            {!! csrf_field() !!}
            <div class="col-md-5 center-box">
                <div class="box-typical box-typical-padding">
                    <label class="form-label">Submission Token</label>
                    <div class="form-control-wrapper">
                        <input type="text" name="submission_token" class="form-control" placeholder="Your submission token">
                    </div>
                </div>

                <div class="form-group m-b-lg pull-right">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>
    </div>
@endsection