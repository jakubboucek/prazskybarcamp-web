<?php

namespace App\Mails;

interface IMessage
{
    /**
     */
    public function send();


    /**
     * @return ITemplate
     */
    public function getTemplate();


    /**
     * @return array
     */
    public function getTemplateParameters();


    /**
     * @return array
     */
    public function getRecipients();


    /**
     * @return string
     */
    public function getSubject();
}
