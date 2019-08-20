@extends('layouts.master')
@section('title', $title)
@section('content')
    <header class="section-header">
        <div class="row">
            <div class="col-md-12">
                <h4>@yield('title')</h4>
            </div>
        </div>
    </header>
    <div class="box-typical box-typical-padding">
        <form class="form-horizontal" method="post"
              action="{{!empty($user->id) ? route('users.update', ['user' => $user]) : route('users.store')}}">
            {!! csrf_field() !!}
            @if (isset($user) && $user->id)
                <input type="hidden" name="_method" value="put"/>
            @endif
            <h5 class="with-border">User Details</h5>
            <div class="row">
                <div class="col-lg-4">
                    <fieldset class="form-group{{ $errors->has('firstname') ? ' has-error' : '' }}">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="firstname" placeholder="E.g. John" required
                               value="{{ old('firstname', $user->firstname)}}">
                        @if ($errors->has('firstname'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('firstname') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
                <div class="col-lg-4">
                    <fieldset class="form-group{{ $errors->has('lastname') ? ' has-error' : '' }}">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="lastname" placeholder="E.g. Doe" required
                               value="{{ old('lastname', $user->lastname)}}">
                        @if ($errors->has('lastname'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('lastname') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
                <div class="col-lg-4">
                    <fieldset class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" placeholder="E.g. johndoe@email.com"
                               required
                               value="{{ old('email', $user->email)}}">
                        @if ($errors->has('email'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('email') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
                <div class="col-lg-4">
                    <fieldset class="form-group{{ $errors->has('contact_number') ? ' has-error' : '' }}">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="contact_number" placeholder="E.g. 0305449520"
                               required
                               value="{{ old('contact_number', $user->contact_number)}}">
                        @if ($errors->has('contact_number'))
                            <small class="text-danger">
                                <strong>{{ $errors->first('contact_number') }}</strong>
                            </small>
                        @endif
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-6">
                    <div class="checkbox m-t-md {{ $errors->has('generate_password') ? ' has-error' : '' }}">
                        {{-- Force password generation for new accounts. Account and app owner passwords cannot be reset --}}
                        <input type="checkbox" name="generate_password" id="reset-password"
                                {{empty($user) || $user->hasAccountOrAppOwnerRole() ? 'disabled' : ''}}
                                {{empty($user) || empty($user->id) ? 'checked' : ''}}>
                        <label for="reset-password">
                            @if (!empty($user->id))
                                Generate password for <strong>{{ $user->getFullName() }}</strong>
                            @else
                                Generate password
                            @endif
                        </label>
                        @if ($errors->has('generate_password'))
                            <span class="help-block m-b-none">
                            <strong>{{ $errors->first('generate_password') }}</strong>
                        </span>
                        @endif
                        <p>
                            <strong>NB:</strong> A password reset link will be sent to this user via
                            email to guide him/her to setup a password
                        </p>
                    </div>
                </div>
            </div>

            @if(isset($roles) && $roles->count())
                <h5 class="with-border m-t-lg">List of Roles</h5>
                <div class="row">
                    @foreach($roles as $role)
                        <div class="col-md-3 col-sm-12">
                            <div class="checkbox">
                                <input type="checkbox" name="roles[]" id="roles-{{$role->id}}" value="{{ $role->id }}"
                                       data-is-approval-role="{{$role->canApproveLoans()}}" class="role-input"
                                        {{in_array($role->id, old('roles', $user->roles->pluck('id')->all())) ? 'checked' : ''}}>
                                <label for="roles-{{$role->id}}">
                                    {{$role->display_name}}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (isset($approvalLevels) && $approvalLevels->count())
                    <div id="approval-level-section" style="display:none;">
                        <h5 class="with-border m-t-lg">Select Approval Level</h5>
                        <div class="row">
                            <div class="col-md-3 col-sm-12">
                                <select name="approval_level_id" class="form-control select2">
                                    <option value="">-- Select Approval Level --</option>
                                    @foreach($approvalLevels as $approvalLevel)
                                        <option value="{{$approvalLevel->id}}"
                                                {{old('approval_level_id', $user->approval_level_id) == $approvalLevel->id ? 'selected' : ''}}>
                                            {{$approvalLevel->name}}&nbsp;(Level {{$loop->iteration}})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif
            @endif


            <div class="row">
                <div class="col-lg-12 m-t-lg text-right">
                    <fieldset class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save changes
                        </button>
                    </fieldset>
                </div>
            </div>

        </form>
    </div>
@endsection
@push('additional_scripts')
<script type="text/javascript">
    (function ($) {
        var selectors = {
            role: '.role-input',
            approvalSection: '#approval-level-section',
            approvalSelector: '#approval-level-section select'
        };

        $(document).ready(function () {
            registerRoleListeners();
            toggleApprovalLevelSectionDisplay();
        });

        function registerRoleListeners() {
            $(selectors.role).change(function () {
                toggleApprovalLevelSectionDisplay();
            });
        }

        function toggleApprovalLevelSectionDisplay() {
            var hasApprovalRole = false;

            $(selectors.role).each(function () {
                var isApprovalRole = parseInt($(this).data('is-approval-role')) === 1 && $(this).is(':checked');

                if (isApprovalRole) {
                    hasApprovalRole = true;
                    return false;
                }
            });

            if (hasApprovalRole) {
                $(selectors.approvalSection).show();
            } else {
                // Clear any selected approval level
                $(selectors.approvalSelector + ' option').prop('selected', false);
                $(selectors.approvalSelector).trigger('change');
                $(selectors.approvalSection).hide();
            }
        }
    })(window.jQuery)
</script>
@endpush