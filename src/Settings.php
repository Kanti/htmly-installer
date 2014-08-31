<?php

class Settings {

    protected $user = "";
    protected $userPassword = "";
    protected $siteUrl = "";
    
    protected $overwriteEmptyForm = array(
        "social.twitter" => "",
        "social.facebook" => "",
        "social.google" => "",
        "social.tumblr" => "",
    );

    protected function extractUser() {
        $this->user = (string) $_REQUEST["user_name"];
        unset($_REQUEST["user_name"]);
        $this->userPassword = (string) $_REQUEST["user_password"];
        unset($_REQUEST["user_password"]);
    }

    protected function convertRequestToConfig() {
        $array = array();
        foreach ($_REQUEST as $name => $value) {
            if (!is_string($value) || empty($value))
                continue;
            $name = str_replace("_", ".", $name);
            $array[$name] = $value;
        }
        foreach($this->overwriteEmptyForm as $name => $value)
        {
            if(!isset($array[$name]))
            {
                $array[$name] = $value;
            }
        }
        return $array;
    }

    protected function generateSiteUrl() {
        $dir = dirname(substr($_SERVER["SCRIPT_FILENAME"], strlen($_SERVER["DOCUMENT_ROOT"])));
        if ($dir == '.' || $dir == '..') {
            $dir = '';
        }
        $this->siteUrl = '//' . rtrim($_SERVER['SERVER_NAME'],"/") . "/" . trim($dir,"/") . '/';
    }

    protected function overwriteINI($data, $string) {
        foreach ($data as $word => $value) {
            $string = preg_replace("/^" . $word . " = .+$/m", $word . ' = "' . $value . '"', $string);
        }
        return $string;
    }

    protected function saveConfigs() {
        $this->extractUser();
        //save config.ini
        $config = array("site.url" => $this->siteUrl);
        $config += $this->convertRequestToConfig();
        $configFile = file_get_contents("config/config.ini.example");
        $configFile = $this->overwriteINI($config, $configFile);
        file_put_contents("config/config.ini", $configFile);
        
        //save users/[Username].ini
        $userFile = file_get_contents("config/users/username.ini.example");
        $parsed = parse_ini_string($userFile);
        if(isset($parsed['encryption']))
        {
            $userFile = $this->overwriteINI(array(
                'encryption' => 'sha512',
                'password' => hash('sha512', $this->userPassword),
                'role' => 'admin',
            ), $userFile);
        }
        else
        {
            $userFile = $this->overwriteINI(array(
                "password" => $this->userPassword,
                'role' => 'admin',
            ), $userFile);
        }
        file_put_contents("config/users/" . $this->user . ".ini", $userFile);
    }

    protected function testTheEnvironment() {
        $message = new Message;

        if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
            $message->error('HTMLy requires at least PHP 5.3 to run.');
        }
        if (!in_array('https', stream_get_wrappers())) {
            $message->error('Installer needs the https wrapper, please install openssl.');
        }
        if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())) {
            $message->warning('mod_rewrite must be enabled if you use Apache.');
        }
        if (!is__writable("./")) {
            $message->error('no permission to write in the Directory.');
        }
        return $message->run();
    }

    public function __construct() {
        $message = $this->testTheEnvironment();
        if(! file_exists(".htaccess"))
        {
            file_put_contents(".htaccess", htaccess());
        }
        
        $this->generateSiteUrl();
        if (!empty($message)) {
            printHeader();
            echo $message;
            echo "</body>";
            echo "</html>";
        } elseif ($this->runForm()) {
            unlink(__FILE__);
            header("Location:" . $this->siteUrl . "add/post");
            exit();
        } else {
            $updater = new Updater;
            $version = $updater->getInfos();
            printHeader($version);
            printForm();
            echo "</body>";
            echo "</html>";
        }
    }

    protected function runForm() {
        if (from($_REQUEST, 'user_name') && from($_REQUEST, 'user_password')) {
            $this->install();
            $this->saveConfigs();
            $_SESSION[$this->siteUrl]["user"] = $this->user;
            return true;
        } else {
            unset($_SESSION[$this->siteUrl]["user"]);
            return false;
        }
    }

    protected function install() {
        $updater = new Updater;
        $updater->update();
    }
}
?>