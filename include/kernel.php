<?php
# Kernel - the thing that sets up critical information, functions and manages the user system

# INIT CHECK
if (!defined('INIT_KERN')) exit('illegal call'); // kill execution if executed directly

# GLOBAL CONSTANTS
// db connection credentials
define('DB_HOST', 'localhost'); // database host
define('DB_USER', 'msg'); // database user
define('DB_PASSWORD', ''); // database password
define('DB_DATABASE', 'msg'); // database name
// cookie
define('COOKIE_AUTH_NAME', 'realm_sess'); // auth cookie name
define('COOKIE_AUTH_EXP', time()+1210000); // auth cookie expiration - 2 weeks
define('COOKIE_TOKEN_NAME', 'token'); // auth cookie name
define('COOKIE_TOKEN_EXP', time()+1210000); // auth cookie expiration - 2 weeks
define('COOKIE_TOKEN_LENGTH', 50); // length of token
// site info
define('SITE_DOMAIN', 'msg.uint.dev'); // generic cookie domain
define('SITE_NAME', 'msg.uint.dev'); // full name to display
define('SITE_NAME_FRIENDLY', 'msg'); // shorter name to display
define('SITE_VERSION', 55); // site version
define('SITE_BUILD', 'DEV'); // site build type
// configuration
define('CONFIG_SQL_CHARSET', 'utf8mb4'); // connection character set for mysql
define('CONFIG_SQL_CON_ERR_MSG', 'There seems to be a problem. It shall be hunted down.. meanwhile, mind checking back later on?'); // message to display if connection fails
define('CONFIG_MAINTENANCE', false); // puts entire site on maintenance mode
define('CONFIG_MAINTENANCE_MSG', 'The site is currently in maintenance mode. Check back later.'); // maintenance mode message
define('CONFIG_READONLY', false); // disallow writes to database (emergancy write stop)
define('CONFIG_READONLY_MSG', 'Warning: database is set to read only'); // r/o message
define('CONFIG_REGISTER_DISABLED', false); // disables registration
define('CONFIG_REGISTER_DISABLED_MSG', 'Registration is currently turned off'); // registration disabled message
// crypto & passwords
// !!! changing any of this section will make previously encrypted content undecryptable or/and accounts inaccessible - only alter when clearing encrypted strings, changing passwords or after migrating from one crypto to another
define('CRYPTO_METHOD', 'aes-256-gcm'); // method to use for crypto
/// if crypto_* value is empty, fill with *very* random characters (including special characters) -- more longer, the better
define('CRYPTO_TAG', ''); // tag
define('CRYPTO_PKEY_USR', ''); // partial key used both ways - auth uid
define('CRYPTO_AAD', ''); // cryptographic aad
define('CRYPTO_PKEY_USRDAT', ''); // partial userdata key
define('CRYPTO_AAD_USRDAT', ''); // crypto additional auth data
define('PASSWORD_HASH_TYPE', 'sha512'); // password hash - part of hashing process
// apis
define('API_MAIL', ''); // 32 character hexidecimal mailgun api key
define('API_GREC_SECRET', ''); // secret key for google recaptcha
define('API_GREC_SITE', ''); // site key for google recaptcha
// scripts
define('SCR_JQUERY_URI', 'https://code.jquery.com/jquery-3.4.1.min.js');
define('SCR_JQUERY_SRIHASH', 'sha384-vk5WoKIaW/vJyUAd9n/wmopsmNhiy+L2Z+SBxGYnUkunIxVxAv/UtMOhba/xskxh'); // sri hash for jquery resource https://www.srihash.org/
// request data
define('REQ_CLIENT_IP', $_SERVER['REMOTE_ADDR']); // client IP address
// global user configuration
define('USR_RES_UN_SPECIALCHARS', [
    ' ',
    '_',
    '-'
]); // special characters allowed in username
// config for session cookie - for protection
define('SERVER_SESS_ID', session_name());
define('SERVER_SESS_CHAR', 26); // * must match character length of session id
// content security policy
define('CSP_JS_POST', 'cspjs');
define('CSP_JS_CHARLIMIT', 30);


/**
 * JSON response formatter.
 * @param string $title Title of message.
 * @param string $msg Actual message.
 * @param integer $code Message code.
 * @param string $type Message type.
 * 
 * @return string
 */
function jsonres($title = 'info', $msg = 'null', $code = 0, $type = 'generic')
{
    header('Content-Type: application/json; charset=utf-8'); // set appropriate mime type for content-type header

    $rmsg = [
        'title' => $title,
        'body' => $msg,
        'code' => (int)$code,
        'type' => $type
    ]; // store response

    $rmsg = json_encode($rmsg); // convert to json

    return $rmsg;
}

# MAINTENANCE MODE
if (!defined('BYPASS_MM') && CONFIG_MAINTENANCE === true) {
    if (sourceverifier()) {
        exit(jsonres('error', CONFIG_MAINTENANCE_MSG, 3, 'msg'));
    } else {
        exit(CONFIG_MAINTENANCE_MSG);
    }
}

# DATABASE CONNECTION
$con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE); // create connnection

if ($con->connect_errno || !$con->set_charset(CONFIG_SQL_CHARSET)) {
    // if connection fails or bad charset
    if (sourceverifier()) {
        exit(jsonres('error', CONFIG_SQL_CON_ERR_MSG, 3, 'msg'));
    } else {
        exit(CONFIG_SQL_CON_ERR_MSG);
    }
}

/**
 * Process killer.
 *
 * @param string $msg Message to halt with.
 * 
 * @return void
 */
function exiti($msg = 'error') {
    global $con;
    $con->close();
    exit($msg);
}

# VERIFY REQUEST SOURCE

/**
 * Verify request source.
 *
 * @return boolean
 */
function sourceverifier()
{
    $domains = [
        SITE_DOMAIN
    ]; // approved (sub-)domains list [int/ext access]

    $domaindummy = 'example.com'; // domain to use for referer if all goes bad for it

    $valid = false; // default for validation check
    $domainmat = false; // default for domain check
    $domainval = $_SESSION['domain'] ?? $_SERVER['HTTP_REFERER'] ?? $domaindummy; // attempt to use referer, fallback is dummy
    $domainval = parse_url($domainval, PHP_URL_HOST) ?? $domaindummy; // attempt to obtain information from referer

    if (in_array($domainval, $domains)) $domainmat = true; // check if referer contains the (sub-)domains whitelisted

    // TODO: When going to fetch(), remove check for HTTP_X_REQUEST_WITH as this will not be present.
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_SERVER['HTTP_REFERER']) && $domainmat === true) $valid = true;

    return $valid;
}

/**
 * Real character counter.
 *
 * @param string $str String to count characters of.
 * 
 * @return integer
 */
function realcount($str = '')
{
    $str = utf8_decode($str); // decode it (will get more real visual looking count when comes to MB chars)
    $str = strlen($str); // get string length

    return (int)$str;
}

/**
 * String generator.
 *
 * @param integer $length Length of generated string.
 * @param string $characters Characters to use in generated string.
 * 
 * @return string
 */
function strgen($length = 150, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$!_-.')
{
        $gene = '';

        for ($i = 0; $i < $length; $i++) $gene .= $characters[random_int(0, strlen($characters) - 1)];

        return $gene;
}

/**
 * TOR exit node check.
 *
 * @return boolean
 */
function IsTorExitPoint()
{
    $ipformat = strpos(REQ_CLIENT_IP, ':');

    if (!$ipformat) {

        $fields = ['QueryIP' => REQ_CLIENT_IP];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://torstatus.blutmagie.de/tor_exit_query.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($ch);
        return strstr($html, 'The IP Address you entered matches one or more active Tor servers'); // if the ip is listed then it's true
    } else {
        return true; // if using ipv6 then bypass check
    }
}

/**
 * Cryptography management.
 *
 * @param string $mode Encrypt or decrypt switch.
 * @param string $wtc Switch between keys.
 * @param string $str Data to encrypt or decrypt.
 * @param string $key Internally provided key.
 * @param string $iv Internally provided IV.
 * @param string $aad Internally provided AAD.
 * 
 * @return boolean
 */
function crypto($mode = 'enc', $wtc = 'usr', $str = '', $key = '', $iv = null, $aad = null)
{
    if ($wtc != 'usr' && $wtc != 'usrd' || is_null($iv) || is_null($aad)) {
        $res = false;
    } else {
        switch ($wtc) {
            case 'usr':
                $pkey = CRYPTO_PKEY_USR;
                $preaad = CRYPTO_AAD;
                break;
            case 'usrd':
                $pkey = CRYPTO_PKEY_USERDAT;
                $preaad = CRYPTO_AAD_USRDAT;
                break;
            default:
                $mode = 'null';
                break;
        }

        $jpkey = $pkey.$key;
        $jaad = $preaad.$aad;

        switch ($mode) {
            case 'enc':
                $key = openssl_random_pseudo_bytes(32);
                $iv = random_bytes(openssl_cipher_iv_length(CRYPTO_METHOD));
                $res = openssl_encrypt($str, CRYPTO_METHOD, $jpkey, OPENSSL_RAW_DATA, $iv, $jaad, CRYPTO_TAG); // encrypt
                if (!$res) {
                    $res = '! CRYPTO ERR: UNABLE TO ENCRYPT !';
                } else {
                    $res = [
                        'partialkey' => $key,
                        'iv' => $iv,
                        'result' => $res
                    ];
                }
                break;
            case 'dec':
                $res = openssl_decrypt($str, CRYPTO_METHOD, $pkey.$key, OPENSSL_RAW_DATA, $iv, $preaad.$aad, CRYPTO_TAG); // decrypt
                if (!$res) $res = '! CRYPTO ERR: UNABLE TO DECRYPT !';
                break;
            default:
                $res = false;
                break;
        }
    }
    return $res;
}

/**
 * Google reCaptcha API.
 *
 * @return boolean
 */
function recaptcha()
{
    $grcurl = 'https://www.google.com/recaptcha/api/siteverify'; // API URL

    if (isset($_POST['g-recaptcha-response'])) {
        $grcpres = $_POST['g-recaptcha-response']; // get recaptcha response from form
    } else {
        $grcpres = '';
    }

    $data = [
        'secret' => API_GREC_SECRET,
        'response' => $grcpres,
        'remoteip' => REQ_CLIENT_IP
    ];

    $curlConfig = [
        CURLOPT_URL => $grcurl,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $data
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $curlConfig);
    $grcres = curl_exec($ch);
    curl_close($ch);

    $grcdata = json_decode($grcres);

    if (!isset($grcdata->success) || $grcdata->success !== true) {
        return false;
    } else {
        return true;
    }
}

/**
 * Email (Mailgun API).
 *
 * @param string $from Email of self.
 * @param string $fromn Appended name.
 * @param string $usern Name of user.
 * @param string $to User email address.
 * @param string $subject Email subject.
 * @param string $body Body of email.
 * 
 * @return string
 */
function emailer($from = null, $fromn = null, $usern = null, $to = null, $subject = null, $body = null)
{
    if (is_null($from) || is_null($fromn) || is_null($usern) || is_null($to) || is_null($subject) || is_null($body)) {
        $result = false;
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:key-'.API_MAIL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/mail.'.SITE_DOMAIN.'/messages');
        curl_setopt($ch, CURLOPT_POSTFIELDS,
        [
            'from' => SITE_NAME.' '.$fromn.' <'.$from.'@mail.'.SITE_DOMAIN.'>',
            'to' => $usern.' '.'<'.$to.'>',
            'subject' => $subject,
            'html' => $body
        ]
        );

        $result = curl_exec($ch);
        curl_close($ch);
    }

    return $result;
}

# CONTENT SECURITY POLICY IDENTITY
if (isset($_POST[CSP_JS_POST]) && ctype_alnum($_POST[CSP_JS_POST]) && realcount($_POST[CSP_JS_POST]) == CSP_JS_CHARLIMIT) {
    $cspjs = $_POST[CSP_JS_POST];
} else {
    $cspjs = strgen(CSP_JS_CHARLIMIT, '0123456789abcdefABCDEF');
}
define('CSP_JS_ID', $cspjs);

# PHP SESSION ID FORMAT VERIFICATION
if (isset($_COOKIE[SERVER_SESS_ID])) {
    if (!preg_match('/^[a-z0-9]{'.SERVER_SESS_CHAR.'}$/', $_COOKIE[SERVER_SESS_ID])) {
        //setrawcookie(SERVER_SESS_ID, '', 1, '/', null, null, true);
        setcookie(SERVER_SESS_ID, '', [
            'expires' => COOKIE_AUTH_EXP,
            'path' => '/',
            'domain' => null,
            'secure' => null,
            'httponly' => true,
            'samesite' => 'Strict']);
        //TODO Check if cookie is actually set and perhaps store the array in a constant
        if (sourceverifier()) {
            exit(jsonres('error', 'session validation error - try again', 3, 'msg'));
        } else {
            exit('session validation error - try again');
        }
    }
}

# USER VERIFICATION & DATA
if (isset($_COOKIE[COOKIE_AUTH_NAME])) {

    $authd = $_COOKIE[COOKIE_AUTH_NAME]; // user session id
    $authd = $con->real_escape_string($authd);

    $userinfo = $con->query("SELECT `uid`,`puid`,`username`,`email` FROM `accounts` WHERE `uid`='$authd'"); // get user info
    $userexists = $userinfo->num_rows; // checks if user credentials are valid

    if ($userexists == 1) {
        $userinfoi = $userinfo->fetch_assoc(); // fetch user info

        $userun = $userinfoi['username']; // username
        $userpuid = $userinfoi['puid']; // public id
        $useruid = $userinfoi['uid']; // private id
        $useremail = $userinfoi['email']; // email
		
		$con->query("UPDATE `accounts` SET `time`=UNIX_TIMESTAMP(NOW()) WHERE `uid`='$useruid'"); // update online time
    } else {
        $authd = '';
        $userexists = 0; // user doesn't exist
    }
    $userinfo->free();
} else {
    $authd = '';
    $userexists = 0; // user doesn't exist
}

# TOKEN
$authtoken = $_COOKIE[COOKIE_TOKEN_NAME] ?? '';

/**
 * Token regeneration.
 *
 * @return void
 */
function tokenregen(): void {
    global $authtoken;
	$gentoken = strgen(COOKIE_TOKEN_LENGTH, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$!_-.');
    //setrawcookie(COOKIE_TOKEN_NAME, $gentoken, COOKIE_TOKEN_EXP, '/', SITE_DOMAIN, true, true);
    setcookie(COOKIE_TOKEN_NAME, $gentoken, [
        'expires' => COOKIE_TOKEN_EXP,
        'path' => '/',
        'domain' => SITE_DOMAIN,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict']);
    $authtoken = $gentoken;
}

/**
 * Token check.
 *
 * @param boolean $errmsg Show response.
 * @param boolean $enfgen Determine usage of JSON.
 * 
 * @return boolean
 */
function chktoken($errmsg = true, $enfgen = false) {
    global $authtoken;
    $tokenval = $_POST['token'] ?? '';
	if ($tokenval != $authtoken) {
        if ($errmsg) {
            if (!$enfgen && sourceverifier()) {
                exiti(jsonres('error', 'token mismatch - try refreshing', 10, 'msg'));
            } else {
                exiti('token mismatch - try refreshing');
            }
        } else {
            return true;
        }
	} else {
		return false;
	}
}
if (realcount($authtoken) != COOKIE_TOKEN_LENGTH) {
	tokenregen(); // re-create token if not using the correct length or doesn't exist
}

/**
 * Multi-functional rate limiter.
 *
 * @param string $rmode Read or write entry.
 * @param string $rtype Rate limiter type.
 * @param string $note Additional note regarding entry.
 * 
 * @return boolean
 */
function ratelimiter($rmode = 'r', $rtype = 'generic', $note = '') {
    /*

    - use IPs and maybe usernames if logged in for rate limiting
    - different rate limiting for different features
	- read and write modes
	- returns TRUE for success (rate limited [r] / write made [w]) and FALSE on failure (not rate limited [r] / unable to write [w])

    * ANTI-FLOOD SCRIPT START
    $floodvalidation = 0; // validator
    $floodd = 10; // flooded within this time frame (seconds)
    $floodp = 2; // maximum flood posts allowed in (+1 auto)
    $floodpro = mysqli_query($conn, "SELECT `id`,`ip`,`type`,`time` FROM `rate_limit` WHERE `ip`='".REQ_CLIENT_IP."' AND `time` > UNIX_TIMESTAMP(NOW())-$floodd ORDER BY id DESC LIMIT $floodp,$floodp"); // query
    $floodpro = mysqli_num_rows($floodpro); // get result count
    if ($floodpro > 0) {
        mysqli_query($conn, "UPDATE `accounts` SET `flood`='1' WHERE `username`='$logged'"); // prevent sending
        $floodvalidation = 1; // block send session
    } elseif ($floodpro == 0 && $flood == 1) {
        mysqli_query($conn, "UPDATE `accounts` SET `flood`='0' WHERE `username`='$logged'"); // allow sending
        $floodvalidation = 0; // free send session
    }
    * ANTI-FLOOD SCRIPT END
    */
    return true;
}
