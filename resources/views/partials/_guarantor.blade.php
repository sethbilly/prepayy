@php
    $canEdit = !isset($isEditable) || $isEditable;
@endphp
<div class="box-typical box-typical-padding">
    <h5 class="with-border">Guarantor Details</h5>
    <p class="form-label">
        Please provide guarantor's details.
    </p>
    <div class="row m-t-lg">
        <div class="col-md-4 col-sm-12">
            <fieldset
                    class="form-group {{ $errors->has('guarantor.name') ? ' has-error' : '' }}">
                <label class="form-label">Guarantor's Full Name</label>
                <input type="text" name="guarantor[name]" class="form-control"
                       placeholder="E.g. John Doe"
                       {{$canEdit ? '' : 'readonly'}}
                       value="{{ old('guarantor.name', $guarantor->name ?? '') }}">
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-12">
            <fieldset
                    class="form-group {{ $errors->has('guarantor.relationship') ? ' has-error' : '' }}">
                <label class="form-label">Relationship</label>
                <div class="form-control-wrapper">
                    <input type="text" name="guarantor[relationship]" class="form-control"
                           placeholder="E.g Co-worker"
                           {{$canEdit ? '' : 'readonly'}}
                           value="{{ old('guarantor.relationship', $guarantor->relationship ?? '') }}">
                </div>
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-12">
            <fieldset
                    class="form-group {{ $errors->has('guarantor.years_known') ? ' has-error' : '' }}">
                <label class="form-label">Years Known</label>
                <div class="form-control-wrapper">
                    <input type="number" min="1" name="guarantor[years_known]" class="form-control"
                           placeholder="E.g 2"
                           {{$canEdit ? '' : 'readonly'}}
                           value="{{ old('guarantor.years_known', $guarantor->years_known ?? '') }}">
                </div>
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-12">
            <fieldset
                    class="form-group {{ $errors->has('guarantor.contact_number') ? ' has-error' : '' }}">
                <label class="form-label">Phone Number</label>
                <div class="form-control-wrapper">
                    <input type="tel" name="guarantor[contact_number]" class="form-control"
                           placeholder="E.g 0544909578"
                           {{$canEdit ? '' : 'readonly'}}
                           value="{{ old('guarantor.contact_number', $guarantor->contact_number ?? '') }}">
                </div>
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-12">
            <fieldset
                    class="form-group {{ $errors->has('guarantor.employer') ? ' has-error' : '' }}">
                <label class="form-label">Employer</label>
                <input type="text" class="form-control" name="guarantor[employer]"
                       placeholder="Where does your guarantor work?"
                       {{$canEdit ? '' : 'readonly'}}
                       value="{{ old('guarantor.employer', $guarantor->employer ?? '') }}">
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-12">
            <fieldset class="form-group">
                <label class="form-label">Position/Job Title</label>
                <div class='input-group'>
                    <input type="text" name="guarantor[position]" placeholder="E.g. Business Manager"
                           value="{{ old('guarantor.position', $guarantor->position ?? '') }}"
                           {{$canEdit ? '' : 'readonly'}}
                           class="form-control">
                </div>
            </fieldset>
        </div>
    </div>
</div>