<!DOCTYPE html>
<html lang="en">
<head>
    <title>Welcome | {{ config('app.name') }}</title>

    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no, minimal-ui"/>

    <!-- FONTS -->
    <link rel="stylesheet" href="{{ asset('css/lib/font-awesome/font-awesome.min.css') }}">
    <link href="{{ asset('home/css/icon-font.min.css') }}"  rel="stylesheet" >
    <link href='https://fonts.googleapis.com/css?family=Poppins:300,400,700' rel='stylesheet' type='text/css'>

    <!-- CSS -->
    <link href="{{ asset('home/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('home/css/style.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/homepage.css') }}">
    <link rel="shortcut icon" href="{{ asset('home/img/favicon.ico') }}" />
</head>
<body>

    {{--Site header--}}
    @include('partials._homepage_header')

    <div class="header-margin"></div>

    <div class="banner-wrapper clearfix text-center">
        <div class="background-block" style="background-image:url({{ asset('images/banner.jpeg') }});">
            <div class="container">
                <div class="slide-inner">
                    <div class="col-md-7 col-md-offset-3 m-t-md">
                        <h1 class="c-h1">Take loans in the comfort of your home or on the go.</h1>
                        <div class="simple-text size-2">
                            <p>
                                {{ config('app.name') }} is an all-purpose loan platform, offering you flexible loans
                                and the option to access additional funds after repayment over time.
                            </p>
                        </div>
                        <a class="c-btn type-1 m-t-md" href="{{ route('loan_products.browse') }}">Browse Loans</a>
                        <div class="empty-space marg-lg-b70 marg-md-b50"></div>
                        <div class="empty-space marg-lg-b200 marg-xs-b160"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--How it works--}}
    <div class="jumbotron" style="background-color: #333333; color: #FFFFFF;">
        <div class="container">
            <div class="col-md-12">
                <div class="col-sm-6 col-md-4 steps">
                    <p class="text">1. Browse Loan Products</p>
                </div>
                <div class="col-sm-6 col-md-4">
                    <p class="text">2. Check Your Eligibility</p>
                </div>
                <div class="col-sm-6 col-md-4">
                    <p class="text">3. Borrow and Repay</p>
                </div>
            </div>
        </div>
    </div>

    {{--Features--}}
    <div class="container">
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <br><br><br>
                <div class="title">
                    <div class="title-cat">Whatever you need finance for, we offer a flexible range of loans to help you afford it.</div>
                    <h2 class="c-h2"><small>Funds to fulfill your dreams</small></h2>
                </div>

                <div class="empty-space marg-lg-b40 marg-sm-b30"></div>

                <div class="tab-wrapper type-1">
                    <div class="tab-nav-wrapper">
                        <div class="tab-select">
                            <div class="select-arrow"><i class="fa fa-angle-down"></i></div>
                            <select>
                                <option selected="">Features</option>
                                <option>Other Features</option>
                                <option>Qualifying Criteria</option>
                                <option>Documentation Required</option>
                                <option>Benefits</option>
                            </select>
                        </div>
                        <div  class="nav-tab mbottom50">
                            <div class="nav-tab-item active">
                                <span class="lnr lnr-gift"></span>
                                <span class="analitics-text">Features</span>
                            </div>
                            <div class="nav-tab-item">
                                <span class="lnr lnr-tag"></span>
                                <span class="analitics-text">Other Features</span>
                            </div>
                            <div class="nav-tab-item">
                                <span class="lnr lnr-spell-check"></span>
                                <span class="analitics-text">Qualifying Criteria</span>
                            </div>
                            <div class="nav-tab-item">
                                <span class="lnr lnr-file-empty"></span>
                                <span class="analitics-text">Documentation Required</span>
                            </div>
                            <div class="nav-tab-item">
                                <span class="lnr lnr-thumbs-up"></span>
                                <span class="analitics-text">Benefits</span>
                            </div>
                        </div>
                    </div>
                    <div class="tabs-content clearfix mbottom50">
                        <div class="tab-info active">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="simple-text size-3">
                                        <h3><small>Features Include</small></h3>
                                        <ul>
                                            <li>Flexible repayment options through a salary account or via direct payroll deduction.</li>
                                            <li>Maximum loan term of 12 months.</li>
                                            <li>Maximum loan repayment period tied to retirement age.</li>
                                        </ul>
                                    </div>
                                    <div class="empty-space marg-sm-b30"></div>
                                </div>
                                <div class="col-md-7">
                                    <span class="lnr icons lnr-gift"></span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-info">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="simple-text size-3">
                                        <h3><small>Other Features</small></h3>
                                        <ul>
                                            <li>Loan repayments should not exceed 45% of your net monthly income.</li>
                                            <li>Competitive interest rates and facility fees.</li>
                                            <li>Top-up option available for existing loans after consistent repayment.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <span class="lnr icons lnr-tag"></span>
                                    <div class="empty-space marg-sm-b30"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-info">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="simple-text size-3">
                                        <h3><small>Qualifying Criteria</small></h3>
                                        <ul>
                                            <li>Applicant must be working for an organization in partnership with a financial institution</li>
                                            <li>Be in employment for 6 months and confirmed in current job role.</li>
                                            <li>Must be 21 years and not more than 60 years.</li>
                                            <li>Ghanaian citizens or legal residents in Ghana.</li>
                                        </ul>
                                    </div>
                                    <div class="empty-space marg-sm-b30"></div>
                                </div>
                                <div class="col-md-7">
                                    <span class="lnr icons lnr-spell-check"></span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-info">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="simple-text size-3">
                                        <h3><small>Documentation Required</small></h3>
                                        <ul>
                                            <li>Employer details.</li>
                                            <li>Proof of identity - Passport, Drivers's License, Voters Identity Card etc.</li>
                                            <li>Guarantor details.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <span class="lnr icons lnr-file-empty"></span>
                                    <div class="empty-space marg-sm-b30"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-info">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="simple-text size-3">
                                        <h3><small>Benefits</small></h3>
                                        <ul>
                                            <li>Ready cash to cover a range of personal and critical expenses.</li>
                                            <li>Flexible repayment options.</li>
                                            <li>No security or collateral required.</li>
                                            <li>Competitive interest rate.</li>
                                            <li>Flexible repayment tenure or period.</li>
                                        </ul>
                                    </div>
                                    <div class="empty-space marg-sm-b30"></div>
                                </div>
                                <div class="col-md-7">
                                    <span class="lnr icons lnr-thumbs-up"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="empty-space marg-lg-b50 marg-sm-b10"></div>
    </div>

    {{--Talk to us--}}
    <div class="jumbotron contact">
        <div class="container">
            <h2 class="c-h2"><small>Talk to us about loans</small></h2>
            <h5>Call +233 263 553118</h5>
        </div>
    </div>

    {{--Footer--}}
    <footer class="footer">
        <div class="container">
            <p class="text-muted white-text">
                &copy; CloudLoan 2017. Built by
                <a target="_blank" href="http://callenssolutions.com" class="white-text">
                    Callens Solutions
                </a>
            </p>
        </div>
    </footer>

    {{--Javascript--}}
    <script src="{{ asset('home/js/jquery.min.js') }}"></script>
    <script src="{{ asset('home/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('home/js/global.js') }}"></script>
</body>
</html>
