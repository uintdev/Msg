<?php
# Mail - module - list, read and reply to emails

if (!isset($con)) {
    # GET KERNEL
    if (@!include('../../include/kernel.php')) exit('unable to communicate with commander');
}

if (!isset($verifier)) exiti('bad access'); // check not accessed via queryparser


$mailinfo = $con->query("SELECT `id`,`sender`,`subject`,`recepient`,`secid` FROM `mail` WHERE `recepient`='".$userinfoi['puid']."'"); // get mail info - inbox
$mailexists = $mailinfo->num_rows;

if ($mailexists > 0) {

    while ($mailresults = $mailinfo->fetch_assoc()) {

        $livemailinfo = $con->query("SELECT `id`,`puid`,`username` FROM `accounts` WHERE `puid`='".$mailresults['sender']."'");
        $livemailresults = $livemailinfo->fetch_assoc();

        echo '<div class="mailselection action" data-launch="inbox/' . $mailresults['secid'] . '"><b>' . $mailresults['subject'];
        echo '</b><span style="float:right;color:grey;font-size:80%;">' . $livemailresults['username'] . '</span><br><span style="color:grey;font-size:90%;">' . $mailresults['subject'] . '</span></div>';

        $livemailinfo->free();

    }


} elseif ($mailexists <= 0) {
    echo 'you do not have any mail, yet';
} else {
	echo 'unable to load mail';
}

$mailinfo->free();
