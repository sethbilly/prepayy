@php
$canEdit = !isset($isEditable) || $isEditable;
@endphp
<div class="">
    <h5 class="with-border">ID Details</h5>
    <p class="form-label m-t-md">
        Please provide your id details below
    </p>
    <div class="row m-t-lg">
        <div class="col-md-4 col-sm-6">
            <fieldset class="form-group">
                <label class="form-label">ID Type</label>
                <div class="form-group">
                    <select name="id_card[type]" class="form-control"
                            id="select-id" {{$canEdit ? '' : 'data-disabled=disabled readonly'}}>
                        <option value="">-- Select ID Type --</option>
                        @if (isset($idTypes))
                            @foreach ($idTypes as $type)
                                <option value="{{$type}}" {{old('id_card.type', $idCard->type ?? '') == $type ? 'selected' : ''}}>{{$type}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-6">
            <fieldset
                    class="form-group {{ $errors->has('id_card.number') ? ' has-error' : '' }}">
                <label class="form-label">ID Number</label>
                <input type="text" name="id_card[number]" class="form-control"
                       {{$canEdit ? '' : 'readonly'}}
                       placeholder="The ID card number"
                       value="{{ old('id_card.number', $idCard->number ?? '') }}">
                @if ($errors->has('id_card.number'))
                    <small class="text-danger">
                        <strong>{{ $errors->first('id_card.number') }}</strong>
                    </small>
                @endif
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-6">
            <fieldset class="form-group">
                <label class="form-label">Date of Issue</label>
                <div class='input-group date'>
                    <input type='text' name="id_card[issue_date]" class="form-control"
                           placeholder="E.g. 05-01-2017"
                           {{$canEdit ? '' : 'readonly'}}
                           value="{{ old('id_card.issue_date', $idCard && $idCard->issue_date ? $idCard->issue_date->format('d-m-Y') : '') }}">
                    <span class="input-group-addon">
                        <i class="font-icon font-icon-calend"></i>
                    </span>
                </div>
            </fieldset>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-sm-6">
            <fieldset class="form-group">
                <label class="form-label">Date of Expiry</label>
                <div class='input-group date'>
                    <input type='text' name="id_card[expiry_date]" class="form-control"
                           placeholder="DE.g. 05-01-2018"
                           {{$canEdit ? '' : 'readonly'}}
                           value="{{ old('id_card.expiry_date', $idCard && $idCard->expiry_date ? $idCard->expiry_date->format('d-m-Y') : '') }}">
                    <span class="input-group-addon">
                        <i class="font-icon font-icon-calend"></i>
                    </span>
                </div>
            </fieldset>
        </div>
    </div>
</div>