<nav class="side-menu side-menu-compact">
    <ul class="side-menu-list">
        @foreach($sideNavLinks as $link)
        <li class="grey {{ is_active_route($link->active_link) }}">
            <a href="{{ Route::has($link->route) ? route($link->route) : '#' }}">
                <i class="{{ $link->icon }}"></i>
                <span class="lbl">
                    {{ $link->title }}
                </span>
            </a>
        </li>
        @endforeach
    </ul>
</nav>
