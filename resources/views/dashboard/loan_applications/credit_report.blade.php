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
    @if(isset($report))
        <a href="{{route('loan_applications.show', ['application' => $application])}}">
            <small class="text-muted">
                <i class="fa fa-long-arrow-left"></i> Back to Loan Application
            </small>
        </a><br/>
        <section class="card card-default">
            <div class="card-block">
                {{--Loan application title--}}
                <div class="row m-t-md">
                    <div class="col-md-6">
                        <h4>
                            <strong>
                                Credit Report for {{$report->personalDetailsSummary->fullName}}
                            </strong>
                        </h4>
                    </div>
                </div>

                <section class="tabs-section">
                    <div class="tabs-section-nav tabs-section-nav-inline">
                        <ul class="nav" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="#basic-details" role="tab" data-toggle="tab">
                                    Customer Details
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#account-summary" role="tab" data-toggle="tab" class="nav-link">
                                    Account Summary
                                </a>
                            </li>
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
                                            {{ $report->personalDetailsSummary->fullName }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Date of Birth</label>
                                        <p>
                                            {{ $report->personalDetailsSummary->birthDate}}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Nationality</label>
                                        <p>
                                            {{ $report->personalDetailsSummary->nationality}}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Gender</label>
                                        <p>
                                            {{ $report->personalDetailsSummary->gender}}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Marital Status</label>
                                        <p>
                                            {{ $report->personalDetailsSummary->maritalStatus}}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Mobile Phone No</label>
                                        <p>
                                            {{ $report->personalDetailsSummary->cellularNo}}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Employer</label>
                                        <p>
                                            {{ $report->personalDetailsSummary->employerDetail}}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">No of Dependants</label>
                                        <p>
                                            {{ $report->personalDetailsSummary->dependants}}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{--Employer Details--}}
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="col-md-12 m-t-lg m-b-lg text-success">
                                        <i class="fa fa-suitcase"></i> Employment History
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="table table-responsive table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Name of Employer</th>
                                                    <th>Occupation</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($report->employmentHistory as $history)
                                                    <tr>
                                                        <td>{{ $history->employerDetail ?? '' }}</td>
                                                        <td>{{ $history->occupation ?? '' }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="col-md-12 m-t-lg m-b-lg text-success">
                                        <i class="font-icon font-icon-contacts"></i> ID Details
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="table table-responsive table-striped">
                                                <thead>
                                                <tr>
                                                    <th>ID Type</th>
                                                    <th>ID Number</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($report->identificationHistory as $history)
                                                    <tr>
                                                        <td>{{ $history->identificationType ?? '' }}</td>
                                                        <td>{{ $history->identificationNumber ?? '' }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{--ID Details--}}

                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="account-summary">
                            <div class="visible-xs m-t-lg"></div>
                            <div class="col-md-12 m-b-lg text-success">
                                <i class="font-icon font-icon-user"></i> Account Rating
                            </div>
                            <div class="row m-t-lg">
                                @include('partials.credit_report._account_rating')
                            </div>

                            <div class="col-md-12 m-t-lg m-b-lg text-success">
                                <i class="fa fa-suitcase"></i> Credit Account Summary
                            </div>
                            <div class="row m-t-lg">
                                @include('partials.credit_report._account_summary')
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    @else
        <p class="text-center" style="margin-top: 20px;">
            <span style="font-size: 15px;">
                Credit report for {{$application->getUser()->getFullName()}} was not found
            </span><br/><br/>
            <a href="{{route('loan_applications.show', ['application' => $application])}}">
                <small class="text-muted">
                    <i class="fa fa-long-arrow-left"></i> Back to Loan Application
                </small>
            </a>
        </p>
    @endif
@endsection