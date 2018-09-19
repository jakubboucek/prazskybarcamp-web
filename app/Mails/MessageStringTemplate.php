<?php

namespace App\Mails;

class MessageStringTemplate implements ITemplate
{
    /**
     * @var string
     */
    private $content;


    /**
     * MessageStringTemplate constructor.
     * @param string $content
     */
    public function __construct($content)
    {

        $this->content = $content;
    }


    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }


    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
