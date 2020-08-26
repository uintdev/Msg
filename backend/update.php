<?php
# Update - check for updates

# GET KERNEL
define('INIT_KERN', true); // verify kernel execution source
if (@!include('../include/kernel.php')) exit('unable to communicate with commander');


session_start(); // get session cookie ready for external verification

if (!sourceverifier()) exiti('access source denied');

chktoken();

if (isset($_POST['vi']) && $_POST['vi'] !== '') {
    $vii = explode(':', $_POST['vi']);
    if (!isset($vii[0]) || !isset($vii[1]) || isset($vii[2])) {
        $updat = [
            'res' => 'ERR',
            'msg' => 'Bad version format'
        ];
    } else if (!ctype_digit($vii[0])) {
        $updat = [
            'res' => 'ERR',
            'msg' => 'Bad data'
        ];
    } else if (SITE_BUILD !== $vii[1] || SITE_VERSION > $vii[0]) {
        $updat = [
            'res' => 'VERCHK',
            'currentv' => (int)$vii[0],
            'currentb' => $vii[1],
            'newv' => SITE_VERSION,
            'newb' => SITE_BUILD,
            'mode' => 'UPD',
            'msg' => 'New release available'
        ];
    } else if (SITE_VERSION <= $vii[0]) {
        $updat = [
            'res' => 'VERCHK',
            'currentv' => (int)$vii[0],
            'currentb' => $vii[1],
            'newv' => SITE_VERSION,
            'newb' => SITE_BUILD,
            'mode' => 'HALT',
            'msg' => 'Already on latest'
        ];
    } else {
        $updat = [
            'res' => 'ERR',
            'msg' => 'Unknown error'
        ];
    }
} else {
    $updat = [
            'res' => 'ERR',
            'res' => 'Missing data'
        ];
}

header('Content-Type: application/json; charset=utf-8'); // set content type header for json
echo json_encode($updat);
