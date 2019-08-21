<?php
# Query parser - the secondary brains of the framework
#
#
# TODO: remove exiti() where not neccessary (especially for on-page includes).

if (!isset($con)) {
    # GET KERNEL
    define('INIT_KERN', true); // verify kernel execution source
    if (@!include('../include/kernel.php')) exit('unable to communicate with commander');

    chktoken();
}

if (!isset($verifier)) {
    session_start(); // get session cookie ready for external verification
}

if (isset($verifier) || sourceverifier()) {
    $verifier = true; // make sure that resulting content isn't directly linked to
} else {
    exiti($con, 'access source denied');
}


# QUERY CHECK
if (isset($_GET['query']) && $_GET['query'] != '') {
    $query = $_GET['query'];
    $query = $con->real_escape_string($query);
    $query = explode('/', $query); // query splitting
} else {
    $query = [''];
}

// include locations
if (sourceverifier()) {
    $bitpath = '../'; // goes back a directory
} else {
    $bitpath = '';
}

$moduleinfo = $con->query("SELECT `id`,`qid`,`name`,`parameters`,`path`,`loginreq` FROM `pages` WHERE `qid`='$query[0]'"); // selects page based on primary query id

if (!$moduleinfo) {
    // page checker
    if (sourceverifier()) {
        exiti($con, jsonres('error', 'an error has occurred. try again later.', 2, 'msg'));
    } else {
        exiti($con, 'an error has occurred while attempting to process your request');
    }
}

$moduleexists = $moduleinfo->num_rows;
$moduleinfoi = $moduleinfo->fetch_assoc();

# PRIVS
/*
Please, clean this mess up.
*/
$loginreqa = false; // auth
$loginreql = 0; // login request ID

if ($userexists == 0 && $moduleinfoi['loginreq'] == 0) {
    $loginreqa = true; // not logged in and no registered users allowed
    $loginreql = 1;
} elseif ($userexists == 0 && $moduleinfoi['loginreq'] == 2) {
    $loginreqa = true; // not logged in and allowed to access either way
    $loginreql = 2;
} elseif ($userexists == 1 && $moduleinfoi['loginreq'] == 1) {
    $loginreqa = true; // logged in and registered users only
    $loginreql = 3;
} elseif ($userexists == 1 && $moduleinfoi['loginreq'] == 2) {
    $loginreqa = true; // logged in and registered users only
    $loginreql = 4;
}

if ($query[0] == '') {
    // default page handling
    $mainid = $userexists;
    //if ($loginreql == 1) $mainid = 2; // TODO: PRIV ID ISSUE HERE - CORRECT MULI-PRIV DETECTION - insert OR into query
    $moduleinfo = $con->query("SELECT `id`,`name`,`path`,`loginreq`,`main` FROM `pages` WHERE loginreq='$mainid' AND main='1'");
    if (!$moduleinfo) {
		// query error
        if (sourceverifier()) {
            exiti($con, jsonres('error', 'an error has occurred. try again later.', 3, 'msg'));
        } else {
            exiti($con, 'an error has occurred while attempting to process your request');
        }
    }

    $moduleexists = $moduleinfo->num_rows;
    $moduleinfoi = $moduleinfo->fetch_assoc();

    if ($moduleexists == 1) {
        if (@!include($bitpath.$moduleinfoi['path'])) {
            if (sourceverifier()) {
                echo jsonres('error', 'bad access', 4, 'msg');
            } else {
                echo 'an error has occurred while attempting to process your request';
            }
        }
        // fallback
    } else {
        echo 'no default page set or conflict detected'; // unable to access file or more than one result
    }

} elseif ($moduleexists == 1 && $loginreqa === true) {
    if (@!include($bitpath.$moduleinfoi['path'])) {
        if (sourceverifier()) {
            echo jsonres('error', 'bad access', 5, 'msg');
        } else {
            echo 'an error has occurred while trying to process your request';
        }
    }
    // include existing result
} elseif ($moduleexists == 1 && $loginreqa === false) {
    // not allowed to access page (login privs)
    if (sourceverifier()) {
        echo jsonres('error', 'please log in', 6, 'msg');
    } else {
        echo 'please log in';
    }
} elseif ($moduleexists == 0) {
    // page not found
    if (sourceverifier()) {
        echo jsonres('error', 'broken link - try again later', 7, 'msg'); // alert message
    } else {
        header($_SERVER['SERVER_PROTOCOL'].' 404 not found');
        echo 'section not found - try going somewhere else'; // on-page message
    }
} else {
    // fallback - should not ever get to this point but just in case ...
    if (sourceverifier()) {
        echo jsonres('error', 'an error had occured', 8, 'msg');
    } else {
        echo 'an error had occured';
    }
}

$con->close(); // closing connection
