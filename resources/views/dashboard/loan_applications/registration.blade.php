@push('additional_styles')
<style>
    .center-box {
        margin: auto;
        float: none;
    }

    .box-typical-padding {
        border-radius: 0;
    }
</style>
@endpush
@extends('layouts.loan_application')
@section('title', 'Loan Application Registration')
@section('content')
    <div class="row m-t-md">
        <h4 class="text-center m-b-lg">
            @php
                $duration = isset($tenure) ? $tenure . ' ' . str_plural('year', $tenure) : '';
                $formattedLoanAmount = isset($amount) ? $currency . ' ' . number_format($amount, 2) : '';
                $productName = isset($application) ? $application->loanProduct->name : $product->name;

                $loanTitle = isset($formattedLoanAmount) ? $formattedLoanAmount . " ($duration)" : $productName;
                $institution = isset($application) ? $application->loanProduct->institution->name : $product->institution->name;
            @endphp
            <strong>
                Application for {{$loanTitle}} from {{$institution}}
            </strong>
        </h4>
        <div class="col-md-9 center-box">
            <div class="alert alert-grey-darker alert-fill alert-close alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                @php
                    $status = isset($application) ? $application->loanApplicationStatus : null;
                    $isDraft = empty($status) || $status->isDraft();
                @endphp
                @if ($isDraft)
                    Hi {{ $authUser->firstname }}! Please fill out your details below and click on <strong>Request
                        employers
                        approval</strong> button to submit & request approval from your employer or click on <strong>Save
                        draft</strong>
                    button to save changes and continue with the application later.
                @else
                    Loan application status: {{ $application->loanApplicationStatus->display_status }}
                @endif
            </div>
        </div>
    </div>
    <div class="row">
        <form method="POST"
              action="{{isset($application) ? route('loan_applications.update', ['application' => $application]) : route('loan_applications.apply.post', ['partner' => $product->institution, 'product' => $product])}}">
            {!! csrf_field() !!}
            @if (isset($application))
                <input type="hidden" name="_method" value="put">
            @endif
            @php
                $hasRequestedInformation = $hasRequestedInformation ?? false;
            @endphp
            <div class="col-md-9 center-box">

                <div class="tabs-section-nav tabs-section-nav-inline">
                    <ul class="nav" role="tablist">
                        <li class="nav-item">
                            <a href="#loan-amount" role="tab" data-toggle="tab"
                               class="nav-link {{$hasRequestedInformation ? '' : 'active'}}">
                                1. Loan Amount
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#personal-details" role="tab" data-toggle="tab">
                                2. Personal Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#current-employer-details" role="tab" data-toggle="tab">
                                3. Employer Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#id-details" role="tab" data-toggle="tab">
                                4. ID Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#guarantor-details" role="tab" data-toggle="tab">
                                5. Guarantor Details
                            </a>
                        </li>
                        @if($hasRequestedInformation)
                            <li class="nav-item">
                                <a class="nav-link active" href="#loan-request-docs" role="tab" data-toggle="tab">
                                    6. Documents
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade {{$hasRequestedInformation ? '' : 'in right active'}}" id="loan-amount"
                         role="tabpanel">
                        <div class="box-typical box-typical-padding">
                            @include('partials._loan_amount_and_tenure')
                        </div>
                    </div>
                    {{--Customer details tab--}}
                    <div role="tabpanel" class="tab-pane fade {{$hasRequestedInformation ? '' : 'in right'}}"
                         id="personal-details">
                        <div class="box-typical box-typical-padding">
                            @include('partials._profile_details', ['user' => $authUser ?? null])
                        </div>
                    </div>
                    {{--Current employer details--}}
                    <div role="tabpanel" class="tab-pane fade" id="current-employer-details">
                        <div class="box-typical box-typical-padding">
                            @include('partials._current_employer', ['employer' => $currentEmployer ?? null])
                        </div>
                    </div>
                    {{--ID details--}}
                    <div role="tabpanel" class="tab-pane fade" id="id-details">
                        <div class="box-typical box-typical-padding">
                            @include('partials._id_details', ['idCard' => $idCard ?? null])
                        </div>
                    </div>
                    {{--Guarantor details--}}
                    <div role="tabpanel" class="tab-pane fade" id="guarantor-details">
                        @include('partials._guarantor', ['guarantor' => $guarantor ?? null])
                    </div>
                    {{--Additional information / documents--}}
                    <div role="tabpanel" class="tab-pane fade {{$hasRequestedInformation ? 'in right active' : ''}}"
                         id="loan-request-docs">
                        @if($hasRequestedInformation)
                            @include('partials._additional_documents')
                        @endif
                    </div>
                </div>

                @if ($buttons && count($buttons))
                    <div class="form-group m-b-lg pull-right">
                        @foreach ($buttons as $button)
                            <button type="submit" name="submit" value="{{$button['value']}}"
                                    class="btn btn-inline {{$button['classes']}}">
                                @if ($button['icons'])
                                    <i class="fa {{$button['icons']}}"></i>
                                @endif
                                {{$button['label']}}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </form>
    </div>
@endsection
@include('partials.bootstrap-slider._slider')
@push('additional_scripts')
<script src="{{ asset('scripts/loan_documents_response.js') }}"></script>
{{-- Disable editing of select dropdown --}}
<script type="text/javascript">
    (function($) {
        $(document).ready(function() {
           $('select[data-disabled="disabled"] option:not(:selected)').prop('disabled', true);
        });
    })(window.jQuery);
</script>
@endpush