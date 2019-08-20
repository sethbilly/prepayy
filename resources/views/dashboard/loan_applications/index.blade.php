@push('additional_styles')
<style>
    .underline {
        text-decoration: none;
        color: #5dca73;
        border-bottom: solid 1px rgba(93, 202, 115, 0.3);
    }
</style>
@endpush
{{-- Use master layouts for employers and financial institutions --}}
@extends($authUser->isBorrower() ? 'partials._profile' : 'layouts.master')
@section('title', 'Loan Requests')
@section('content')
    @if($applications->count())
        @if($authUser->isFinancialInstitutionStaff())
            <div class="col-md-12">
                <h4>@yield('title')</h4>
            </div>
            <div class="row">
                <div class="col-md-12">
                    @foreach($applications as $application)
                        @include('partials._loan_applications')
                    @endforeach
                </div>
            </div>
        @elseif($authUser->isEmployerStaff())
            <div class="col-md-12">
                <h4>@yield('title')</h4>
            </div>
            <div class="row">
                <div class="col-md-12">
                    @foreach($applications as $application)
                        @include('partials._loan_applications')
                    @endforeach
                </div>
            </div>
        @else
            @include('partials._borrower_navigation')

            <div class="row">
                <div class="col-md-12">
                    <div class="box-typical box-typical-padding" style="border-radius: 0">
                        @foreach($applications as $application)
                            @include('partials._loan_applications')
                        @endforeach
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        @endif
        <div class="pull-left">
            {{$applications->appends(['limit' => request()->get('limit', 20)])->links()}}
        </div>
    @elseif ($authUser->isBorrower())
        @include('partials._borrower_navigation')
        <div class="box-typical box-typical-full-height" style="margin-top: -1px; border-radius: 0">
            <div class="center-content tbl">
                <div class="center-content-in">
                    <div class="center-content-icon">
                        <i class="font-icon font-icon-post fa-2x"></i>
                    </div>
                    <h4>You haven't applied for a loan yet</h4>
                    <a href="{{ route('loan_products.browse') }}" class="btn btn-secondary">
                        Browse loans
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="box-typical box-typical-full-height" style="margin-top: -1px; border-radius: 0">
            <div class="center-content tbl">
                <div class="center-content-in">
                    <div class="center-content-icon">
                        <i class="font-icon font-icon-revers fa-2x"></i>
                    </div>
                    <h4>No loan application requests available</h4>
                </div>
            </div>
        </div>
    @endif
@endsection