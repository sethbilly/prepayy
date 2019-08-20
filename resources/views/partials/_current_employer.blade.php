@php
    $canEdit = !isset($isEditable) || $isEditable;
@endphp
<div class="">
    <h5 class="with-border">Current Employer Details</h5>
    <p class="form-label m-t-md">
        Are you currently employed? If yes please provide your employment details
    </p>
    <div class="row m-t-lg">
        <div class="col-md-4 col-sm-12">
            <fieldset class="form-group">
                <label class="form-label">Name of Employer</label>
                <div class="form-group">
                    <select name="employer[id]" class="form-control"
                            {{$canEdit ? '' : 'data-disabled=disabled readonly'}}
                            id="select-employer">
                        <option value="">-- Select Employer --</option>
                        @if (isset($employers))
                            @foreach ($employers as $employer)
                                <option value="{{$employer->id}}" {{old('employer.id', $currentEmployer->id) == $employer->id ? 'selected' : ''}}>{{$employer->name}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-12">
            <fieldset class="form-group">
                <label class="form-label">Contract Type</label>
                <div class="form-group">
                    <select name="employer[contract_type]" class="form-control"
                            {{$canEdit ? '' : 'data-disabled=disabled readonly'}}
                            id="select-contract">
                        <option value="">-- Select Contract Type --</option>
                        @if (isset($contractTypes))
                            @foreach ($contractTypes as $type)
                                <option value="{{$type}}" {{old('employer.contract_type', $currentEmployer && $currentEmployer->pivot ? $currentEmployer->pivot->contract_type : '') == $type ? 'selected' : ''}}>{{$type}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-12">
            <fieldset
                    class="form-group {{ $errors->has('employer.position') ? ' has-error' : '' }}">
                <label class="form-label">Position/Job Title</label>
                <input type="text" class="form-control"
                       name="employer[position]"
                       {{$canEdit ? '' : 'readonly'}}
                       placeholder="E.g. Sales Officer"
                       value="{{ old('employer.position', $currentEmployer->pivot->position ?? '') }}">
                @if ($errors->has('employer.position'))
                    <small class="text-danger">
                        <strong>{{ $errors->first('employer.position') }}</strong>
                    </small>
                @endif
            </fieldset>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <fieldset
                    class="form-group {{ $errors->has('employer.department') ? ' has-error' : '' }}">
                <label class="form-label">Department</label>
                <input type="text" class="form-control" name="employer[department]"
                       placeholder="E.g. Sales and Marketing"
                       {{$canEdit ? '' : 'readonly'}}
                       value="{{ old('employer.department', $currentEmployer->pivot->department ?? '') }}">
                @if ($errors->has('employer.department'))
                    <small class="text-danger">
                        <strong>{{ $errors->first('employer.department') }}</strong>
                    </small>
                @endif
            </fieldset>
        </div>
        <div class="col-md-4 col-sm-12">
            <fieldset
                    class="form-group {{ $errors->has('employer.salary') ? ' has-error' : '' }}">
                <label class="form-label">Monthly Salary ({{$currency}})</label>
                <input type="text" class="form-control" name="employer[salary]" placeholder="E.g. 2000"
                       {{$canEdit ? '' : 'readonly'}}
                       value="{{ old('employer.salary', $currentEmployer->pivot->salary ?? '') }}">
                @if ($errors->has('employer.salary'))
                    <small class="text-danger">
                        <strong>{{ $errors->first('employer.salary') }}</strong>
                    </small>
                @endif
            </fieldset>
        </div>
    </div>
</div>