<?php
# Logout - module - log out the user

if (!isset($con)) {
    # GET KERNEL
    if (@!include('../../include/kernel.php')) exit('unable to communicate with commander');
}

if (!isset($verifier)) exiti('bad access'); // check not accessed via queryparser


$tokeni = $query[1] ?? ''; // attempt to obtain token
$cutoff = false; // prevent default output
$cookiejar = $_COOKIE; // store cookies
$cookieexceptions = [
    COOKIE_TOKEN_NAME
]; // cookie exception list

foreach ($cookieexceptions as $tohide) {
    if (array_key_exists($tohide, $cookiejar)) unset($cookiejar[$tohide]); // remove excluded cookies from removal list
}

if (!empty($cookiejar)) {
    if ($authtoken == $tokeni) {
        // if tokens match
        foreach ($cookiejar as $key => $value) setrawcookie($key, '', 1, '/', SITE_DOMAIN, true, true); // remove cookies
    } else {
        if (isset($verifier) && !sourceverifier()) {
            echo 'invalid token'; // invalid token
        } else {
            echo jsonres('error', 'invalid token', 0, 'msg');
        }
        $cutoff = true;
    }
}

if ($cutoff === false) {
    if (isset($verifier) && !sourceverifier()) {
        echo '<meta http-equiv="refresh" content="2; url=/">Logging out...';
    } else {
        echo 'logoutpls';
    }
}
