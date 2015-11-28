<?php
namespace HTMLy;

class Messages
{

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
        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                $string .= '<p class="error">' . $error . "</p>";
            }
        }
        if (!empty($this->warnings)) {
            foreach ($this->warnings as $warning) {
                $string .= '<p class="warning">' . $warning . "</p>";
            }
        }
        return $string;
    }

}