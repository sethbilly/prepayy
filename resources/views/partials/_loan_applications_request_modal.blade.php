{{--Request information / document modal--}}
<div class="modal fade"
     id="request-info-modal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="request-info-label"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{route('loan_applications.documents.request', ['application' => $application])}}">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="modal-title" id="request-info-label">
                                @if ($authUser->isEmployerStaff())
                                    Request for Loan Application Changes
                                    @else
                                    Request for Additional Information / Document
                                @endif
                            </h4>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-md btn-default" data-dismiss="modal">Close
                            </button>
                            <button type="submit" class="btn btn-md btn-success">Send</button>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <span class="form-label">
                        Please add your comment below.
                    </span>
                    <textarea rows="3" name="request" class="form-control m-t-md m-b-md"></textarea>
                </div>
            </form>
        </div>
    </div>
</div>

{{--Additional information / documents request modal--}}
<div class="modal fade"
     id="reply-request-info-modal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="reply-request-info-modal-label"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data" data-action="{{ route('loan_applications.documents.respond', ['application' => $application, 'document' => ':document']) }}">
                {!! csrf_field() !!}

                <div class="modal-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="modal-title" id="reply-request-info-label">Reply to request</h4>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close
                            </button>
                            <button type="submit" class="btn btn-sm btn-success">Send</button>
                        </div>
                    </div>
                </div>
                <div class="modal-body m-b-md">
                    <span class="form-label">
                        Please reply to the request by adding a comment or uploading a file.
                    </span>
                    <label class="m-t-md form-label">Comment</label>

                    {{--Comment--}}
                    <textarea rows="3" name="response" class="form-control m-b-md"></textarea>

                    {{--Upload image / file--}}
                    <label class="form-label">Upload Documents</label>
                    <input type="file" name="files[]" multiple>
                </div>
            </form>
        </div>
    </div>
</div>