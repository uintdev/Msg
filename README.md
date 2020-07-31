# Msg
<img src="banner.png" alt="Banner with logo">

## About

A modern mobile messaging web application.
<br>
Development began back in 2016. It slowed down to a halt after a while.
<br>
This is a public release of the source code behind it. It is unfinished. A lot of the framework is already in place, albeit still needs changes.
<br>
You can try it out at https://msg.uint.dev/. Please note that as live site is worked on directly, it may not accurately reflect what is currently up on this repository.

## Features

- Transitions
- Hamburger menu
- Requests done without reloading. Ever.
- Modern security protection
- Modular (pages, menu options & permissions for them are managed on the database)
- Update notification system

## To do
(In no particular order...)
- Clean up update notification system
- Add mail compose option
- Fix main conversation view
- Add message viewer
- Improve 'about' and 'account' modules
- Work on cryptography for account userdata and messages
- Further implement email activation
- Clean up code and more comments
- Correct splash screen and loading indicator alignment

## Known issues

- On touch screen devices, quickly toggling the menu and closing it by touching the shaded area can result in the menu locking until a navigation option is selected or the page is reloaded
- When selecting the menu toggle button via tab key and spamming the enter key, the previously mentioned issue also occurs

## Prerequisites

For this to function, there are a few requirements.

- PHP 7
- PHP cURL & JSON extensions
- OpenSSL
- Google reCaptcha v2
- Mailgun
  * When email activation is completely implemented

## Configuring

### Web application

Refer to [include/kernel.php](include/kernel.php) for what needs changing. Most of the global constants need to have their values changed and has comments mentioning what is expected. If you have any issues with setting it up, let me know.

### Database

Import the `import.sql` file to deal with the table structure in the database.
- Future updates may involve alternations to the table structure, which would of course mean the SQL file will be updated with those changes.

### Web server

Assuming you are using Nginx, add this to your configuration:

```nginx
location / {
    try_files $uri $uri/ /index.php;
    rewrite ^/(.*)/?$ /index.php?query=$1;
}
location ~* /(backend|css|img) {
    rewrite ^/index\.php?query=(.*)$ /index.php?query=$1;
    rewrite ^/index\.php?query=(.*)/?$ /index.php?query=$1;
}
```
The block to do with using the PHP socket for files with the `.php` file extension **must** be above the configuration mentioned. Otherwise, files in `/backend` will not be interpreted.
