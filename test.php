
<?php
if (\HTMLy\StaticFunctions::from($_SERVER, 'QUERY_STRING') == "rewriteRule.html") {
    echo "YES!";
    die();
}
http_response_code(500);
