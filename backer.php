<?php

class Backer {

	protected $backedName = "htmly-installer";

	protected $fileList = array();

	public function addFile($fileName) {
		$this->fileList[] = $fileName;
		return $this;
	}
	
	protected function testIfNecessary()  {
		
		if(! file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . "composer.json"))
			return false;
		
		$time = filemtime(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . $this->backedName . ".php");
		
		foreach($this->fileList as $fileName) {
			if($time < filemtime(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . $fileName))
				return true;
		}
		return false;
	}

	public function __construct(){
	
		$this->addFile("vendor/autoload.php");

		$this->addFile("vendor/composer/autoload_real.php")
			->addFile("vendor/composer/autoload_namespaces.php")
			->addFile("vendor/composer/autoload_psr4.php")
			->addFile("vendor/composer/autoload_classmap.php")
			->addFile("vendor/composer/ClassLoader.php");

		$this->addFile("vendor/kanti/hub-updater/HubUpdater.php")
			->addFile("vendor/kanti/hub-updater/HelperClass.php")
			->addFile("vendor/kanti/hub-updater/CacheOneFile.php")
			->addFile("vendor/kanti/hub-updater/ca_bundle.crt");

        $this->addFile("index.php")
            ->addFile("backer.php");

        /*
        $this->addFile("old/src/Form.html.php")
            ->addFile("old/src/Header.html.php")
            ->addFile("old/src/functions.php")
            ->addFile("old/src/htaccess.php")
            ->addFile("old/src/Message.php")
            ->addFile("old/src/Settings.php");
	    */
	}
	
	public function run() {
		if($this->testIfNecessary()){
			$this->back();
			return true;
		}
		return false;
	}
	
	protected function back() {
		$phar = new Phar(
			$this->backedName . ".phar", 
			FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 
			$this->backedName . ".phar");

		foreach($this->fileList as $fileName) {
			$phar[$fileName] = file_get_contents(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . $fileName);
		}
		$phar->setStub($phar->createDefaultStub("index.php"));

		copy($this->backedName . ".phar",$this->backedName . ".php");
		//$phar->stopBuffering();
		unset($phar);
		unlink($this->backedName . ".phar");
	}
}
$backer = (new Backer);
return( $backer->run() );