<?php

namespace App\Mails;

class RegistrationMessage extends UniversalDynamicMessage implements IMessage
{
    /**
     * RegistrationMessage constructor.
     * @param string $recipient
     * @param array $mail
     */
    public function __construct($recipient, $mail)
    {
        $this->addRecipient($recipient);
        $this->setSubject($mail['subject']);
        $this->setTemlateFromString($mail);
    }
}
