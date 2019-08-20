@php
$isApproved = $isApproved ?? false;
$isDeclined = $isDeclined ?? false;
$canApprove = $canApprove ?? true;

$disableBtns = $isApproved || $isDeclined || !$canApprove;
@endphp
<div class="col-md-6 col-xs-12 text-right">
    <div class="form-group">
        <button id="approve-loan" class="btn btn-md btn-success" {{$disableBtns ? 'disabled' : ''}}
                data-toggle="modal" data-target="#approve-loan-request-modal">
            Approve
        </button>
        <button id="decline-loan" class="btn btn-md btn-danger" {{$disableBtns ? 'disabled' : ''}}
                data-toggle="modal" data-target="#decline-loan-request-modal">
            Decline
        </button>
        <button id="request-info" class="btn btn-md btn-info" {{$disableBtns ? 'disabled' : ''}}
                data-toggle="modal" data-target="#request-info-modal">
            @if ($authUser->isEmployerStaff())
                Request Changes
            @else
                Request Information
            @endif
        </button>
    </div>
</div>