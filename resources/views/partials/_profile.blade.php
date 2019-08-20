@extends('layouts.borrower')
@section('profile_content')
    <div class="page-content">
        <div class="profile-header-photo">
            <div class="profile-header-photo-in"></div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-3 col-lg-4">
                    <aside class="profile-side">

                        <form method="POST" action="{{route('user.profile.store')}}" id="upload-picture-form" enctype="multipart/form-data" class="m-t-md">
                            {!! csrf_field() !!}

                            <section class="box-typical profile-side-user" style="height: 295px">

                                @if($authUser->isApplicationOwner())
                                    <a href="{{ route('callens.partners.index') }}">
                                        <p style="margin-left: -70px" class="text-success">
                                            <i class="fa fa-long-arrow-left"></i> Back to dashboard
                                        </p>
                                    </a>
                                    <br>
                                @elseif($authUser->isFinancialInstitutionStaff() || $authUser->isEmployerStaff())
                                    <a href="{{ route('roles.index') }}">
                                        <p style="margin-left: -70px" class="text-success">
                                            <i class="fa fa-long-arrow-left"></i> Back to dashboard
                                        </p>
                                    </a>
                                    <br>
                                @endif

                                <div class="avatar-preview avatar-preview-128 m-t-lg">
                                    @if(isset($authUser->picture->url))
                                        <img src="{{ $authUser->picture->url }}">
                                        <span class="update">
                                            <i class="font-icon font-icon-picture-double"></i>
                                            Update photo
                                        </span>
                                    @else
                                        <img src="{{ asset('images/avatar.png') }}">
                                        <span class="update">
                                            <i class="font-icon font-icon-picture-double"></i>
                                            Update photo
                                        </span>
                                    @endif
                                    <input type="file" name="picture" id="picture">
                                </div>
                                <h5>
                                    {{ $authUser->getFullName() }}
                                </h5>
                            </section>
                        </form>

                        <section class="box-typical">
                            <header class="box-typical-header-sm bordered">Info</header>
                            <div class="box-typical-inner">
                                <p class="line-with-icon">
                                    <i class="font-icon font-icon-pin-2"></i>
                                    {{ $authUser->country->name ?? 'N/A' }}
                                </p>
                                <p class="line-with-icon">
                                    <i class="font-icon font-icon-mail"></i>
                                    <a href="mailto:">
                                        {{ $authUser->email ?? 'N/A' }}
                                    </a>
                                </p>
                                <p class="line-with-icon">
                                    <i class="font-icon font-icon-phone"></i>
                                    {{ $authUser->contact_number ?? 'N/A' }}
                                </p>
                                <p class="line-with-icon">
                                    <i class="font-icon font-icon-calend"></i>
                                    {{ $authUser->dob ? $authUser->dob->format('jS M, Y') : 'N/A' }}
                                </p>
                            </div>
                        </section>
                        <section class="box-typical">
                            <header class="box-typical-header-sm bordered">Apply for a Loan!</header>
                            <div class="box-typical-inner text-center">
                                <h6 class="">
                                    <a href="{{ route('loan_products.browse') }}" class="btn btn-block btn-success">
                                        Browse all loans
                                    </a>
                                </h6>
                            </div>
                        </section>
                    </aside>
                </div>

                <div class="col-xl-9 col-lg-8">

                    @include('partials._status_messages')

                    <section class="tabs-section">
                        @yield('content')
                    </section>
                </div>

            </div>
        </div>
    </div>
@endsection
@push('additional_scripts')
<script>
    $('#picture').change(function () {
        $('#upload-picture-form').submit();
    });
</script>
@endpush