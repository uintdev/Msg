<?php
# Main page - what everyone is going to face no matter what


# GET KERNEL
define('INIT_KERN', true); // verify kernel execution source
if (@!include('include/kernel.php')) exit('unable to communicate with commander [ERR_CON_1]');


$verifier = true; // make sure that resulting content isn't directly linked to
$instalink = true; // tell queryparser that we don't want a closed connection

session_start();
$domainvy = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']; // get domain name
if ($domainvy != '') {
    if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
        $protocol = 'http';
    } else {
        $protocol = 'https';
    }
    $domainvy = $protocol.'://'.$domainvy; // prepend protocol
    $_SESSION['domain'] = $domainvy; // set session
}

# SET HEADER
header("Content-Security-Policy: script-src 'self' 'strict-dynamic' 'nonce-".CSP_JS_ID."' 'unsafe-inline'; font-src 'self' data:; object-src 'none'; style-src 'self' ".$domainvy." 'unsafe-inline'; img-src 'self' https: data:;");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo SITE_NAME_FRIENDLY; ?></title>
<link rel="icon" href="/img/icon.png">
<link rel="apple-touch-icon-precomposed" href="/img/icon.png">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link rel="stylesheet" type="text/css" property="stylesheet" href="/css/styles.css" async>
<meta charset="utf-8">
<meta name="description" content="Messaging.">
<meta name="keywords" content="Message, Messaging, Communication, Send, Msg, Encrypt">
<meta name="theme-color" content="#7e61e5">
<script src="<?php echo SCR_JQUERY_URI; ?>" integrity="<?php echo SCR_JQUERY_SRIHASH; ?>" crossorigin="anonymous" nonce="<?php echo CSP_JS_ID; ?>"></script>
</head>
<body class="preload">
<div class="shell_loader show">
<div class="shell_loader_con">
<div class="shell_loader_logo"><?php echo SITE_NAME_FRIENDLY; ?></div>
<div class="shell_loader_text"><noscript>javascript required</noscript></div>
</div>
</div>
<div class="header">
<span class="menuwrapper"><span class="menutoggle"></span><span class="menuicon"></span></span>
<span class="title"><?php echo SITE_NAME_FRIENDLY; ?></span>
<span class="loading">...</span>
</div>
<div class="menulist">
<div class="menuheader">
<span class="title"><?php echo SITE_NAME_FRIENDLY; ?></span>
<hr>
</div>
<div class="menuselection">
<div class="menuoption" data-launch="" data-order="1">error</div>
</div>
<div class="menufooter">A <a href="https://uint.dev/" target="_blank" rel="noopener">uint.dev</a> project</div>
</div>
<div class="coverarea"></div>
<div class="zoneblock">
<?php
# GET PARSER
if (@!include('backend/queryparser.php')) {

    echo 'init parser error';
    if (is_resource($con)) $con->close();

}
?>
<br>
<br>
</div>
<div class="updatenot">waiting for update</div>
<script type="text/javascript" nonce="<?php echo CSP_JS_ID; ?>">
console.log("%c!!! WARNING !!!", 'color:red;font-weight:bold;text-align:center;font-size:30px;');
console.log("%cThis feature is intended for developers only.", 'font-size:15px;');
console.log("%cIf someone tells you to enter code here, it is most likely a trick that could result in your account being compromised.", 'font-size:15px;');
console.log("%cBe aware of what the code actually does before executing it.", 'font-size:15px;');
console.log('! MSG BUILD <?php echo SITE_VERSION.' '.SITE_BUILD ?> LOADED !');
function checkjson(jsonin) {
    try {
        var jsonva = window.JSON.parse(jsonin);
        if (jsonva && typeof jsonva === "object" && jsonva !== null) {
            return true;
        }
    } catch (e) {}
    return false;
}
function menuloader(jsonres = '') {
    $('.menuselection').html('');
    if (checkjson(jsonres)) {
        jsondat = window.JSON.parse(jsonres);
        for (var count in jsondat) {
            $('.menuselection').append('<div class="menuoption action" data-order="' + count + '" data-launch="' + jsondat[count]['path'] + '">' + jsondat[count]['name'] + '</div>');
        }
    } else {
        $('.menuselection').append('<div class="menuoption" data-order="1" data-launch="">malformed data</div>');
    }
}
var list = document.getElementsByClassName('preload')[0];
list.getElementsByClassName('shell_loader_text')[0].innerHTML = 'loading ...';
if (typeof $ == 'undefined') {
    list.getElementsByClassName('shell_loader_text')[0].innerHTML = 'error: unable to load library';
    console.error('ERROR: Library failed to load');
    librarypayload = false;
} else {
    librarypayload = true;
    console.log("%cINFO: JS library loaded - powered by jQuery "+jQuery.fn.jquery, 'color:#7e61e5;');
    window.onload = function() {
        $.get('/backend/menu.php', function(data) {
            if (data != '') {
                menuloader(data);
                setTimeout(function() {
                    $('.shell_loader_con').addClass('shell_loader_con_expand');
                }, 500);
                setTimeout(function() {
                    $('.shell_loader').addClass('shell_loader_fadeaway');
                }, 500);
                setTimeout(function() {
                    $('.shell_loader').removeClass('show');
                    $('.shell_loader_con').removeClass('shell_loader_con_expand');
                    $('.shell_loader').removeClass('shell_loader_fadeaway');
                    $('.shell_loader_text').html('');
                }, 1005);
            }
        }, 'text')
        .fail(function() {
            $('.shell_loader_text').html('component load failed');
        })
    }
    if (librarypayload === true) {
        menutog = false;
        querypending = false;
        menutogs = 150;
        pressc = 'rgba(126,97,229,0.4)';
        presscm = 'rgba(0,0,0,0.1)';
        cspjss = '<?php echo CSP_JS_ID ?>';
        sitev = '<?php echo SITE_VERSION ?>';
        siteb = '<?php echo SITE_BUILD ?>';
        csrfcookie = '<?php echo $authtoken ?>';
        function togglemenu(specialexception = 'none') {
            if (menutog === false) {
                menutog = true;
                $('.menutoggle').addClass('menutogglelock');
                $('.coverarea').addClass('show');
                setTimeout(function() {
                    $('.coverarea').addClass('coverareaa');
                }, 5);
                setTimeout(function() {
                    $('.menulist').addClass('menu_show');
                }, 150);
            } else if (menutog === true) {
                $('.menutoggle').removeClass('menutogglelock');
                $('.menulist').removeClass('menu_show');
                setTimeout(function() {
                    $('.coverarea').removeClass('coverareaa');
                    if (specialexception != 'm') {
                        menutog = false;
                    }
                }, 250);
                if (specialexception == 'm') {
                    setTimeout(function() {
                        $('.coverarea').removeClass('show');
                        menutog = false;
                    }, 400);
                }
            }
        }
        function navigator(location = '', ntype = '') {
            getquerybit = window.location.pathname.replace('/', '');
            if (getquerybit != location && ntype == 'n') {
                history.pushState('/' + location, '', '/' + location);
                delete getquerybit;
                getquerybit = window.location.pathname.replace('/', '');
            }
        }
        function reloadall(option = '') {
            $.get('/backend/menu.php', function(data) {
                if (data != '') {
                    menuloader(data);
                    if (option == 'm') navigator('','n');
                    $.post('/backend/queryparser.php?query', {cspjs: cspjss, token: csrfcookie}, function(datab) {
                        if (datab != '') {
                            menuloader(data);
                            if ($('.zoneblock').html() != '') {
                                $('.zoneblock').html(datab);
                            } else {
                                $('.zoneblock').html('invalid response');
                            }
                        }
                    }, 'text')
                    .fail(function() {
                        $('.zoneblock').html('please reload');
                    })
                }
            }, 'text')
            .fail(function() {
                $('.menuselection').append('<div class="menuoption" data-order="1">please reload</div>');
            })
        }
        function query(location = 'null', ntype = 'n') {
            querypending = true;
            // document.title = "This is the new page title.";
            if (menutog === true) {
                $('.coverarea').addClass('show');
            }
            $('.loading').addClass('loading_s'); // loading indicator
            $('.zoneblock').html('<div style="text-align:center;">loading</div>'); // content loading text
            locationsp = location.split('/');
            if (locationsp[0] != 'logout') {
                navigator(location, 'n');
            } else {
                navigator(location);
            }
            $.post('/backend/queryparser.php?query=' + location, {cspjs: cspjss, token: csrfcookie}, function(data) {
                if (data != '') {
                    if (checkjson(data) === true) {
                        jsonbit = window.JSON.parse(data);
                        jsontitle = jsonbit.title;
                        jsonbody = jsonbit.body;
                        $('.loading').removeClass('loading_s');
                        $('.zoneblock').html('<div style="text-align:center;">'+jsontitle+'<br>'+jsonbody+'</div>');
                    } else {
                        if (data == 'logoutpls') {
                            $('.loading').removeClass('loading_s');
                            $('.zoneblock').html('<div style="text-align:center;">logging out ...</div>');
                            reloadall('m');
                        } else {
                            $('.loading').removeClass('loading_s');
                            setTimeout(function() {
                                $('.coverarea').removeClass('show');
                            }, 320); // TODO - Removal timings -- change depending on navigation method
                            if ($('.zoneblock').html() != '') {
                                $('.zoneblock').html(data);
                            } else {
                                $('.zoneblock').html('invalid response');
                            }
                        }
                    }
                }
            }, 'text')
            .fail(function() {
                $('.zoneblock').html('<div style="text-align:center;">failed to fetch page</div>');
            })
            querypending = false;
        }
        var updatelock = false;
        function updatechk() {
            if (!updatelock) {
                $.post('/backend/update.php', {vi: sitev+':'+siteb, token: csrfcookie}, function(datac) {
                    if (datac != '') {
                        if (checkjson(datac) === true) {
                            var jsonres = window.JSON.parse(datac);
                            var jsonresp = jsonres.res;
                            var jsonmsg = jsonres.msg;
                            if (jsonresp == 'ERR') {
                                console.error('Error checking update: '+jsonmsg);
                            } else if (jsonresp == 'VERCHK') {
                                var jsonmode = jsonres.mode;
                                if (jsonmode == 'UPD') {
                                    var jsoncurrentv = jsonres.currentv;
                                    var jsoncurrentb = jsonres.currentb;
                                    var jsonnewv = jsonres.newv;
                                    var jsonnewb = jsonres.newb;
                                    // $('.updatenot').html(jsonmsg+". New build: "+jsonnewv+'_'+jsonnewb+" - Loaded: "+jsoncurrentv+'_'+jsoncurrentb+" - Refresh?");
                                    $('.updatenot').html(jsonmsg);
                                    updatelock = true;
                                } else if (jsonmode != 'HALT') {
                                    $('.updatenot').html('UCBR tampered : Unknown mode');
                                }
                            } else if (jsonres.code == 10) {
                                $('.updatenot').html(jsonres.body);
                            } else if (jsonresp != '') {
								$('.updatenot').html('UCBR tampered : Unknown response type');
							}
                        }
                        //$('.zoneblock').html(datac);
                    }
                }, 'text')
                .fail(function() {
                    console.error('Failed checking for updates -- connection or backend failure');
                    //$('.zoneblock').html('please refresh');
                })
            }
        }
        
        setInterval(function() { updatechk(); }, 1800000);

        $(window).on('popstate', function(event) {
            getquerybit = window.location.pathname;
            getquerybit = getquerybit.replace('/', '');
            if (menutog === true) {
                togglemenu('m_only');
            }
            query(getquerybit, 'bt');
        });
        $(function() {
            $(document).on('click', '.coverarea.coverareaa', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (menutog === true && querypending === false) {
                    togglemenu('m');
                }
            });
            $(document).on('click', '.menutoggle', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (querypending === false) {
                    togglemenu('m');
                }
            });

            var parent, ink, d, x, y;
            var actionlock = false;

            $(document).on('click', '.action', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                parent = $(this);

                if (parent.find('.ink').length == 0)
                $('<span class="ink"></span>').appendTo($(this));
        
                ink = parent.find('.ink');

                ink.removeClass('animate');
    
                if (!ink.height() && !ink.width()) {
                    d = Math.max(parent.outerWidth(), parent.outerHeight());
                    ink.css({height: d, width: d});
                }

                x = e.pageX - parent.offset().left - ink.width()/2;
                y = e.pageY - parent.offset().top - ink.height()/2;
    
                ink.css({top: y+'px', left: x+'px'}).addClass('animate');

                launch = $(this).data('launch');

                if (!actionlock) {
                    actionlock = true;
                    setTimeout(function() {
                        if (menutog === true) {
                            togglemenu('m');
                            setTimeout(function() {
                                    query(launch);
                                    actionlock = false;
                            }, 250);
                        } else {
                                query(launch);
                                actionlock = false;
                        }
                    }, 300);
                }
            });
        });
    }
}
</script>
</body>
</html>
