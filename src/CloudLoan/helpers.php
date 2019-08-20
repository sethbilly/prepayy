<?php
/**
 * Created by PhpStorm.
 * User: benjaminmanford
 * Date: 1/13/17
 * Time: 5:07 PM
 */

if (! function_exists('is_active_route')) {
    function is_active_route($groupPrefix, $shouldReturnClass = true)
    {
        $isActive = strpos(request()->route()->getName(), $groupPrefix) !== false;

        if ($shouldReturnClass) {
            return $isActive ? "opened" : "";
        }

        return $isActive;
    }
}

// Active state for borrower menu
if (! function_exists('is_active_profile_menu')) {
    function is_active_profile_menu($groupPrefix, $shouldReturnClass = true)
    {
        $isActive = strpos(request()->route()->getName(), $groupPrefix) !== false;

        if ($shouldReturnClass) {
            return $isActive ? "active" : "";
        }

        return $isActive;
    }
}

// Array to object
if (! function_exists('array_to_object')) {

    /**
     * Converts a given array to \stdClass
     *
     * @param $data
     * @return \stdClass
     */
    function array_to_object(array $data)
    {
        return json_decode(json_encode($data));
    }
}