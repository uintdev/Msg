<?php
# Menu - the page lister

# GET KERNEL
define('INIT_KERN', true); // verify kernel execution source
if (@!include('../include/kernel.php')) exit('unable to communicate with commander');


session_start(); // get session cookie ready for external verification

if (!sourceverifier()) exiti('access source denied');


$menulogger = $con->query("SELECT `id`, `entry_order`, `name`, `path`, `loggedin` FROM `menu` ORDER BY `entry_order`"); // get menu
$menu_order = 0; // entry order for menu entry

while ($menulogresult = $menulogger->fetch_assoc()) {
    $menu_loggedin = (int)$menulogresult['loggedin']; // login data from menu entry

    if ($menu_loggedin === 2 || $userexists === $menu_loggedin) {
        // if menu entry is accessible globally or if login data matches
        $menu_name = $menulogresult['name']; // name of menu entry
        $menu_path = $menulogresult['path']; // path of menu entry

        # MENU VAR CONFIG
        $menu_url_vars_in = [
            '{AUTH_TOKEN}'
        ];
        $menu_url_vars_out = [
            $authtoken ?? ''
        ];

        $menu_path_rp = str_replace($menu_url_vars_in, $menu_url_vars_out, $menu_path); // replace string var with actual data
        ++$menu_order; // count up order ID for each outputted entry

        $results[$menu_order] = [
            'name' => $menu_name,
            'path' => $menu_path_rp
        ];
    }
}

$jsonify = json_encode($results);
header('Content-Type: application/json; charset=utf-8'); // set content type header for json
echo $jsonify;

$menulogger->free();
