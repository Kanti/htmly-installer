<?php

class Backer
{

    protected $backedName = "htmly-installer";
    protected $tmpDir = "/var/www/";

    protected $fileList = array();

    public function addFileToPhar($fileName)
    {
        $this->fileList[] = $fileName;
        return $this;
    }

    public function addDirToPhar($dirName)
    {
        $dirName = rtrim($dirName, "/") . "/";
        foreach (scandir($dirName) as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dirName . $value)) {
                    $this->addDirToPhar($dirName . $value);
                } else {
                    $this->addFileToPhar($dirName . $value);
                }
            }
        }
        return $this;
    }

    protected function testIfBackingIsNecessary()
    {
        if (!file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . "composer.json")) {
            return false;
        }

        if (!file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . $this->backedName . ".php")) {
            return true;
        }
        $time = filemtime(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . $this->backedName . ".php");

        foreach ($this->fileList as $fileName) {
            if ($time < filemtime(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . $fileName)) {
                return true;
            }
        }
        return false;
    }

    public function __construct()
    {
        $this->addDirToPhar("vendor/");
        $this->addDirToPhar("src/");

        $this->addFileToPhar("index.php")
            ->addFileToPhar("backer.php");

        /*
        $this->addFile("old/src/Form.html.php")
            ->addFile("old/src/Header.html.php")
            ->addFile("old/src/functions.php")
            ->addFile("old/src/htaccess.php")
            ->addFile("old/src/Message.php")
            ->addFile("old/src/Settings.php");
	    */
    }

    public function run()
    {
        if ($this->testIfBackingIsNecessary()) {
            $this->back();
            return true;
        }
        return false;
    }

    protected function back()
    {
        $phar = new Phar(
            $this->tmpDir . $this->backedName . ".phar",
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
            $this->backedName . ".phar");

        foreach ($this->fileList as $fileName) {
            $phar[$fileName] = file_get_contents(dirname($_SERVER["SCRIPT_FILENAME"]) . "/" . $fileName);
        }
        $phar->setStub($phar->createDefaultStub("index.php"));

        copy($this->tmpDir . $this->backedName . ".phar", $this->backedName . ".php");
        unset($phar);
        unlink($this->tmpDir . $this->backedName . ".phar");
    }
}

$backer = (new Backer);
return ($backer->run());