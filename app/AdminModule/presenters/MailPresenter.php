<?php

namespace App\AdminModule\Presenters;

use App\Model\EntityNotFound;
use App\Model\MailDynamicLoader;
use App\Model\MailerManager;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;
use Ublaboo\DataGrid\DataGrid;

class MailPresenter extends BasePresenter
{
    /**
     * @var MailDynamicLoader
     */
    private $mailLoader;
    /**
     * @var MailerManager
     */
    private $mailer;


    /**
     * MailPresenter constructor.
     * @param MailDynamicLoader $mailLoader
     * @param MailerManager $mailer
     */
    public function __construct(MailDynamicLoader $mailLoader, MailerManager $mailer)
    {
        $this->mailLoader = $mailLoader;
        $this->mailer = $mailer;
    }


    /**
     * @param string $name
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentMailsDatagrid($name)
    {
        $grid = new DataGrid($this, $name);

        $mails = [];

        foreach ($this->mailLoader->getMails() as $key => $title) {
            $mails[] = [
                'id' => $key,
                'title' => $title,
            ];
        }

        $grid->setDataSource($mails);
        $grid->addColumnLink('title', 'Typ e-mailu', 'edit', null, ['id']);
        $grid->addAction('edit', 'Upravit')->setTitle('Upravit e-mail');
        $grid->addAction('view', 'Zobrazit')->setTitle('Zobrazit náhled e-mailu');
        $grid->setPagination(false);
    }


    /**
     * @param $name
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentLayoutsDatagrid($name)
    {
        $grid = new DataGrid($this, $name);

        $mails = [];

        foreach ($this->mailLoader->getLayouts() as $key => $title) {
            $mails[] = [
                'id' => $key,
                'title' => $title,
            ];
        }

        $grid->setDataSource($mails);
        $grid->addColumnLink('title', 'Layout', 'edit', null, ['id']);
        $grid->addAction('edit', 'Upravit')->setTitle('Upravit e-mail');
        $grid->setPagination(false);
    }


    /**
     * @param string $id
     * @throws BadRequestException
     * @throws \Nette\Utils\JsonException
     */
    public function renderEdit($id)
    {

        $this->template->id = $id;


        /** @var Form $form */
        $form = $this['editForm'];

        if ($id === 'layout') {
            $mail = $this->mailLoader->getLayout();
            $form->removeComponent($form['subject']);
            $form->removeComponent($form['header']);
            $form->removeComponent($form['preheader']);
            $form->removeComponent($form['purpose']);
        } else {
            $mail = $this->getMailById($id);
        }
        $form->setDefaults($mail);
    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function renderView($id)
    {
        $this->forward('preview', ['id' => $id]); //temporary
    }


    /**
     * @param $id
     * @param string $parametersJson
     * @throws EntityNotFound
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function renderPreview($id, $parametersJson = '{}')
    {
        $parameters = Json::decode($parametersJson, Json::FORCE_ARRAY);

        $message = $this->mailer->getDynamicMessage(null, $id, $parameters);

        $body = $this->mailer->compileBody($message);

        echo $body;

        $this->terminate();
    }


    /**
     * @param $templateId
     * @param $recipient
     * @param string $parametersJson
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     * @throws EntityNotFound
     */
    public function renderSend($templateId, $recipient, $parametersJson = '{}')
    {
        $parameters = Json::decode($parametersJson, Json::FORCE_ARRAY);

        $this->mailer->getDynamicMessage($recipient, $templateId, $parameters)->send();

        $reponse = [
            'status' => 'ok',
            'templateId' => $templateId,
            'recipient' => $recipient,
            'parameters' => $parameters,
        ];

        $this->sendJson($reponse);
    }


    /**
     * @param $id
     * @return array
     * @throws BadRequestException
     * @throws \Nette\Utils\JsonException
     */
    private function getMailById($id)
    {
        try {
            return $this->mailLoader->getMailById($id);
        } catch (EntityNotFound $e) {
            throw new BadRequestException('Tento e-mail nebyl nalezen', 404, $e);
        }
    }


    /**
     * @return Form
     */
    public function createComponentEditForm()
    {
        $form = new Form();

        $form->addHidden('id');
        $form->addSubmit('submit', 'Uložit');

        $form->addText('subject', 'Předmět e-mailu');

        $form->addTextArea('body', 'Tělo e-mailu', null, 20)
            ->setOption('description', 'Editace HTML');

        $form->addText('header', 'Nadpis')
            ->setOption('description', 'Zobrazí se v e-mailu jako velký nadpis (a propíše se technicky na víc míst)');

        $form->addText('preheader', 'Náhledový text')
            ->setOption('description', 'Pár slov, které někteří klienti mohou zobrazit v náhledu e-mailu');

        $form->addText('purpose', 'Odůvodnění poslání e-mailu')
            ->setOption('description', 'Zobrazuje se v patičce e-mailu (doporučeno v rámci GDPR)');

        $form->addSubmit('submit2', 'Uložit');

        $form->addProtection();

        $form->onSuccess[] = [$this, 'save'];

        return $form;
    }


    /**
     * @param Form $form
     * @param ArrayHash $values
     * @throws EntityNotFound
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    public function save(Form $form, $values)
    {
        $id = $values->id;
        if ($id === 'layout') {
            $this->mailLoader->setLayout($values['body']);
        } else {
            $this->mailLoader->setMail(
                $id,
                $values['subject'],
                $values['body'],
                $values['header'],
                $values['preheader'],
                $values['purpose']
            );
        }

        $this->flashMessage('Uloženo', 'success');
        $this->redirect('this');
    }
}
