<?php

namespace App\Mails;

class VoteAnnounceMessage extends UniversalDynamicMessage implements IMessage
{
    /**
     * VoteAnnounceMessage constructor.
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
