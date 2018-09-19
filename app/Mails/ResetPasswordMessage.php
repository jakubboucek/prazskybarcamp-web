<?php

namespace App\Mails;

class ResetPasswordMessage extends UniversalDynamicMessage implements IMessage
{
    /**
     * RegistrationMessage constructor.
     * @param string $recipient
     * @param array $mail
     * @param string $tokenUrl
     */
    public function __construct($recipient, $mail, $tokenUrl)
    {
        $this->addRecipient($recipient);
        $this->setSubject($mail['subject']);
        $this->setTemlateFromString($mail);
        $this->addTemplateParameter('tokenUrl', $tokenUrl);
    }
}
