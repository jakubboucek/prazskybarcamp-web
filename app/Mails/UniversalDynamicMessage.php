<?php

namespace App\Mails;

class UniversalDynamicMessage extends BaseMessage implements IMessage
{
    /**
     * @var MessageLatteStringTemplate
     */
    private $template;


    /**
     * @param array $mail
     * @return string
     */
    private function injectLatteConnectors($mail)
    {

        $format = <<<EOT
{layout 'layout'}
%s
%s
%s
{block content}
%s
EOT;
        $output = sprintf(
            $format,
            ($mail['header'] ? "{block header}$mail[header]{/block}" : ''),
            ($mail['preheader'] ? "{block preheader}$mail[preheader]{/block}" : ''),
            ($mail['purpose'] ? "{block purpose}$mail[purpose]{/block}" : ''),
            $mail['body']
        );
        return $output;
    }


    /**
     * @return ITemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }


    /**
     * @param MessageLatteStringTemplate $template
     */
    public function setTemplate(MessageLatteStringTemplate $template)
    {
        $this->template = $template;
    }


    /**
     * @param array $mail
     * @param array $layout
     */
    public function setTemlateFromString($mail, $layout = null)
    {
        $template = new MessageLatteStringTemplate();

        $template->setMainTemplate($this->injectLatteConnectors($mail));

        if ($layout) {
            $this->setLayout($layout);
        }

        $this->setTemplate($template);
    }


    /**
     * @param array $layout
     */
    public function setLayout($layout)
    {
        $this->template->setLayout($layout['body']);
    }


}
