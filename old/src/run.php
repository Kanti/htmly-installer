<?php

require "phar://htmly-installer.phar/init.php";
require "phar://htmly-installer.phar/functions.php";
require "phar://htmly-installer.phar/Header.html.php";
require "phar://htmly-installer.phar/Form.html.php";
require "phar://htmly-installer.phar/htaccess.php";
require "phar://htmly-installer.phar/Message.php";
require "phar://htmly-installer.phar/updater.php";
require "phar://htmly-installer.phar/Settings.php";

if(from($_SERVER,'QUERY_STRING') == "rewriteRule.html")
{
    echo "YES!";
    die();
}
session_start();
new Settings;