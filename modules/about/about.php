<?php
# About - module - site information

if (!isset($con)) {
    # GET KERNEL
    if (@!include('../../include/kernel.php')) exit('unable to communicate with commander');
}

if (!isset($verifier)) exiti('bad access'); // check not accessed via queryparser


$info = 'msg.uint.dev - a messaging service<br>----------------------------<br>Build number: '.SITE_VERSION.'<br>Build type: '.SITE_BUILD;

echo $info;
