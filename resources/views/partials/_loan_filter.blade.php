<form method="GET" action="{{ route('loan_products.browse') }}" class="loan-product-filter-form">
    <div class="row">
        <div class="col-lg-3 col-sm-12 hidden-xs">
            <fieldset class="form-group">
                <label>Financial Institutions (Banks)</label>
                <div>
                    <select name="institution_ids[]" data-placeholder="Loans from any institution" multiple
                            class="form-control select2">
                        <option value=""></option>
                        @foreach ($allInstitutions as $institution)
                            <option value="{{$institution->id}}" {{in_array($institution->id, $institution_ids ?? []) ? 'selected' : ''}}>
                                {{$institution->name}}</option>
                        @endforeach
                    </select>
                </div>
            </fieldset>
        </div>
        <div class="col-lg-3 col-sm-12">
            <fieldset class="form-group">
                <label>Loan Types</label>
                <div>
                    <select name="loan_type_id" class="form-control select2">
                        <option value="">-- Any Loan Type --</option>
                        @foreach ($loanTypes as $loanType)
                            <option value="{{$loanType->id}}" {{$loanType->id == $loan_type_id ? 'selected' : ''}}>
                                {{$loanType->name}}</option>
                        @endforeach
                    </select>
                </div>
            </fieldset>
        </div>
        <div class="col-lg-3 col-sm-12">
            <fieldset class="form-group">
                <label>I want to borrow ({{$currency}})</label>
                <div>
                    <input class="slider form-control" data-slider-id='amount-slider' type="text"
                           data-slider-min="{{$minLoanAmount}}" name="min_amount"
                           data-slider-max="{{$maxLoanAmount}}" data-slider-step="500"
                           data-slider-value="{{$min_amount ?? $minLoanAmount}}"/>
                </div>
            </fieldset>
        </div>
        <div class="col-lg-3 col-sm-12">
            <fieldset class="form-group">
                <label>I want it for (no. of years)</label>
                <div>
                    <input class="slider form-control"
                           data-slider-id='tenure-slider' name="tenure"
                           type="text" data-slider-min="1"
                           data-slider-max="10" data-slider-step="1" data-slider-value="{{$tenure ?? 1}}"/>
                </div>
            </fieldset>
        </div>
        <div class="col-lg-3" style="display:none">
            <fieldset class="form-group">
                <button type="submit" class="btn btn-success btn-block btn-inline" style="margin-top:11%;">
                    <i class="fa fa-search"></i> Apply
                </button>
            </fieldset>
        </div>
    </div>
</form>
@push('additional_scripts')
<script type="text/javascript">
    (function ($) {
        $(document).ready(function () {
            var formSelector = '.loan-product-filter-form';
            var selectors = {
                institutions: formSelector + ' select[name="institution_ids[]"]',
                loanTypes: formSelector + ' select[name="loan_type_id"]',
                minAmount: formSelector + ' input[name="min_amount"]',
                tenure: formSelector + ' input[name="tenure"]',
                form: formSelector,
                slider: '.slider'
            };

            for (var selector in selectors) {
                $(document).on('change', selector, function () {
                    $(selectors.form).submit();
                });
            }
        });
    })(window.jQuery);
</script>
@endpush