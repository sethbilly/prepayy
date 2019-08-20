<div class="row" style="margin-left:0;margin-right: 0">
    @foreach($products as $product)
        <div class="col-md-12 m-b-md">
            <div class="row loan-product">
                @unless ($product->can_be_borrowed)
                    <div class="col-md-12 loan-product-unavailable">
                        <p>This is not available for the amount you want to borrow</p>
                    </div>
                @endunless
                <div class="col-md-12 loan-product-header">
                    <div class="col-md-2 hidden-xs"></div>
                    <div class="col-md-10" style="padding-left:0">
                        <h5>{{$product->institution->name}}&nbsp;{{$product->loanType->name}}</h5>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-12 col-lg-2 hidden-xs no-margin-right no-padding-right">
                    <div class="loan-product-institution">
                        {{$product->institution->name}}
                    </div>
                </div>
                <div class="col-lg-2 col-sm-12 hidden-xs no-margin-right no-padding-right">
                    <div class="loan-product-type loan-product-padding loan-product-border">
                        <small class="text-center">Loan Type</small>
                        <p>{{$product->loanType->name}}</p>
                    </div>
                </div>
                <div class="col-lg-2 col-sm-12 no-margin-right no-padding-right">
                    <div class="loan-product-apr loan-product-padding loan-product-border">
                        <small>Representative APR</small>
                        @if ($product->can_be_borrowed)
                            <p>{{$product->interest_per_year}}% APR</p>
                        @else
                            <p>N/A</p>
                        @endif
                    </div>
                </div>
                <div class="col-lg-2 col-sm-12 no-margin-right no-padding-right">
                    <div class="loan-product-total-payable loan-product-padding loan-product-border">
                        <small>Total Payable</small>
                        @if ($product->can_be_borrowed)
                            <p>{{$currency}}&nbsp;{{number_format($product->total_payable, 2)}}</p>
                        @else
                            <p>N/A</p>
                        @endif
                    </div>
                </div>
                <div class="col-lg-2 col-sm-12 no-margin-right no-padding-right">
                    <div class="loan-product-monthly-payable loan-product-padding loan-product-border">
                        <small>Monthly Payable</small>
                        @if ($product->can_be_borrowed)
                            <p>{{$currency}}&nbsp;{{number_format($product->monthly_payable, 2)}}</p>
                        @else
                            <p>N/A</p>
                        @endif
                    </div>
                </div>
                <div class="col-lg-2 col-sm-12 loan-product-apply">
                    @php
                        $guidelinesEndpoint = route('loan_applications.guidelines', [
                                'partner' => $product->institution,
                                'product' => $product,
                                'amount' => $min_amount ?? $minLoanAmount,
                                'tenure' => $tenure ?? 1
                                ]);
                        $applyEndpoint = $product->can_be_borrowed ? $guidelinesEndpoint : 'javascript:void(0)';
                    @endphp
                    <a href="{{$applyEndpoint}}"
                       class="btn btn-md btn-success {{$product->can_be_borrowed ? '' : 'disabled'}}">
                        Apply for Loan
                    </a>
                </div>
                @if ($product->can_be_borrowed)
                    <div class="col-sm-12 hidden-xs loan-product-payable-desc">
                        <p>
                            Representative example: The representative rate is
                            <strong>{{$product->interest_per_year}}%</strong>
                            (fixed) so if you borrow <strong>{{$currency}}
                                &nbsp;{{number_format($min_amount ?? $minLoanAmount, 2)}}</strong> over
                            <strong>{{$tenure ?? 1}} {{str_plural('year', $tenure ?? 1)}}</strong> at a rate of
                            <strong>{{$product->interest_per_year}}%</strong> (fixed), you will repay
                            <strong>{{$currency}}&nbsp;{{number_format($product->monthly_payable, 2)}}</strong>
                            for
                            <strong>{{ceil( ($tenure ?? 1) * 12 )}}</strong> months and
                            <strong>{{$currency}}&nbsp;{{number_format($product->total_payable, 2)}}</strong> in
                            total.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{$products->appends([
            'min_amount' => $min_amount,
            'search' => $search,
            'institution_ids' => $institution_ids,
            'tenure' => $tenure,
            'loan_type_id' => $loan_type_id,
            'limit' => $limit
            ])->links()}}
    @endforeach
</div>