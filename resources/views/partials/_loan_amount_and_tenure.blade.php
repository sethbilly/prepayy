<h5 class="with-border">Loan Amount and Duration</h5>
<div class="row">
    <div class="col-md-4 col-sm-6">
        <fieldset
                class="form-group {{ $errors->has('amount') ? ' has-error' : '' }}">
            <label class="form-label">I want to borrow ({{$currency}})</label>
            <input class="slider form-control" data-slider-id='amount-slider' type="text"
                   data-slider-min="{{$product->min_amount}}" name="amount"
                   data-slider-max="{{$product->max_amount}}" data-slider-step="500"
                   data-slider-value="{{old('amount', $amount)}}"
                   data-slider-enabled="{{$isEditable}}"
                   value="{{old('amount', $amount)}}" {{$isEditable ? '' : 'readonly'}}/>
        </fieldset>
    </div>
    <div class="col-md-4 col-sm-6">
        <fieldset
                class="form-group {{ $errors->has('tenure') ? ' has-error' : '' }}">
            <label class="form-label">I want it for (no. of years)</label>
            <div class="form-control-wrapper">
                <input class="slider form-control"
                       data-slider-id='tenure-slider' name="tenure"
                       type="text" data-slider-min="1"
                       data-slider-max="10" data-slider-step="1"
                       data-slider-value="{{old('tenure', $tenure)}}"
                       data-slider-enabled="{{$isEditable}}"
                       value="{{old('tenure', $tenure)}}" {{$isEditable ? '' : 'readonly'}}/>
            </div>
        </fieldset>
    </div>
</div>