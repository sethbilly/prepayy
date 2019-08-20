<div class="tabs-section-nav tabs-section-nav-inline">
    <ul class="nav" role="tablist">
        @if($authUser->isBorrower())
            <li class="nav-item">
                <a class="nav-link {{ is_active_profile_menu('loan_applications.index') }}" href="{{ route('loan_applications.index') }}">
                    My Loans
                </a>
            </li>
        @endif

        @if(Request::is('applications'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('user.profile.index') }}">
                    Personal Details
                </a>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link active" href="#personal-details" role="tab" data-toggle="tab">
                    Personal Details
                </a>
            </li>
            @if($authUser->isBorrower())
                <li class="nav-item">
                    <a class="nav-link" href="#current-employment-details" role="tab" data-toggle="tab">
                        Current Employer Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#id-details" role="tab" data-toggle="tab">
                        ID Details
                    </a>
                </li>
            @endif
        @endif
    </ul>
</div>
