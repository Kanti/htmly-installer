<?php
namespace HTMLy;

class StaticFunctions
{
    //http://de3.php.net/manual/en/function.is-writable.php#73596
    public static function isWritable($path)
    {
        //will work in despite of Windows ACLs bug
        //NOTE: use a trailing slash for folders!!!
        //see http://bugs.php.net/bug.php?id=27609
        //see http://bugs.php.net/bug.php?id=30931

        if ($path{strlen($path) - 1} == '/') { // recursively return a temporary file path
            return StaticFunctions::isWritable($path . uniqid(mt_rand()) . '.tmp');
        } else {
            if (is_dir($path)) {
                return StaticFunctions::isWritable($path . '/' . uniqid(mt_rand()) . '.tmp');
            }
        }
        // check tmp file for read/write capabilities
        $rm = file_exists($path);
        $f = @fopen($path, 'a');
        if ($f === false) {
            return false;
        }
        fclose($f);
        if (!$rm) {
            unlink($path);
        }
        return true;
    }

    public static function from($source, $name)
    {
        if (is_array($name)) {
            $data = array();
            foreach ($name as $k) {
                $data[$k] = isset($source[$k]) ? $source[$k] : null;
            }
            return $data;
        }
        return isset($source[$name]) ? $source[$name] : null;
    }
}
