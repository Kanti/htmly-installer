<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require "vendor/autoload.php";

if (require_once "backer.php")//new Backed?
{
    header('Location: ./', true, 302);
    exit;
}

file_put_contents(dirname($_SERVER["SCRIPT_FILENAME"]) . "/test.php",'
<?php
if (\HTMLy\StaticFunctions::from($_SERVER, \'QUERY_STRING\') == "rewriteRule.html") {
    echo "YES!";
    die();
}
http_response_code(500);
');

session_start();
new HTMLy\Settings;