<form method="GET" action="{{ route('employer.loan_requests.index') }}">
    <div class="row">
        <div class="col-lg-4">
            <fieldset class="form-group">
                <select name="institutions[]" class="form-control" id="select-institution">
                    <option value="">-- Select Institution --</option>
                    @if (isset($institutions))
                        @foreach ($institutions as $institution)
                            <option value="{{$institution->id}}">{{$institution->name}}</option>
                        @endforeach
                    @endif
                </select>
            </fieldset>
        </div>
        <div class="col-lg-4">
            <fieldset class="form-group">
                <select name="employers[]" class="form-control" id="select-employer">
                    <option value="">-- Select Employer --</option>
                    @if (isset($employers))
                        @foreach ($employers as $employer)
                            <option value="{{$employer->id}}">{{$employer->name}}</option>
                        @endforeach
                    @endif
                </select>
            </fieldset>
        </div>
        <div class="col-lg-4 pull-right">
            <fieldset class="form-group">
                <button type="submit" class="btn btn-success btn-block btn-inline">
                    <i class="fa fa-search"></i> Search
                </button>
            </fieldset>
        </div>
    </div>
</form>