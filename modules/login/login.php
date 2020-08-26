<?php
# Login ( / register) module - the page displayed when not logged in

if (!isset($con)) {
        # GET KERNEL
        if (@!include('../../include/kernel.php')) exit('unable to communicate with commander');
}

if (!isset($verifier)) exiti('bad access'); // check not accessed via queryparser

?>
<link rel="stylesheet" type="text/css" property="stylesheet" href="/css/login.css">
<br>
<br>
<br>
<span class="login-logo"></span>
<br>
<br>
<div class="alert"></div>
<br>
<form class="magicform" method="post">
<label for="email">email</label>
<br>
<input type="email" id="email" minlength="8" maxlength="40" class="email" name="email">
<br>
<br>
<label for="password">password</label>
<br>
<input type="password" minlength="10" maxlength="160" id="password" class="password" name="password">
<input type="hidden" name="token" value="<?php echo $authtoken ?>">
<br>
<div id="regblock">
<br>
<label for="passworda">password again</label>
<br>
<input type="password" minlength="10" maxlength="160" id="passworda" class="passworda" name="passworda" disabled>
<br>
<br>
<label for="username">username</label>
<br>
<input type="text" minlength="4" maxlength="20" id="username" class="username" name="username" disabled>
<br>
<br>
<div class="g-recaptcha" data-sitekey="<?php echo API_GREC_SITE; ?>" data-size="invisible"></div>
<br>
<input type="checkbox" name="tos" id="tos" autocomplete="off"> <label for="tos">have you read and agree to the <a href="/tos" target="_blank">terms of service?</a></label>
</div>
<br>
<br>
<br>
<input type="submit" id="login_sub" value="login">
</form>
<br>
<a class="formtog" href="javascript:void(0)">register</a>
<br>
<br>
forgot your password?
<br>
<br>
who are we?
<br>
<br>
<script src="https://www.google.com/recaptcha/api.js" nonce="<?php echo CSP_JS_ID; ?>" async defer></script>
<script type="text/javascript" nonce="<?php echo CSP_JS_ID; ?>">
$(window).on('load', function() {
    $('body').removeClass('preload');
});

window.formmode = 0;
togglefirst = false;
window.submissionmsg = 'logging in...';

$(function() {
    $(document).on('click', '.formtog', function(et) {

        et.preventDefault();
        et.stopImmediatePropagation();

        if (window.formmode === 0 && togglefirst === false) {
            window.submissionmsg = 'registering...';
            $('#login_sub').val('register');
            $('.formtog').html('login');
            $('#passworda').prop('disabled', false);
            $('#username').prop('disabled', false);
            $('#regblock').css({'display':'block'});
            $('.container').stop(false,true).fadeOut(200).animate({
                    'left': '-75%'
                }, {duration: 500, queue: false}, 'easing', function() {
                // do something once done
            });
            $('#passworda').val('');
            $('#username').val('');
            window.formmode = 1;
        } else {
            // login
            window.submissionmsg = 'logging in...';
            $('#login_sub').val('login');
            $('.formtog').html('register');
            $('#passworda').prop('disabled', true);
            $('#username').prop('disabled', true);
            $('#regblock').css({'display':'none'});
            window.formmode = 0;
            if (togglefirst === true) {
                togglefirst = false;
            }
        }
    });
    $(document).on('submit', '.magicform', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        email = document.getElementById('email').value;
        etrimmed = $.trim(email);
        password = document.getElementById('password').value;
        ptrimmed = $.trim(password);

        if (window.formmode == 1) {
            passworda = document.getElementById('passworda').value;
            patrimmed = $.trim(passworda);
            dischkp = $('#passworda').is('[disabled]');
            username = document.getElementById('username').value;
            utrimmed = $.trim(username);
            grecaptcha.execute();
            grecap = document.getElementById('g-recaptcha-response').value;
            dischk = $('#username').is('[disabled]');
        }

        $('.alert').css({'display':'block'});
        if (etrimmed == '' || ptrimmed == '' || window.formmode == 1 && utrimmed == '' && !dischk || window.formmode == 1 && patrimmed == '' && !dischkp) {
            $('.alert').html('please fill in all of the fields');
        }
        else if (window.formmode == 1 && grecap == '') {
            $('.alert').html('recaptcha not complete');
        }
        else if ($("#tos").prop('checked') === false && window.formmode == 1) {
            $('.alert').html('you must accept the terms of service');
        } else {
            $('.alert').html(window.submissionmsg);
            $('#email').prop('readonly', true);
            $('#password').prop('readonly', true);
            if (window.formmode == 1) {
                $('#passworda').prop('readonly', true);
                $('#username').prop('readonly', true);
            }
            $('#login_sub').prop('disabled', true);

            setTimeout(function() {
                $.post('/backend/mfls.php', $('.magicform').serialize(), function (data, formdata) {
                if(data != '') {
                    $('.alert').html(data);
                    if(data.substring(0, 16) == '...welcome back ' || data == '...and you\'re in!') {
                        setTimeout(function() {
                            reloadall();
                        }, 500);
                    } else {
                        $('#email').prop('readonly', false);
                        $('#password').prop('readonly', false);
                        if (window.formmode == 1) {
                            $('#passworda').prop('readonly', false);
                            $('#username').prop('readonly', false);
                        }
                        $('#login_sub').prop('disabled', false);
                        $('#password').val('');
                        $('#passworda').val('');
                    }
                }
            })
            .fail(function () {
                $('.alert').html('unable to contact the server');
                $('#email').prop('readonly', false);
                $('#password').prop('readonly', false);
                $('#password').val('');
                if (window.formmode == 1) {
                    $('#passworda').prop('readonly', false);
                    $('#passworda').val('');
                    $('#username').prop('readonly', false);
                }
                $('#login_sub').prop('disabled', false);
            });
            }, 300);
        }
    return false;
    });
});
</script>
