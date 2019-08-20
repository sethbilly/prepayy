@php
    $canEdit = !isset($isEditable) || $isEditable;
@endphp
<h5 class="with-border">Personal Details</h5>
<div class="row">
    <div class="col-md-4 col-sm-6">
        <fieldset
                class="form-group {{ $errors->has('user.firstname') ? ' has-error' : '' }}">
            <label class="form-label">First Name</label>
            <input type="text" name="user[firstname]" class="form-control"
                   placeholder="E.g. John"
                   {{$canEdit ? '' : 'readonly'}}
                   value="{{ old('user.firstname', $user->firstname) }}">
        </fieldset>
    </div>
    <div class="col-md-4 col-sm-6">
        <fieldset
                class="form-group {{ $errors->has('user.lastname') ? ' has-error' : '' }}">
            <label class="form-label">Last Name</label>
            <div class="form-control-wrapper">
                <input type="text" name="user[lastname]" class="form-control"
                       placeholder="E.g Doe"
                       {{$canEdit ? '' : 'readonly'}}
                       value="{{ old('user.lastname', $user->lastname) }}">
            </div>
        </fieldset>
    </div>
    <div class="col-md-4 col-sm-6">
        <fieldset
                class="form-group {{ $errors->has('user.othernames') ? ' has-error' : '' }}">
            <label class="form-label">Other Names</label>
            <div class="form-control-wrapper">
                <input type="text" name="user[othernames]" class="form-control"
                       placeholder="Your othernames"
                       {{$canEdit ? '' : 'readonly'}}
                       value="{{ old('user.othernames', $user->othernames) }}">
            </div>
        </fieldset>
    </div>
    <div class="col-md-4 col-sm-6">
        <fieldset
                class="form-group {{ $errors->has('user.contact_number') ? ' has-error' : '' }}">
            <label class="form-label">Contact Number</label>
            <input type="text" class="form-control" name="user[contact_number]"
                   placeholder="Your phone number"
                   {{$canEdit ? '' : 'readonly'}}
                   value="{{ old('user.contact_number', $user->contact_number) }}">
        </fieldset>
    </div>
    <div class="col-md-4 col-sm-6">
        <fieldset class="form-group">
            <label class="form-label">Date of Birth</label>
            <div class='input-group date'>
                <input type='text' name="user[dob]" class="form-control" placeholder="E.g. 05-01-1998"
                       {{$canEdit ? '' : 'readonly'}}
                       value="{{ old('user.dob', $user->dob ? $user->dob->format('d-m-Y') : '') }}"/>
                <span class="input-group-addon">
                    <i class="font-icon font-icon-calend"></i>
                </span>
            </div>
        </fieldset>
    </div>
    <div class="col-md-4 col-sm-6">
        <fieldset class="form-group">
            <label class="form-label">Country</label>
            <div class="form-group">
                <select name="user[country_id]" class="form-control"
                        id="select-country" {{$canEdit ? '' : 'data-disabled=disabled readonly'}}>
                    <option value="">-- Select Country --</option>
                    @if (isset($countries))
                        @foreach ($countries as $country)
                            <option value="{{$country->id}}" {{old('user.country_id', $user->country_id) == $country->id ? 'selected' : ''}}>{{$country->name}}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </fieldset>
    </div>
</div>
<div class="row">
    <div class="col-md-8 col-sm-6">
        <fieldset
                class="form-group {{ $errors->has('user.ssnit') ? ' has-error' : '' }}">
            <label class="form-label">SSNIT Number</label>
            <input type="text" class="form-control" name="user[ssnit]"
                   placeholder="Your SSNIT number"
                   {{$canEdit ? '' : 'readonly'}}
                   value="{{ old('user.ssnit', $user->ssnit) }}">
        </fieldset>
    </div>
</div>