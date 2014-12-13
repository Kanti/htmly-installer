<?php

require "vendor/autoload.php";

if(require_once "backer.php")//new Backed?
{
	header('Location: ./', true, 302);
	exit;
}

error_reporting(E_ALL);
ini_set("display_errors", 1);

$updater = new Kanti\HubUpdater(array(
    "name" => "danpros/htmly",
    "cache" => "cache/",
    "save" => "save/",
));

var_dump($updater->able());