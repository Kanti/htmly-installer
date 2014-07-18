<?php
function printHeader() {
	echo <<<EOT
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
#error:before {
	content: "Error: ";
	color: rgb(213, 134, 48);
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

function testTheEnvironment() {
	$message = new Message;

	if(!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
		$message->error('HTMLy requires at least PHP 5.3 to run.');
	}
	if(!in_array('https', stream_get_wrappers()))
	{
		$message->error('Installer needs the https wrapper, please install openssl.');
	}
	if(!is__writable("./"))
	{
		$message->error('no permission to write in ' . $file . '.');
	}
	$message->run();
}

class Message {
	protected $errors = array();
	public function error($message)
	{
		$this->errors[] = $message;
	}
	public function run()
	{
		if(! empty($this->errors))
		{;
			@header("HTTP/1.0 500 ".$this->errors[0], true, $code);
			foreach($this->errors as $error)
			{
				echo '<p id="error">' . $error . "</p>";
			}
			die();
		}
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
	
	public function runForm() {
		session_start();
		$this->generateSiteUrl();
		if(from($_REQUEST,'user_name') && from($_REQUEST,'user_password'))
		{
			$this->saveConfigs();
			$this->install();
			$_SESSION[$this->siteUrl]["user"] = $this->user;
		}
		if(isset($_SESSION[$this->siteUrl]["user"]))
		{
			unlink(__FILE__);
			header("Location:" . $this->siteUrl . "add/post");
			exit();
		}
		else{
			echo $this->printForm();
		}
	}
	
	protected function install() {
		$updater = new Updater;
		$updater->update();
	}
}

printHeader();
testTheEnvironment();
$set = new Settings;
$set->runForm();

