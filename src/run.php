<?php
if(from($_SERVER,'QUERY_STRING') == "rewriteRule.html")
{
    echo "YES!";
    die();
}
session_start();
new Settings;