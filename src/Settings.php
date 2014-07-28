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

    protected function printForm() { //EOT
        return <<<EOT
<form method="POST">
        <label for="user_name">Username:<span class="required">*</span></label>
        <input name="user_name" value="" placeholder="Your User Name" required>
        <br/>
        <label for="user_password">Password:<span class="required">*</span></label>
        <input name="user_password" value="" type="password" placeholder="Password" required>
        <br/>
        <br/>
        <label for="blog_title">Blog Title:</label>
        <input name="blog_title" value="" placeholder="HTMLy">
        <br/>
        <label for="blog_tagline">Blog Tagline:</label>
        <input name="blog_tagline" value="" placeholder="Just another HTMLy blog">
        <br/>
        <label for="blog_description">Blog Description:</label>
        <input name="blog_description" value="" placeholder="Proudly powered by HTMLy, a databaseless blogging platform.">
        <br/>
        <label for="blog_copyright">Blog Copyright:</label>
        <input name="blog_copyright" value="" placeholder="(c) Your name.">
        <br/>
        <br/>
        <label for="social_twitter">Twitter Link:</label>
        <input name="social_twitter" type="url" value="" placeholder="https://twitter.com">
        <br/>
        <label for="social_facebook">Facebook Link:</label>
        <input name="social_facebook" type="url" value="" placeholder="https://www.facebook.com">
        <br/>
        <label for="social_google">Google+ Link:</label>
        <input name="social_google" type="url" value="" placeholder="https://plus.google.com">
        <br/>
        <label for="social_tumblr">Tumblr Link:</label>
        <input name="social_tumblr" type="url" value="" placeholder="https://www.tumblr.com">
        <br/>
        <br/>
        <label for="comment_system">Comment System:</label>
        <select name="comment_system" onchange="checkCommentSystemSelection();" id="comment.system">
           <option value="disable">disable</option>
           <option value="facebook">facebook</option>
           <option value="disqus">disqus</option>
</select>
        <div id="facebook" style="display:none">
                <br/>
                <label for="fb_appid">Facebook AppId:</label>
                <input name="fb_appid" value="" placeholder="facebook AppId">
        </div>
        <div id="disqus" style="display:none">
                <br/>
                <label for="disqus_shortname">Disqus Shortname:</label>
                <input name="disqus_shortname" value="" placeholder="disqus shortname">
        </div>
        <br/><input type="submit" value="Install via Tool">
</form>
<script>
function checkCommentSystemSelection(){
    a = document.getElementById("comment.system");
    if(a.value == "facebook")
            document.getElementById("facebook").setAttribute("style","display:inline");
    else
            document.getElementById("facebook").setAttribute("style","display:none");
    if(a.value == "disqus")
            document.getElementById("disqus").setAttribute("style","display:inline");
    else
            document.getElementById("disqus").setAttribute("style","display:none");
    return a.value;
}
</script>
EOT;
    }

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
        $method = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
            $method = 'https';
        }
        $dir = dirname(substr($_SERVER["SCRIPT_FILENAME"], strlen($_SERVER["DOCUMENT_ROOT"])));
        if ($dir == '.' || $dir == '..') {
            $dir = '';
        }
        $this->siteUrl = $method . '://' . $_SERVER['SERVER_NAME'] . $dir . '/';
    }

    protected function overwriteINI($data, $string) {
        foreach ($data as $word => $value) {
            $string = preg_replace("/^" . $word . " = .+$/m", $word . ' = "' . $value . '"', $string);
        }
        return $string;
    }

    protected function saveConfigs() {
        $this->extractUser();
        $config = array("site.url" => $this->siteUrl);
        $config += $this->convertRequestToConfig();
        $configFile = file_get_contents("config/config.ini.example");
        $configFile = $this->overwriteINI($config, $configFile);
        file_put_contents("config/config.ini", $configFile);
        
        $userFile = file_get_contents("config/users/username.ini.example");
        $userFile = $this->overwriteINI(array(
            "password" => $this->userPassword,
            'role' => 'admin',
        ), $userFile);
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
        $this->generateSiteUrl();

        $message = $this->testTheEnvironment();
        if (!empty($message)) {
            echo printHeader();
            echo $message;
            echo "</body>";
            echo "</html>";
        } elseif ($this->runForm()) {
            unlink(__FILE__);
            header("Location:" . $this->siteUrl . "add/post");
            exit();
        } else {
            echo printHeader();
            echo $this->printForm();
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