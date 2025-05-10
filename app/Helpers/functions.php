<?php

if (!function_exists('format_days')) {
    /**
     * Format days value, showing whole numbers as integers
     */
    function format_days($days)
    {
        return $days == floor($days) ? (int)$days : number_format($days, 1);
    }
}