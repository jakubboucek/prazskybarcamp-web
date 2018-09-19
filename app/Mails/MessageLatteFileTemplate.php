<?php

namespace App\Mails;

class MessageLatteFileTemplate implements ITemplate
{
    /**
     * @var string
     */
    private $filename;


    /**
     * MessageLatteFileTemplate constructor.
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }


    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }


    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
}
