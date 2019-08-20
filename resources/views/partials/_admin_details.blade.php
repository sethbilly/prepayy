<h5 class="m-t-lg with-border">Administrator's Details</h5>
<div class="row">
    <div class="col-lg-4">
        <fieldset class="form-group{{ $errors->has('owner.firstname') ? ' has-error' : '' }}">
            <label class="form-label">Firstname</label>
            <input type="text" class="form-control" name="owner[firstname]" placeholder="E.g. John" required
                   value="{{ old('owner.firstname', $owner->firstname ?? '')}}">
            @if ($errors->has('owner.firstname'))
                <small class="text-danger">
                    <strong>{{ $errors->first('owner.firstname') }}</strong>
                </small>
            @endif
        </fieldset>
    </div>
    <div class="col-lg-4">
        <fieldset class="form-group{{ $errors->has('owner.lastname') ? ' has-error' : '' }}">
            <label class="form-label">Lastname</label>
            <input type="text" class="form-control" name="owner[lastname]" placeholder="E.g. Doe" required
                   value="{{ old('owner.lastname', $owner->lastname ?? '') }}">
            @if ($errors->has('owner.lastname'))
                <small class="text-danger">
                    <strong>{{ $errors->first('owner.lastname') }}</strong>
                </small>
            @endif
        </fieldset>
    </div>
    <div class="col-lg-4">
        <fieldset class="form-group{{ $errors->has('owner.email') ? ' has-error' : '' }}">
            <label class="form-label">E-Mail Address</label>
            <input type="email" class="form-control" name="owner[email]"
                   placeholder="E.g. johndoe@institution.com"
                   value="{{ old('owner.email', $owner->email ?? '') }}"
                   required>
            @if ($errors->has('owner.email'))
                <small class="text-danger">
                    <strong>{{ $errors->first('owner.email') }}</strong>
                </small>
            @endif
        </fieldset>
    </div>
    <div class="col-md-4 col-sm-6">
        <fieldset class="form-group{{ $errors->has('owner.contact_number') ? ' has-error' : '' }}">
            <label class="form-label">Contact Number</label>
            <input type="text" class="form-control" name="owner[contact_number]"
                   placeholder="E.g. 030544909311"
                   value="{{ old('owner.contact_number', $owner->contact_number ?? '') }}">
            @if ($errors->has('owner.contact_number'))
                <small class="text-danger">
                    <strong>{{ $errors->first('owner.contact_number') }}</strong>
                </small>
            @endif
        </fieldset>
    </div>

    <div class="col-md-12 col-sm-6">
        <div class="checkbox m-t-lg {{ $errors->has('generate_password') ? ' has-error' : '' }}">
            {{-- Force password generation for new accounts --}}
            <input type="checkbox" name="generate_password" id="check-1"
                    {{empty($owner) || empty($owner->id) ? 'checked disabled' : ''}}>
            <label for="check-1">
                Generate password for <strong>User</strong>
            </label>
            @if ($errors->has('generate_password'))
                <span class="help-block m-b-none">
                            <strong>{{ $errors->first('generate_password') }}</strong>
                        </span>
            @endif
        </div>
        <p>
            <strong>NB:</strong> A password reset link will be sent to this user via
            email to guide him/her setup a password
        </p>
    </div>

</div>

<div class="row">
    <div class="col-md-12 m-t-lg text-right">
        <fieldset class="form-group">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-save"></i> Save changes
            </button>
        </fieldset>
    </div>
</div>