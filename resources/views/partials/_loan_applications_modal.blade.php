{{--Approve loan request modal--}}
<div class="modal fade"
     id="approve-loan-request-modal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="approve-loan-request-label"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{route('loan_applications.approve', ['application' => $application])}}">
                {!! csrf_field() !!}
                <input type="hidden" name="_method" value="put">
                <div class="modal-body">
                    <div class="row m-b-lg">
                        <div class="col-md-12 m-t-md text-center">
                            <h3 class="" id="approve-loan-request-label">Are you sure?</h3>
                            <span class="form-label">You cannot revert the process!</span>
                        </div>
                        <div class="col-md-12 m-t-md text-center">
                            <input type="hidden" name="status_id" value="{{isset($statusApprove) ? $statusApprove->id : ''}}">
                            <button type="button" class="btn btn-md btn-default" data-dismiss="modal">No, cancel
                            </button>
                            <button type="submit" class="btn btn-md btn-success">Yes, approve it!</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{--Decline loan request modal--}}
<div class="modal fade"
     id="decline-loan-request-modal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="decline-loan-request-label"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{route('loan_applications.approve', ['application' => $application])}}">
                {!! csrf_field() !!}
                <input type="hidden" name="_method" value="put">
                <div class="modal-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="modal-title" id="decline-loan-request-label">Decline Loan Request</h4>
                        </div>
                        <div class="col-md-6 text-right">
                            <input type="hidden" name="status_id" value="{{isset($statusDecline) ? $statusDecline->id : ''}}">
                            <button type="button" class="btn btn-md btn-default" data-dismiss="modal">Close
                            </button>
                            <button type="submit" class="btn btn-md btn-success">Decline Loan</button>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <span class="form-label">Please add your reason below.</span>
                    <textarea rows="3" name="comment" class="form-control m-t-md m-b-md"></textarea>
                </div>
            </form>
        </div>
    </div>
</div>