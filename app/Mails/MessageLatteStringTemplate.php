<?php

namespace App\Mails;

class MessageLatteStringTemplate implements ITemplate
{
    /**
     * @var array
     */
    private $templates;


    /**
     * MessageLatteFileTemplate constructor.
     * @param array $templates
     */
    public function __construct(array $templates = [])
    {
        $this->templates = $templates;
    }


    /**
     * @param string $key
     * @param string $template
     */
    public function addTemplate($key, $template)
    {
        $this->templates[$key] = $template;
    }


    /**
     * @param string $template
     */
    public function setLayout($template)
    {
        $this->templates['layout'] = $template;
    }


    /**
     * @param string $template
     */
    public function setMainTemplate($template)
    {
        $this->templates['main'] = $template;
    }


    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }


    /**
     * @param array $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }
}
