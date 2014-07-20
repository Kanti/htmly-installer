<?php
//http://de3.php.net/manual/en/function.is-writable.php#73596
function is__writable($path) {
//will work in despite of Windows ACLs bug
//NOTE: use a trailing slash for folders!!!
//see http://bugs.php.net/bug.php?id=27609
//see http://bugs.php.net/bug.php?id=30931

    if ($path{strlen($path)-1}=='/') // recursively return a temporary file path
        return is__writable($path.uniqid(mt_rand()).'.tmp');
    else if (is_dir($path))
        return is__writable($path.'/'.uniqid(mt_rand()).'.tmp');
    // check tmp file for read/write capabilities
    $rm = file_exists($path);
    $f = @fopen($path, 'a');
    if ($f===false)
        return false;
    fclose($f);
    if (!$rm)
        unlink($path);
    return true;
}

function from($source, $name) {
  if (is_array($name)) {
    $data = array();
    foreach ($name as $k)
      $data[$k] = isset($source[$k]) ? $source[$k] : null ;
    return $data;
  }
  return isset($source[$name]) ? $source[$name] : null ;
}


class Message {
	protected $errors = array();
	public function error($message)
	{
		$this->errors[] = $message;
	}
	protected $warnings = array();
	public function warning($message)
	{
		$this->warnings[] = $message;
	}
	public function run()
	{
		$string = "";
		if(! empty($this->errors))
		{
			foreach($this->errors as $error)
			{
				$string .= '<p class="error">' . $error . "</p>";
			}
		}
		if(! empty($this->warnings))
		{
			foreach($this->warnings as $warning)
			{
				$string .= '<p class="warning">' . $warning . "</p>";
			}
		}
		return $string;
	}
}

class CacheOneFile {
	protected $fileName = "";
	protected $holdTime = 43200;//12h
	
	public function __construct($fileName , $holdTime = 43200)
	{
		$this->fileName = $fileName;
		$this->holdTime = $holdTime;
	}

	public function is()
	{
		if(! file_exists($this->fileName))
			return false;
		if(filemtime($this->fileName) < ( time() - $this->holdTime ) )
		{
			unlink($this->fileName);
			return false;
		}
		return true;
	}
	public function get()
	{
		return file_get_contents($this->fileName);
	}
	public function set($content)
	{
		file_put_contents($this->fileName,$content);
	}
}

class Updater {
	protected $cachedInfo = "cache/downloadInfo.json";
	protected $versionFile = "cache/installedVersion.json";
	protected $zipFile = "cache/tmpZipFile.zip";
	
	protected $infos = array();
	
	public function __construct()
	{
		if(! file_exists("cache/"))
		{
			mkdir("cache/");
		}
		$this->cachedInfo = new CacheOneFile($this->cachedInfo);
		$this->infos = $this->getInfos();
	}
	
	protected function getInfos() {
		$path = "https://api.github.com/repos/danpros/htmly/releases";
		if($this->cachedInfo->is())
		{
			$fileContent = $this->cachedInfo->get();
		}
		else
		{
			if(!in_array('https', stream_get_wrappers()))
			{
				return array();
			}
			$fileContent = @file_get_contents($path,false, stream_context_create(
				array(
					'http' => array(
						'header'=>"User-Agent: Awesome-Update-My-Self\r\n"
					)
				)
			));
			if($fileContent === false)
			{
				return array();
			}
			$json = json_decode($fileContent,true);
			$fileContent = json_encode($json, JSON_PRETTY_PRINT);
			$this->cachedInfo->set($fileContent);
			return $json;
		}
		return json_decode($fileContent,true);
	}

	public function updateAble()
	{
		if(!in_array('https', stream_get_wrappers()))
			return false;
		if(empty($this->infos))
			return false;

		if(file_exists($this->versionFile))
		{
			$fileContent = file_get_contents($this->versionFile);
			$current = json_decode($fileContent,true);
		
			if(isset($current['id']) && $current['id'] == $this->infos[0]['id'])
				return false;
			if(isset($current['tag_name']) && $current['tag_name'] == $this->infos[0]['tag_name'])
				return false;
		}
		return true;
	}
	
	public function update()
	{
		if($this->updateAble())
		{
			if($this->download("https://github.com/danpros/htmly/archive/" . $this->infos[0]['tag_name'] . ".zip"))
			{
				if($this->unZip())
				{
					unlink($this->zipFile);
					file_put_contents($this->versionFile, json_encode(array(
						"id" => $this->infos[0]['id'],
						"tag_name" => $this->infos[0]['tag_name']
					), JSON_PRETTY_PRINT));
					return true;
				}
			}
		}
		return false;
	}	
	protected function download($url)
	{
		$file = @fopen($url, 'r');
		if($file == false)
			return false;
		file_put_contents($this->zipFile, $file);
		return true;
	}
	protected function unZip()
	{
		$path = dirname($_SERVER['SCRIPT_FILENAME']) . "/" . $this->zipFile;
		
		$zip = new ZipArchive;
		if ($zip->open($path) === true) {
			$cutLength = strlen($zip->getNameIndex(0));
			for($i = 1; $i < $zip->numFiles; $i++) {//iterate throw the Zip
				$fileName = $zip->getNameIndex($i);
				$stat = $zip->statIndex($i);
				if($stat["crc"] == 0)
				{
					$dirName = substr($fileName,$cutLength);
					if(! file_exists($dirName))
					{
						mkdir($dirName);
					}
				}
				else{
					copy("zip://".$path."#".$fileName, substr($fileName,$cutLength));
				}
			}                   
			$zip->close();
			return true;
		}
		else{
			return false;
		}
	}
	
	public function printOne()
	{
		$releases = $this->infos;
		$string = "<h3>Updated to<h3>";
		$string .= "<h2>[" . $releases[0]['tag_name'] . "] " . $releases[0]['name'] . "</h2>\n";
		$string .= "<p>" . $releases[0]['body'] . "</p>\n";
		return $string;
	}
	
	public function getName()
	{
		return $this->infos[0]['tag_name'];
	}
}

class Settings {

	protected $user = "";
	protected $userPassword = "";
	
	protected $siteUrl = "";

	protected function printHeader() { //EOT
return <<<EOT
<!DOCTYPE html>
<html>
<head>
<style>
* {
	margin: 0;
	padding: 0;
}
*, *:before, *:after {
  -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box;
 }
.responsive {
	min-width: 512px;
	width: 100%;
	max-width: 730px;
}
section, footer, header, aside, nav {
	display: block;
}
.error:before {
	content: "Error: ";
	color: red;
	font-weight: bold;
	font-size: 120%;
}

.warning:before {
	content: "Warning: ";
	color: yellow;
	font-weight: bold;
	font-size: 120%;
}
body {
	font-size: 17px;
	line-height: 1.6em;
	font-family: Georgia, sans-serif;
	background: #F7F7F7;
	color: #444444;
}
#cover {
	padding: 0 0 20px 0;
	float: left;
	width: 100%;
}
#header-wrapper {
	float: left;
	width: 100%;
	position: relative;
}
#header {
	position: relative;
	padding: 0 15px;
	margin: 0 auto;
}
#branding {
	text-align: left;
	position: relative;
	width: 100%;
	float: left;
	margin: 1em 0;
	text-shadow: 0 1px 0 #ffffff;
}
#branding h1, #branding h2 {
	font-size: 36px;
	font-family: Georgia,sans-serif;
	margin: 0;
}
#branding p {
	margin: 0;
	font-style: italic;
	margin-top: 5px;
}
#main-wrapper {
	float: left;
	width: 100%;
	background: #ffffff;
	position: relative;
	border-top: 1px solid #DFDFDF;
	border-bottom: 1px solid #DFDFDF;
}
#main {
	position: relative;
	padding: 0;
	margin: 0 auto;
	background: #ffffff;
	overflow: hidden;
	padding: 30px 15px;
}
label{
	width: 100%;
	max-width: 180px;
	float:left;
}
input:not([type=submit]), select{
	float:left;
	width: 100%;
	max-width: 520px;
	font-size: 80%;
}
input{
	padding: 2px;
}
input[type=submit]{
	margin-top: 10px;
	padding: 5px;
	width: 100%;
}
</style>
<link rel="icon" href="data:image/png;base64,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" type="image/ico" />
<title>HTMLy Installer</title>
</head>
<body>
<div id="cover">
	<div id="header-wrapper">
		<header id="header" class="responsive">
			<div id="branding">
				<h1>
					HTMLy
				</h1>
				<div id="blog-tagline">
					<p>the HTMLy Installer Tool</p>
				</div>
			</div>
		</header>
	</div>
</div>
<div id="main-wrapper">
	<div id="main" class="responsive">
EOT;
}
	protected function printForm() { //EOT
return <<<EOT
<html>
	<form method="POST">
		<label for="user_name">Username:</label>
		<input name="user_name" value="" placeholder="Your User Name">
		<br/>
		<label for="user_password">Password:</label>
		<input name="user_password" value="" type="password" placeholder="Password">
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
		<input name="social_tumblr" type="url" value="" placeholder="http://www.tumblr.com">
		<br/>
		<br/>
		<label for="comment_system">Comment System:</label>
		<select name="comment_system" onchange="checkIfOther();" id="comment.system">
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
function checkIfOther(){
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
</html>
EOT;
}
	protected function standardConfig() { //EOT
return <<<EOT
; The URL of your blog. Include the http or https.
site.url = ""

; Blog info
blog.title = "HTMLy"
blog.tagline = "Just another HTMLy blog"
blog.description = "Proudly powered by HTMLy, a databaseless blogging platform."
blog.copyright = "(c) Your name."

; Social account
social.twitter = "https://twitter.com"
social.facebook = "https://www.facebook.com"
social.google = "https://plus.google.com"
social.tumblr = "http://www.tumblr.com"

; Custom menu link.
; See example below:
; "Google->http://www.google.com|Wikipedia->http://www.wikipedia.org". 
blog.menu = ""

; Breadcrumb home text. Useful when installed on subfolder.
breadcrumb.home = "Home"

; Comment system. Choose "facebook", "disqus", or "disable".
comment.system = "disable"

;Facebook comments
fb.appid = ""
fb.num = "5"
fb.color = "light"

; Disqus comments
disqus.shortname = ""

; Google+ publisher
google.publisher = ""

; Google analytics
google.analytics.id = ""

; Pagination, RSS, and JSON
posts.perpage = "5"
tag.perpage = "10"
archive.perpage = "10"
search.perpage = "10"
profile.perpage = "10"
json.count = "10"

; Related posts
related.count = "4"

; Author info on blog post. Set "true" or "false".
author.info = "true"

; Teaser type: set "trimmed" or "full".
teaser.type = "trimmed"

; Teaser char count
teaser.char = "200"

; Description char count
description.char = "150"

;RSS feed count
rss.count = "10"

;RSS feed description length. If left empty we will use full page.
rss.char = ""

; Enable image thumbnail on teaser, the options is "true" and "false". If set to "true", you can specify the default thumbnail also.
img.thumbnail = "true"
default.thumbnail = ""

;Enable or disable jQuery, if Lightbox is "on" then this option ignored.
jquery = "disable"

; Lightbox inline image handling. This can slowdown your page load, especially when Disqus enabled. "on" or "off".
lightbox = "off"

; Set the theme here
views.root = "themes/logs"

; Framework config. No need to edit.
views.layout = "layout"
EOT;
	}
	protected function standardUser() { //EOT
return <<<EOT
;Password
password = yourpassword

;Role
role = admin
EOT;
	}
	
	protected function extractUser() {
		$this->user = (string)$_REQUEST["user_name"];
		unset($_REQUEST["user_name"]);
		$this->userPassword = (string)$_REQUEST["user_password"];
		unset($_REQUEST["user_password"]);
	}
	protected function convertRequestToConfig() {
		$array = array();
		foreach($_REQUEST as $name => $value)
		{
			if(! is_string($value) ||empty($value))
				continue;
			$name = str_replace("_",".",$name);
			$array[$name] = $value;
		}
		return $array;
	}
	
	protected function generateINI($data , $seperator=null) {
		$lb = "\n";//linebreak
		$string = ";INI File".$lb;
		foreach($data as $name => $value)
		{
			$string .= $name . ' = ' . $seperator . $value . $seperator .$lb;
		}
		return $string;
	}	
	protected function generateSiteUrl() {
		$method = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
			$method = 'https';
		}
		$dir = dirname(substr($_SERVER["SCRIPT_FILENAME"], strlen($_SERVER["DOCUMENT_ROOT"]) ));
		if($dir == '.' || $dir == '..')
		{
			$dir = '';
		}
		$this->siteUrl = $method . '://' . $_SERVER['SERVER_NAME'] . $dir . '/';		
	}
	
	protected function saveConfigs() {
		$standardConfig = parse_ini_string($this->standardConfig());
		$this->extractUser();
		$config = array("site.url" => $this->siteUrl);
		$config += $this->convertRequestToConfig();
		$config += $standardConfig;
		$dir = "config/";
		if(! file_exists($dir))
			mkdir($dir);
		file_put_contents($dir . "config.ini", $this->generateINI($config,'"'));
		$userDir = $dir."users/";
		if(! file_exists($userDir))
			mkdir($userDir);
		file_put_contents($userDir . $this->user . ".ini", $this->generateINI(array(
			'password' => $this->userPassword,
			'role' => 'admin',
		)));
	}
	
	protected function testTheEnvironment() {
		$message = new Message;

		if(!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
			$message->error('HTMLy requires at least PHP 5.3 to run.');
		}
		if(!in_array('https', stream_get_wrappers()))
		{
			$message->error('Installer needs the https wrapper, please install openssl.');
		}
		if(!in_array('mod_rewrite', apache_get_modules()))
		{
			$message->warning('mod_rewrite must be enabled if you use Apache.');
		}
		if(!is__writable("./"))
		{
			$message->error('no permission to write in the Directory.');
		}
		return $message->run();
	}

	public function __construct() {
		$this->generateSiteUrl();
		
		$message = $this->testTheEnvironment();
		if(! empty($message)) {
			echo $this->printHeader();
			echo $message;
			echo "</body>";
			echo "</html>";
		}
		elseif($this->runForm()) {
			unlink(__FILE__);
			header("Location:" . $this->siteUrl . "add/post");
			exit();
		}
		else {
			echo $this->printHeader();
			echo $this->printForm();
			echo "</body>";
			echo "</html>";
		}
	}
	
	protected function runForm() {
		if(from($_REQUEST,'user_name') && from($_REQUEST,'user_password'))
		{
			$this->saveConfigs();
			$this->install();
			$_SESSION[$this->siteUrl]["user"] = $this->user;
			return true;
		}
		else{
			unset($_SESSION[$this->siteUrl]["user"]);
			return false;
		}
	}
	
	protected function install() {
		$updater = new Updater;
		$updater->update();
	}
}

session_start();

new Settings;
