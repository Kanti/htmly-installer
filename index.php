<?php

require "vendor/autoload.php";

if(require_once "backer.php")//new Backed?
{
	header('Location: ./', true, 302);
	exit;
}

$updater = new Kanti\HubUpdater(array(
        "cacheFile" => "downloadInfo.json",
        "versionFile" => "installedVersion.json",
        "zipFile" => "tmpZipFile.zip",
        "updateignore" => ".updateignore",

        "name" => "kanti/test",
        "branch" => "master",
        "cache" => "cache/",
        "save" => "save/",
        "prerelease" => false,
		
		"internetException" => false,
    ));
	
	
var_dump($updater->able());
var_dump($updater);
