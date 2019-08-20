@extends('partials._profile')
@section('title', 'Profile')
@section('content')
    <section class="tabs-section">

        @include('partials._borrower_navigation')

        <form method="POST" action="{{route('user.profile.store')}}" class="m-t-md">
            {!! csrf_field() !!}
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade in active" id="personal-details">
                    <div class="box-typical box-typical-padding" style="margin-top: -20.5px; border-radius: 0">
                        @include('partials._profile_details', ['user' => $authUser ?? null])
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="current-employment-details">
                    <div class="box-typical box-typical-padding" style="margin-top: -20.5px; border-radius: 0">
                        @include('partials._current_employer', ['currentEmployer' => $currentEmployer ?? null])
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="id-details">
                    <div class="box-typical box-typical-padding" style="margin-top: -20.5px; border-radius: 0">
                        @include('partials._id_details', ['idCard' => $idCard ?? null])
                    </div>
                </div>
            </div>

            <div class="row">
                <p class="col-md-6">
                    We store your personal data in Ghana, West Africa.
                </p>
                <div class="col-md-6 text-right">
                    <fieldset class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save changes
                        </button>
                    </fieldset>
                </div>
            </div>
        </form>

    </section>
@endsection