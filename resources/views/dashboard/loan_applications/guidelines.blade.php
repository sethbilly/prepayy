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
@section('title', 'Before You Apply')
@section('content')
    <div class="row">
        <div class="col-md-8 center-box">
            <div class="tab-content">
                {{--Loan application intro guide--}}
                <div role="tabpanel" class="tab-pane fade in active" id="intro">

                    <h3 class="text-center m-b-lg">
                        <strong>Welcome, {{ $authUser->getFullName() }}</strong>
                    </h3>

                    <section class="box-typical box-typical-padding">
                        <h5 class="with-border">
                            @yield('title')
                        </h5>
                        <blockquote class="blockquote">
                            <p>
                                Our online application provides you with a fast and secure way to
                                obtain loans anytime you need it. We process loan applications 24/7 with exceptions to
                                major
                                holidays. The steps involved in applying for a loan are as follows:
                            </p>
                        </blockquote>
                        <article class="profile-info-item m-t-lg">
                            <section class="skill-item tbl">
                                <div class="tbl-row">
                                    <div class="tbl-cell tbl-cell-num">
                                        <div class="skill-circle skill-circle-num">1</div>
                                    </div>
                                    <div class="tbl-cell tbl-cell-txt">
                                        <strong>Verify Eligibility for the Loan</strong>
                                    </div>
                                </div>
                            </section>
                            <p>
                                You are only eligible for loans from financial institutions your current employer
                                is in partnership with. This simply means that you cannot apply for a loan from a
                                financial institution your employer is not in partnership with.
                            </p>
                            <br>
                            <section class="skill-item tbl m-t-lg">
                                <div class="tbl-row">
                                    <div class="tbl-cell tbl-cell-num">
                                        <div class="skill-circle skill-circle-num">2</div>
                                    </div>
                                    <div class="tbl-cell tbl-cell-txt">
                                        <strong>Complete the Loan Application Form</strong>
                                    </div>
                                </div>
                            </section>
                            <p>
                                After verifying your eligibility, you will be required to fill out a form that captures
                                your personal information, current employer’s details, identification card details and
                                loan guarantor’s details.
                                You can save the form and return to complete your application at a later date.
                            </p>
                            <br>
                            <section class="skill-item tbl m-t-lg">
                                <div class="tbl-row">
                                    <div class="tbl-cell tbl-cell-num">
                                        <div class="skill-circle skill-circle-num">3</div>
                                    </div>
                                    <div class="tbl-cell tbl-cell-txt">
                                        <strong>Request Employer’s Approval</strong>
                                    </div>
                                </div>
                            </section>
                            <p>
                                After filling out the loan application form, you can submit it to your employer for
                                approval. You can
                                begin tracking the loan application ones its approved.
                            </p>
                            <br>
                            <section class="skill-item tbl m-t-lg">
                                <div class="tbl-row">
                                    <div class="tbl-cell tbl-cell-num">
                                        <div class="skill-circle skill-circle-num">4</div>
                                    </div>
                                    <div class="tbl-cell tbl-cell-txt">
                                        <strong>Track Progress</strong>
                                    </div>
                                </div>
                            </section>
                            <p>
                                After a successful submission of your loan application form for approval by your
                                employer,
                                you will be able to track the status of your application.
                            </p>
                        </article>
                        <hr>
                        <form method="get" action="{{route('loan_applications.eligibility', ['partner' => $product->institution, 'product' => $product])}}">
                            <div class="row">
                                <input type="hidden" name="amount" value="{{$amount}}">
                                <input type="hidden" name="tenure" value="{{$tenure}}">
                                <div class="col-md-6">
                                    <div class="checkbox m-b-lg js-do-not-show-loan-intro-form">
                                        <input type="checkbox" name="skip_guidelines" value="1" id="dont-show-again">
                                        <label for="dont-show-again">Don't show this guide again</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="pull-right">
                                        <button type="submit" class="btn btn-success">
                                            Continue
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection