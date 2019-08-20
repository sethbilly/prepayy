@push('additional_styles')
<style>
    .underline {
        text-decoration: none;
        color: #5dca73;
    }

    .attachment {
        text-decoration: none;
        color: blue;
    }
</style>
@endpush
@extends('layouts.master')
@section('title', 'Loan Application Details')
@section('content')
    @if(isset($application))
        <section class="card card-default">
            <div class="card-block">
                {{--Loan application title--}}
                <div class="row m-t-md">
                    <div class="col-md-6">
                        <h4>
                            <strong>
                                @if ($authUser->isBorrower())
                                    <a href="{{route('loan_applications.edit', ['application' => $application])}}">{{ $application->loanProduct->name }}</a>
                                @else
                                    {{ $application->loanProduct->name }}
                                @endif
                                {{!$authUser->isFinancialInstitutionStaff() ? ' by ' . $application->loanProduct->institution->name : ''}}
                            </strong>
                        </h4>
                        <h6 class="text-success">
                            Amount: GHS {{ number_format($application->amount, 2) }} for
                            {{ $application->tenure_in_years }}
                            &nbsp;{{ str_plural('year', $application->tenure_in_years) }}
                        </h6>
                    </div>
                    @if ($authUser->isLoanApprover())
                        @include('partials._loan_approval_buttons', [
                           'isApproved' => $authUser->isEmployerStaff() ?
                                $application->getLoanApplicationStatus()->isEmployerApproved() :
                                $application->getLoanApplicationStatus()->isPartnerApproved(),
                           'isDeclined' => $authUser->isEmployerStaff() ?
                                $application->getLoanApplicationStatus()->isEmployerDeclined() :
                                $application->getLoanApplicationStatus()->isPartnerDeclined(),
                           'canApprove' => $authUser->canApproveLoanApplication($application)
                        ])
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="label label-warning">
                            {{ $application->loanApplicationStatus ? $application->loanApplicationStatus->display_status : 'N/A' }}
                        </label>
                        @if (!$authUser->isBorrower() && !empty($application->status_label))
                            <br/>
                            <small style="font-size:12px">{{$application->status_label->level}}</small>
                        @endif
                    </div>
                    @if ($authUser->isLoanApprover())
                        <div class="col-md-6 text-right">
                            Created by
                            <strong>{{$application->user->getFullName()}}</strong>&nbsp;
                            on {{ $application->created_at->format('jS M, Y') }}
                        </div>
                    @endif
                </div>

                <br>
                @if ($authUser->isFinancialInstitutionStaff())
                    <a href="{{route('loan_applications.credit_report', ['application' => $application])}}" class="btn btn-md btn-info">
                        Get Loan Credit Report
                    </a>
                @endif
                <br><br>

                <section class="tabs-section">
                    <div class="tabs-section-nav tabs-section-nav-inline">
                        <ul class="nav" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="#basic-details" role="tab" data-toggle="tab">
                                    Customer Details
                                </a>
                            </li>
                            @if (isset($requestedInformation) && $requestedInformation->count())
                                <li class="nav-item">
                                    <a class="nav-link" href="#additional-info" role="tab" data-toggle="tab">
                                        @if ($authUser->isEmployerStaff())
                                            Requested Changes
                                        @else
                                            Additional Information / Documents
                                        @endif
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="tab-content">
                        {{--Basic Details--}}
                        <div role="tabpanel" class="tab-pane fade in active" id="basic-details">
                            <div class="visible-xs m-t-lg"></div>
                            <div class="col-md-12 m-b-lg text-success">
                                <i class="font-icon font-icon-user"></i> Basic Details
                            </div>
                            <div class="row m-t-lg">
                                <div class="col-md-12">
                                    <div class="col-md-3">
                                        <label class="form-label">Full Name</label>
                                        <p>
                                            {{ $application->user->getFullName() }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Contact Number</label>
                                        <p>
                                            {{ $application->user->contact_number }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Date of Birth</label>
                                        <p>
                                            {{ $application->user->dob ? $application->user->dob->format('jS M, Y') : 'N/A'}}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Country</label>
                                        <p>
                                            {{ $application->user->country ? $application->user->country->name : 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">SSNIT Number</label>
                                        <p>
                                            {{ $application->user->ssnit ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{--Employer Details--}}
                            <div class="col-md-12 m-t-lg m-b-lg text-success">
                                <i class="fa fa-suitcase"></i> Employer Details
                            </div>
                            <div class="row m-t-lg">
                                <div class="col-md-12">
                                    <div class="col-md-3">
                                        <label class="form-label">Name of Employer</label>
                                        <p>
                                            {{ $employer->name ?? '' }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Contract Type</label>
                                        <p>
                                            {{ $employer->pivot ? $employer->pivot->contract_type : '' }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Position/Job Title</label>
                                        <p>
                                            {{ $employer->pivot ? $employer->pivot->position : '' }}
                                            &nbsp;{{$employer->pivot ? "({$employer->pivot->department})" : ''}}
                                        </p>
                                    </div>
                                    @if ($authUser->isEmployerStaff())
                                        <div class="col-md-3">
                                            <label class="form-label">Monthly Salary ({{$currency}})</label>
                                            <p>
                                                {{$currency}}
                                                {{ !empty($employer->pivot->salary) ? number_format($employer->pivot->salary) : 'N/A' }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{--ID Details--}}
                            <div class="col-md-12 m-t-lg m-b-lg text-success">
                                <i class="font-icon font-icon-contacts"></i> ID Details
                            </div>
                            <div class="row m-t-lg">
                                <div class="col-md-12">
                                    <div class="col-md-3">
                                        <label class="form-label">ID Type</label>
                                        <p>
                                            {{ $application->identificationCard->type }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">ID Number</label>
                                        <p>
                                            {{ $application->identificationCard->number }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Date of Issue</label>
                                        <p>
                                            {{ $application->identificationCard->issue_date->format('jS M, Y') }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Date of Expiry</label>
                                        <p>
                                            {{ $application->identificationCard->expiry_date->format('jS M, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{--Guarantor Details--}}
                            <div class="col-md-12 m-t-lg m-b-lg text-success">
                                <i class="font-icon font-icon-bookmark"></i> Guarantor Details
                            </div>
                            <div class="row m-t-lg">
                                <div class="col-md-12">
                                    <div class="col-md-3 col-sm-12">
                                        <label class="form-label">Guarantor</label>
                                        <p>
                                            {{ $application->guarantor->name }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <label class="form-label">Relationship</label>
                                        <p>
                                            {{ $application->guarantor->relationship }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <label class="form-label">Years Known</label>
                                        <p>
                                            {{ $application->guarantor->years_known }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <label class="form-label">Phone Number</label>
                                        <p>
                                            {{ $application->guarantor->contact_number }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <label class="form-label">Employer</label>
                                        <p>
                                            {{ $application->guarantor->employer }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <label class="form-label">Position/Job Title</label>
                                        <p>
                                            {{ $application->guarantor->position }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{--Additional info--}}
                        @if(isset($requestedInformation) && $requestedInformation->count())
                            <div role="tabpanel" class="tab-pane fade" id="additional-info">
                                <div class="row">
                                    <div class="col-md-12">
                                        @include('partials._additional_loan_request_messages')
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </section>

        @include('partials._loan_applications_modal')
        @include('partials._loan_applications_request_modal')

    @endif
@endsection