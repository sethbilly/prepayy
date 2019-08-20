<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 17/06/2016
 * Time: 17:24
 */
$default = <<<EOF
/* Top bar title color */
.site-title { color: #FFFFF; }

/* Top bar background color */
.site-header { background: #5dca73; }

/* Sub menu active links color */
.is-active { color: #5dca73; }

/* User profile dropdown menu text color */
.site-header .user-menu.dropdown .dropdown-toggle {
    color: #FFFFF;
}

/* User profile dropdown menu text color on hover */
.site-header .user-menu.dropdown .dropdown-toggle:hover,
.site-header .user-menu.dropdown.open .dropdown-toggle {
    color: #FFFFF;
}

/* User profile dropdown menu icon color */
.dropdown-item.current .font-icon,
.dropdown-item:hover .font-icon {
    color: #5dca73
}

/* User profile dropdown menu background and on hover color */
.dropdown-item.current, .dropdown-item:hover {
    background: 0 0;
    color: #5dca73
}
EOF;

return [
    'partner' => $default
];