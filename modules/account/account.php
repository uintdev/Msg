<?php
# Account - module - account management section

if (!isset($con)) {
    # GET KERNEL
    if (@!include('../../include/kernel.php')) exit('unable to communicate with commander');
}

if (!isset($verifier)) exiti('bad access'); // check not accessed via queryparser


echo 'This is the account page.';
