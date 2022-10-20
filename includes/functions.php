<?php

//==================================================================================================
//  Format bytes into other units
//==================================================================================================
function formatBytes($bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}


//==================================================================================================
//  Return the number of bytes from a text value
//==================================================================================================
function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}


//==================================================================================================
//  Show miliseconds on a human readable message
//==================================================================================================
function miliseconds2human($ss) {

    $ms = $ss % 1000;
    $s = floor(($ss % 60000) / 1000);
    $m = floor(($ss % 3600000) / 60000);
    $h = floor($ss / 3600000);

    $out = '<small>' . ($ms < 100 ? '0' : '') . ($ms < 10 ? '0' : '') . $ms . '</small>';
    $out = ($s < 10 ? '0' : '').($s ? $s : '0').'.'.$out;
    $out = ($m < 10 ? '0' : '').($m ? $m : '0').':'.$out;
    $out = ($h < 10 ? '0' : '').($h ? $h : '0').':'.$out;

    return $out;
}
