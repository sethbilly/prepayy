{{--Override flash messages style--}}
@if (session()->has('flash_notification.message'))
    <div class="alert alert-{{ session('flash_notification.level') }}
    {{ session()->has('flash_notification.important') ? 'alert-important' : '' }}
            alert-fill alert-close alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        {!! session('flash_notification.message') !!}
    </div>
    @elseif (session()->has('status'))
    <div class="alert alert-info alert-fill alert-close alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        {{session('status')}}
    </div>
@endif

@if (isset($errors) && count($errors))
    <div class="alert alert-danger alert-fill alert-close alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{$error}}</li>
            @endforeach
        </ul>
    </div>
@endif