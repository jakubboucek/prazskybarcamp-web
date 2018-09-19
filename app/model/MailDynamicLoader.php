<?php

namespace App\Model;

class MailDynamicLoader
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var array
     */
    private $layouts = [
        'layout' => [
            'title' => 'Layout',
        ],
    ];

    /**
     * @var array
     */
    private $structure = [
        'registration' => [
            'title' => 'Registrace',
            'params' => [],
        ],
        'reset-password' => [
            'title' => 'Reset hesla',
            'params' => [
                'tokenUrl' => 'URL na reset hesla'
            ],
        ],
        'vote-announce' => [
            'title' => 'Ohlášení hlasování přednášek',
            'params' => [],
        ],
        'before-event-announce' => [
            'title' => 'E-mail před konáním barcampu',
            'params' => [],
        ],
        'after-event-announce' => [
            'title' => 'E-mail po konání barcampu',
            'params' => [
                'formLink' => 'URL na formulář'
            ],
        ],
        'report-publish-announce' => [
            'title' => 'E-mail po vydání vídeí na YouTube',
            'params' => [],
        ],
    ];


    /**
     * MailDynamicLoader constructor.
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }


    /**
     * @return array
     */
    public function getMails()
    {
        $mails = [];
        foreach ($this->structure as $key => $struct) {
            $mails[$key] = $struct['title'];
        }
        return $mails;
    }


    /**
     * @return array
     */
    public function getLayouts()
    {
        $mails = [];
        foreach ($this->layouts as $key => $struct) {
            $mails[$key] = $struct['title'];
        }
        return $mails;
    }


    /**
     * @param string $id
     * @return array
     * @throws EntityNotFound
     * @throws \Nette\Utils\JsonException
     */
    public function getMailById($id)
    {
        $this->validateId($id);

        $struct = $this->structure[$id];
        $data = $this->loadMail($id);

        return [
            'id' => $id,
            'title' => $struct['title'],
            'params' => $struct['params'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'header' => $data['header'],
            'preheader' => $data['preheader'],
            'purpose' => $data['purpose'],
        ];
    }


    /**
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    public function getLayout()
    {
        $id = 'layout';
        $struct = $this->layouts[$id];
        $data = $this->loadLayout($id);

        return [
            'id' => $id,
            'title' => $struct['title'],
            'body' => $data['body'],
        ];
    }


    /**
     * @param string $id
     * @param string $subject
     * @param string $body
     * @param $header
     * @param $preheader
     * @param $purpose
     * @throws EntityNotFound
     * @throws \Nette\Utils\JsonException
     */
    public function setMail($id, $subject, $body, $header, $preheader, $purpose)
    {
        $this->validateId($id);

        $this->saveMail($id, $subject, $body, $header, $preheader, $purpose);
    }


    /**
     * @param $body
     * @throws \Nette\Utils\JsonException
     */
    public function setLayout($body)
    {
        $id = 'layout';

        $this->saveLayout($id, $body);
    }


    /**
     * @param string $id
     * @throws EntityNotFound
     */
    private function validateId($id)
    {
        if (!isset($this->structure[$id])) {
            throw new EntityNotFound("Mail with ID '$id' not found.");
        }
    }


    /**
     * @param string $id
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    private function loadMail($id)
    {
        $configKey = $this->getMailConfigKey($id);

        return $this->loadTemplate($configKey);
    }


    /**
     * @param string $id
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    private function loadLayout($id)
    {
        $configKey = $this->getLayoutConfigKey($id);

        return $this->loadTemplate($configKey);
    }


    /**
     * @param string $id
     * @param string $subject
     * @param string $body
     * @param string $header
     * @param string $preheader
     * @param string $purpose
     * @throws \Nette\Utils\JsonException
     */
    private function saveMail($id, $subject, $body, $header, $preheader, $purpose)
    {
        $configKey = $this->getMailConfigKey($id);

        $this->saveTemplate($configKey, [
            'subject' => $subject,
            'body' => $body,
            'header' => $header,
            'preheader' => $preheader,
            'purpose' => $purpose,
        ]);
    }


    /**
     * @param $id
     * @param $body
     * @throws \Nette\Utils\JsonException
     */
    private function saveLayout($id, $body)
    {
        $configKey = $this->getLayoutConfigKey($id);

        $this->saveTemplate($configKey, [
            'body' => $body,
        ]);
    }


    /**
     * @param string $mailId
     * @return string
     */
    private function getMailConfigKey($mailId)
    {
        return sprintf("mail.content.%s", $mailId);
    }


    /**
     * @param string $mailId
     * @return string
     */
    private function getLayoutConfigKey($mailId)
    {
        return sprintf("mail.layout.%s", $mailId);
    }


    /**
     * @param $configKey
     * @return array
     * @throws \Nette\Utils\JsonException
     */
    private function loadTemplate($configKey)
    {
        $data = $this->configManager->get($configKey);

        $mail = [];

        foreach (['subject', 'body', 'header', 'preheader', 'purpose'] as $key) {
            $mail[$key] = null;

            if (isset($data[$key])) {
                $mail[$key] = $data[$key];
            }
        }

        return $mail;
    }


    /**
     * @param $configKey
     * @param array $mail
     * @throws \Nette\Utils\JsonException
     */
    private function saveTemplate($configKey, array $mail)
    {
        $this->configManager->set($configKey, $mail);
    }
}
