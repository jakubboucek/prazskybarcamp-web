<?php

namespace App\Mails;

use App\Model\MailerManager;
use Nette\InvalidStateException;

abstract class BaseMessage
{
    /**
     * @var MailerManager|null
     */
    protected $manager;

    /**
     * @var array
     */
    protected $recipients = [];

    /**
     * @var string
     */
    protected $subject;

    protected $parameters = [];


    /**
     * @param string $recipient
     */
    public function addRecipient($recipient)
    {
        $this->recipients[] = $recipient;
    }


    /**
     * @return array
     */
    public function getTemplateParameters()
    {
        return $this->parameters;
    }


    /**
     * @param array $parameters
     */
    public function setTemplateParameters($parameters)
    {
        $this->parameters = $parameters;
    }


    /**
     * @param string $name
     * @param string $value
     */
    public function addTemplateParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }


    /**
     * @param MailerManager|null $manager
     */
    public function setManager(MailerManager $manager = null)
    {
        $this->manager = $manager;
    }


    /**
     *  Short way to send mail
     */
    public function send()
    {
        if (!$this->manager) {
            throw new InvalidStateException('Mailer manager is not set, use any Mailer to send Message');
        }

        $this->manager->send($this);
    }


    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }


    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }


    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
}
