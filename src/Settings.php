<?php
namespace HTMLy;

use Kanti\HelperClass;
use Kanti\HubUpdater;

class Settings
{

    protected $user = "";
    protected $userPassword = "";
    protected $siteUrl = "";

    protected $overwriteEmptyForm = array(
        "social.twitter" => "",
        "social.facebook" => "",
        "social.google" => "",
        "social.tumblr" => "",
    );

    protected $hubUpdaterSettings = array(
        "name" => "danpros/htmly",
        "cache" => "cache/",
        "save" => "",
    );

    protected function extractUser()
    {
        $this->user = (string)$_REQUEST["user_name"];
        unset($_REQUEST["user_name"]);
        $this->userPassword = (string)$_REQUEST["user_password"];
        unset($_REQUEST["user_password"]);
    }

    protected function convertRequestToConfig()
    {
        $array = array();
        foreach ($_REQUEST as $name => $value) {
            if (!is_string($value) || empty($value)) {
                continue;
            }
            $name = str_replace("_", ".", $name);
            $array[$name] = $value;
        }
        foreach ($this->overwriteEmptyForm as $name => $value) {
            if (!isset($array[$name])) {
                $array[$name] = $value;
            }
        }
        return $array;
    }

    protected function generateSiteUrl()
    {
        $dir = dirname(substr($_SERVER["SCRIPT_FILENAME"], strlen($_SERVER["DOCUMENT_ROOT"])));
        if ($dir == '.' || $dir == '..') {
            $dir = '';
        }
        $port = '';
        if ($_SERVER["SERVER_PORT"] != "80") {
            $port = ':' . $_SERVER["SERVER_PORT"];
        }
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
        if ($dir === '') {
            $this->siteUrl = $scheme . '://' . trim($_SERVER['SERVER_NAME'], "/") . $port . "/";
            return;
        }
        $this->siteUrl = $scheme . '://' . trim($_SERVER['SERVER_NAME'], "/") . $port . "/" . $dir . '/';
    }

    protected function overwriteINI($data, $string)
    {
        foreach ($data as $word => $value) {
            $string = preg_replace("/^" . $word . " = .+$/m", $word . ' = "' . $value . '"', $string);
        }
        return $string;
    }

    protected function saveConfigs()
    {
        $this->extractUser();
        //save config.ini
        $config = array(
            "site.url" => $this->siteUrl,
            "timezone" => $this->getTimeZone(),
        );
        $config += $this->convertRequestToConfig();
        $configFile = file_get_contents(dirname($_SERVER['SCRIPT_FILENAME']) . "/" . "config/config.ini.example");
        $configFile = $this->overwriteINI($config, $configFile);
        file_put_contents(dirname($_SERVER['SCRIPT_FILENAME']) . "/" . "config/config.ini", $configFile);

        //save users/[Username].ini
        $userFile = file_get_contents(dirname($_SERVER['SCRIPT_FILENAME']) . "/" . "config/users/username.ini.example");
        $parsed = parse_ini_string($userFile);
        if (isset($parsed['encryption'])) {
            $userFile = $this->overwriteINI(array(
                'encryption' => 'sha512',
                'password' => hash('sha512', $this->userPassword),
                'role' => 'admin',
            ), $userFile);
        } else {
            $userFile = $this->overwriteINI(array(
                "password" => $this->userPassword,
                'role' => 'admin',
            ), $userFile);
        }
        file_put_contents(
            dirname($_SERVER['SCRIPT_FILENAME']) . "/" . "config/users/" . $this->user . ".ini",
            $userFile
        );
    }

    protected function testTheEnvironment()
    {
        $messages = new Messages;

        if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
            $messages->error('HTMLy requires at least PHP 5.3 to run.');
        }
        if (!in_array('https', stream_get_wrappers())) {
            $messages->error('Installer needs the https wrapper, please install openssl.');
        }
        if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())) {
            $messages->warning('mod_rewrite must be enabled if you use Apache.');
        }
        if (!StaticFunctions::isWritable("./")) {
            $messages->error('no permission to write in the Directory.');
        }
        return $messages->run();
    }

    public function __construct()
    {
        $messages = $this->testTheEnvironment();
        if (StaticFunctions::isWritable("./") && !HelperClass::fileExists(".htaccess")) {
            file_put_contents(dirname($_SERVER["SCRIPT_FILENAME"]) . "/.htaccess", GetHtaccess::htaccess());
        }

        $this->generateSiteUrl();
        if (!empty($messages)) {
            HeaderTemplate::printHeader();
            echo $messages;
            echo "</body>";
            echo "</html>";
        } elseif ($this->runForm()) {
            unlink($_SERVER["SCRIPT_FILENAME"]);
            header("Location:" . $this->siteUrl . "add/post");
            exit();
        } else {
            $updater = new HubUpdater($this->hubUpdaterSettings);
            $version = $updater->getNewestInfo();
            HeaderTemplate::printHeader($version);
            FormTemplate::printForm();
            echo "</body>";
            echo "</html>";
        }
    }

    protected function getTimeZone()
    {
        static $ip;
        if (empty($ip)) {
            $ip = @file_get_contents("http://ipecho.net/plain");
            if (!is_string($ip)) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }
        $json = @json_decode(@file_get_contents("http://ip-api.com/json/" . $ip), true);
        if (isset($json['timezone'])) {
            return $json['timezone'];
        }
        return 'Europe/Berlin';
    }

    protected function runForm()
    {
        if (StaticFunctions::from($_REQUEST, 'user_name') && StaticFunctions::from($_REQUEST, 'user_password')) {
            $this->install();
            $this->saveConfigs();
            $_SESSION[$this->siteUrl]["user"] = $this->user;
            return true;
        } else {
            unset($_SESSION[$this->siteUrl]["user"]);
            return false;
        }
    }

    protected function install()
    {
        if (HelperClass::fileExists('test.php')) {
            unlink(dirname($_SERVER["SCRIPT_FILENAME"]) . "/test.php");
        }
        $updater = new HubUpdater($this->hubUpdaterSettings);
        $updater->update();
    }
}
