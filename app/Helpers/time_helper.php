<?php

if (!function_exists('get_local_date')) {
    function get_local_date($timestamp)
    {
        return date('d M Y', $timestamp);
    }
}

if (!function_exists('get_local_time')) {
    function get_local_time($timestamp)
    {
        return date('H:i:s', $timestamp);
    }
}

if (!function_exists('relative_local_time')) {
    function relative_local_time($timestamp)
    {
        $time = \CodeIgniter\I18n\Time::createFromTimestamp($timestamp);
        return $time->humanize();
    }
}

if (!function_exists('get_local_datetime')) {
    function get_local_datetime($timestamp)
    {
        return date('d M Y, H:i:s', $timestamp);
    }
}
