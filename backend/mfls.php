<?php
# Multi-functional login script - allowing registration and access to accounts (if auth'd that is)
#
#
# To do: bruteforce protection


# GET KERNEL
define('INIT_KERN', true); // verify kernel execution source

if (@!include('../include/kernel.php')) exit('unable to communicate with commander [ERR_CON_2]');

if (!sourceverifier()) exiti('access denied');

chktoken(true, true);

#######################
# 0 = Login (DEFAULT) #
# 1 = Register        #
#######################

$switch = 0; // mode switch


// EMAIL
if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $email = htmlentities($email, ENT_QUOTES);
    $email = $con->real_escape_string($email);
    $email = trim($email);

    // check if user is going above set limit (if they bypass it client-side)
    $ecount = realcount($email);
    if ($ecount < 8 || $ecount > 40) exiti('email must be 8 - 40 characters long');
} else {
    exiti('email is mandatory');
}
// PASSWORD
if (isset($_POST['password'])) {

    $password = $_POST['password'];
    $password = $con->real_escape_string($password);

    $pcount = realcount($password);

    if ($pcount < 10 || $pcount > 160) exiti('password must be 10 - 160 characters long');

} else {

    exiti('you need to set a password'); // non-existing password input

}
// PASSWORD AGAIN
if (isset($_POST['passworda'])) {

    $passworda = $_POST['passworda'];

    if ($password != $passworda) exiti('both passwords don\'t match');

    $switch = 1;

}
// USERNAME
if (isset($_POST['username'])) {

    $username = $_POST['username'];
    $username = htmlentities($username, ENT_QUOTES);
    $username = $con->real_escape_string($username);
    $username = trim($username);

    $ucount = realcount($username);
    if($ucount < 4 || $ucount > 20) exiti('username must be 4 - 20 characters long');

    // character types exclusion list (bypass)
    $urcsticky = implode('\', \'', USR_RES_UN_SPECIALCHARS); // join allowed special characters
    $ulimits = str_replace(USR_RES_UN_SPECIALCHARS, '', $username); // bypass
    $ulimits = ctype_alnum($ulimits); // check if alphanumerical

    if ($ulimits === false) exiti('username must be alphanumerical (also allowed: \''.$urcsticky.'\')');

    $switch = 1;

}

# ACCOUNT EXISTANCE CHECK

if (!preg_match('/[\w\.+-]+@[\w\.-]+[.][\w]+/', $email)) exiti('please enter a valid email address. i.e. user@example.com'); // email format checker

$verify = $con->query("SELECT `id`,`uid`,`email`,`username`,`password` FROM `accounts` WHERE `email`='$email' COLLATE utf8mb4_general_ci"); // email presents

if (!$verify) exiti('an error has occurred. try again later.');

$valid = $verify->num_rows;

# LOGIN
if ($switch == 0) {

    if ($valid > 0) {

        $logindetails = $verify->fetch_assoc();
        $rhash = $logindetails['password'];
        $rusr = $logindetails['username'];
        $password = hash(PASSWORD_HASH_TYPE, $password); // password hash

        if (!password_verify($password, $rhash)) exiti('invalid email or password 1'); // password failed to verify

        echo '...welcome back ' , $rusr; // all good, log in
        $luid = $logindetails['uid'];
        setcookie(COOKIE_AUTH_NAME, $luid, [
            'expires' => COOKIE_AUTH_EXP,
            'path' => '/',
            'domain' => SITE_DOMAIN,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict']);
    } else {
        echo 'invalid email or password 2'; // incorrect credentials
    }
}

$verify->free();


# REGISTER
if ($switch == 1) {

    if (CONFIG_REGISTER_DISABLED === true) exiti(CONFIG_REGISTER_DISABLED_MSG); // regisitration disabled check
    if (CONFIG_READONLY === true) exiti(CONFIG_READONLY_MSG); // read only check

    // check if user checked the checkbox
    if (!isset($_POST['tos'])) exiti('you must accept the terms of service');

    // recaptcha check
    if (!recaptcha()) exiti('recaptcha is incomplete'); // recaptcha failure

    $uused = $con->query("SELECT `id`,`username` FROM `accounts` WHERE `username`='$username' COLLATE utf8mb4_general_ci"); // check if username already is in use
    if (!$uused) exiti('an error has occurred. try again later.');
    $uusedi = $uused->num_rows;

    if ($uusedi == 0 && $valid == 0) {

        $uid = strgen(); // uid for account
        $activkey = strgen(60); // activation key

        // just in case a puid already exists, we shall check and attempt to get an unused one
        $puidchk = false; // determines if the loop should end
        $puidchkcnt = 0; // retry count
        while ($puidchk === false) {
            if ($puidchkcnt == 3) exiti('error while creating account data - try again later'); // allowed retry attempts exceeded

            $puid = strgen(10, '0123456789ABCDEF'); // public id
            $puidq = $con->query("SELECT `id`,`puid` FROM `accounts` WHERE `puid`='$puid'");
            if (!$puidq) exiti('error while setting up account data - try again later');
            $puidc = $puidq->num_rows;

            if (($puidc <=> 1) == -1) {
                $puidchk = true; // doesn't already exist
            } else {
                ++$puidchkcnt; // it does exist
            }
            $puidq->free();
        }

        $avatar = '/img/default_avatar.jpg'; // default avatar
        $phash = hash(PASSWORD_HASH_TYPE, $password); // password hash
        $phash = password_hash($phash, PASSWORD_ARGON2ID); // password salted hash
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? false; // user agent
        if (!$ua) exiti('missing header'); // if user agent header is missing, halt
		$ua = htmlentities($ua, ENT_QUOTES);
        $ua = $con->real_escape_string($ua);
		
		/*
		add in iv, partital (en/de)cryption key and other crypto data for accounts table.
		encrypt uid, email, ip and so forth.
		
		how the migration might be implemented:
		if i were to do this.. if the full key were to be somehow compromised or i need to pony up the encryption
		then i would need to decrypt all that data using the current crypto data, re-encrypt it with the new crypto data,
		store the new crypto data under the user(s) (and messages) and then finally add in the information manually into the kernel script.
		oh and maintenance mode should be on to spare us from one thing that could be a pain in the back-side.
		this would all have to be done via php cli with enough memory and a high enough execution time (which gladly cli has its own php.ini).. we don't want to end up with bad data due to it taking quite a bit.
		the script(s) should be stored in a directory that nginx is going to be set to throw a 403 on.
		at the time of typing this, i had implemented a maintenance bypass so that the maintenance script could use the connection and user defined functions in the kernel.
		the bypass can be done by defining the constant 'BYPASS_MM' before including the kernel file.
		
		we need to consider possible complications as a result of the implementation, so:
		- for login, we need to encrypt the email and look it up. if it exists then pull the encrypted password from the table,
		  decrypt it and then check it within the password_hash function.
		- if the session id were to be encrypted in the database and decrypted in a user's cookie then we would need to try decrypting it
		  each load time. this might slow things down in terms of performance but it shouldn't be so noticeable. it varies on how many and what things are being en/decrypted on that one page load.
		- the main concern is if the crypto fails. it might not happen at all but it's better to be safe than sorry. it needs to be handled.
        the function returns the false boolean on failure but using that data it needs to be handled gracefully in order to avoid issues in the middle of operations. perhaps we try to do the crypto magic and store it somewhere in memory before it gets into the stuff that would result in certain database actions? notifying the user that something went wrong would be one way of handling it.
		*/

        $createaccount = $con->query("INSERT INTO `accounts` (`uid`, `puid`, `email`, `username`, `password`, `ip`, `useragent`, `avatar`, `time`,`activationkey`,`regtime`) VALUES ('$uid','$puid','$email','$username','$phash','".REQ_CLIENT_IP."','$ua','$avatar',UNIX_TIMESTAMP(NOW()),'$activkey',UNIX_TIMESTAMP(NOW()))");
        $uused->free();

        if (!$createaccount) exiti('an error has occurred. try again later.'); // query broke

        # EMAIL ACTIVATION
        $mto = $email; // to send email to
        $mfrom = 'accounts';
        $mfromn = 'Account Activation';
        $msubject = 'Account Activation'; // subject
        $mbody = '
            <html>
            <body>
            Hello and welcome to ' . SITE_NAME_FRIENDLY . '.
            <br/>
            <br/>
            To activate your account, just press the link below or copy and paste it into your URL bar.
            <br/>
            You will have 24 hours to activate this account. If it isn\'t activated before then, the account will be removed.
            <br/>
            <a href="https://' . SITE_DOMAIN . '/actistaging/' . $activkey . '">https://' . SITE_DOMAIN . '/actistaging/' . $activkey . '</a>
            <br/>
            IP used for registration: '.REQ_CLIENT_IP.'
            <br/>
            <br/>
            <br/>
            Didn\'t perform this request? Simply ignoring this email will do.
            <br/>
            <br/>
            <br/>
            Remember that we never ask for passwords.
            <br/>
            <br/>
            <br/>
            A service provided by <a href="https://uint.dev/">Muffin.Cloud</a>.
            </body>
            </html>
            '; // email body

        // TODO: set up email verification and then mailgun
        /*
        if (!emailer($mfrom, $mfromn, $username, $mto, $msubject, $mbody)) {
            // error handling here if any of the params aren't filled
        }
        */

        setcookie(COOKIE_AUTH_NAME, $uid, [
            'expires' => COOKIE_AUTH_EXP,
            'path' => '/',
            'domain' => SITE_DOMAIN,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict']); // set cookie
        echo '...and you\'re in!'; // success on registration

    } elseif ($uused > 0 || $valid > 0) {
            echo 'email or username already in use'; // email or username exists
    } else {
            echo 'failed to register - try again later'; // query went bad
    }
}

$con->close();
