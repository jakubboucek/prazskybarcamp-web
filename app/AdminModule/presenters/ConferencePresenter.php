<?php

namespace App\AdminModule\Presenters;

use App\Model\ConfereeManager;
use App\Model\IdentityManager;
use App\Model\TalkManager;
use App\Model\UserManager;
use App\Orm\Conferee;
use App\Orm\Identity;
use App\Orm\Program;
use App\Orm\Talk;
use App\Orm\UserRole;
use DateInterval;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nextras\Orm\Collection\ICollection;
use Ublaboo\DataGrid\DataGrid;

class ConferencePresenter extends BasePresenter
{
    /**
     * @var ConfereeManager
     */
    private $confereeManager;
    /**
     * @var TalkManager
     */
    private $talkManager;
    /**
     * @var IdentityManager
     */
    private $identityManager;
    /**
     * @var UserManager
     */
    private $userManager;


    /**
     * ConferencePreseneter constructor.
     * @param ConfereeManager $confereeManager
     * @param TalkManager $talkManager
     * @param IdentityManager $identityManager
     * @param UserManager $userManager
     */
    public function __construct(
        ConfereeManager $confereeManager,
        TalkManager $talkManager,
        IdentityManager $identityManager,
        UserManager $userManager
    ) {
        $this->confereeManager = $confereeManager;
        $this->talkManager = $talkManager;
        $this->identityManager = $identityManager;
        $this->userManager = $userManager;
    }


    public function renderConferee(): void
    {
        $this->template->count = $this->confereeManager->findAll()->countStored();
    }


    /**
     * @param $name
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentConfereeDatagrid($name): void
    {
        $grid = new DataGrid($this, $name);

        $grid->setDataSource($this->confereeManager->findAll());

        $grid->addColumnText('name', 'Jméno');
        $grid->addColumnText('email', 'E-mail');
        $grid->addAction('confereeEdit', 'Upravit')->setTitle('Upravit uživatele');
    }


    /**
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function renderConfereeEdit($id): void
    {
        $conferee = $this->confereeManager->getById($id);
        $this->validateConferee($conferee);

        $this->template->conferee = $conferee;

        /** @var HiddenField $idField */
        $idField = $this['confereeDeleteForm']['id'];
        $idField->setDefaultValue($id);

        /** @var HiddenField $idField */
        $idField = $this['confereeAdminForm']['userId'];
        $idField->setDefaultValue($conferee->user->id);

        /** @var Checkbox $checkbox */
        $checkbox = $this['confereeAdminForm']['isAdmin'];
        $checkbox->setDefaultValue($conferee->user->isInRole('admin'));



        $this['confereeEditForm']->setDefaults([
            'id' => $conferee->id,
            'name' => $conferee->name,
            'email' => $conferee->email,
            'pictureUrl' => $conferee->pictureUrl,
            'bio' => $conferee->bio,
        ]);
    }


    /**
     * @return Form
     */
    public function createComponentConfereeEditForm(): Form
    {
        $form = new Form();

        $form->addHidden('id');

        $form->addText('name', 'Jméno')->setRequired();
        $form->addEmail('email', 'E-mail')->setRequired();
        $form->addText('pictureUrl', 'URL obrázku')
            ->setOption('description', 'URL profilového obrázku.');
        $form->addTextArea('bio', 'Bio');


        $form->addSubmit('submit', 'Uložit')->setOption('primary', true);

        $form->addProtection();

        $form->onSuccess[] = [$this, 'onConfereeEditFormSuccess'];

        return $form;
    }


    /**
     * @return Form
     */
    public function createComponentConfereeDeleteForm(): Form
    {
        $form = new Form();

        $form->addHidden('id');
        $form->addSubmit('delete', 'Smazat')->setOption('primary', true);

        $form->addProtection();

        $form->onSuccess[] = [$this, 'onConfereeDeleteFormSuccess'];

        return $form;
    }


    /**
     * @param Form $form
     * @param $values
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function onConfereeEditFormSuccess(Form $form, $values): void
    {
        $id = $values->id;
        $conferee = $this->confereeManager->getById($id);
        $this->validateConferee($conferee);

        $conferee->name = $values->name;
        $conferee->email = $values->email;
        $conferee->pictureUrl = $values->pictureUrl;
        $conferee->bio = $values->bio;

        $user = $conferee->user;

        $user->name = $values->name;
        $user->email = $values->email;
        $user->pictureUrl = $values->pictureUrl;

        $this->confereeManager->save($conferee);

        $this->flashMessage('Uživatel uložen', 'success');
        $this->redirect('conferee');
    }


    /**
     * @param Form $form
     * @param $values
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function onConfereeDeleteFormSuccess(Form $form, $values)
    {
        /** @var SubmitButton $delete */
        $delete = $form['delete'];
        if ($delete->isSubmittedBy()) {
            $id = $values->id;
            $conferee = $this->confereeManager->getById($id);
            $this->validateConferee($conferee);
            $this->deleteConferee($conferee);

            $this->flashMessage('Uživatel smazán', 'success');
        }
        $this->redirect('conferee');
    }

    /**
     * @return Form
     */
    public function createComponentConfereeAdminForm(): Form
    {
        $form = new Form();

        $form->addHidden('userId');
        $form->addCheckbox('isAdmin', 'Povolit uživateli přístup do admina');
        $form->addSubmit('save', 'Uložit nastavení práv')->setOption('primary', true);

        $form->addProtection();

        $form->onSuccess[] = [$this, 'onConfereeAdminFormSuccess'];

        return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function onConfereeAdminFormSuccess(Form $form, $values): void
    {
        /** @var Checkbox $isAdmin */
        $isAdmin = $form['isAdmin'];
        $user = $this->userManager->getById($values->userId);
        if ($isAdmin->isFilled()) {
            $user->addRole('admin');
            $this->userManager->save($user);
            $this->flashMessage('Uživateli byl povolen přístup adminu. Nyní by se měl odhlásit a přihlásit.', 'success');
        }
        else {
            foreach ($user->role as $userRole) {
                if($userRole->role === 'admin') {
                    $this->userManager->removeRole($userRole);
                    $this->flashMessage('Uživateli byl odebrán přístup adminu. Nyní by se měl odhlásit a přihlásit.', 'success');
                }
            }
        }
        $this->redirect('//this');
    }



    /**
     * Remove user with all dependecies
     * @param Conferee $conferee
     */
    private function deleteConferee(Conferee $conferee)
    {
        /** @var Talk $talk */
        foreach ($conferee->talk as $talk) {
            $this->talkManager->remove($talk);
        }

        /** @var Identity $identity */
        foreach ($conferee->user->identity as $identity) {
            $this->identityManager->remove($identity);
        }

        /** @var UserRole $role */
        foreach ($conferee->user->role as $role) {
            $this->userManager->removeRole($role);
        }

        $this->userManager->remove($conferee->user);

        $this->confereeManager->remove($conferee);
    }


    /**
     * @param bool $msExcel
     * @throws \Nette\Application\AbortException
     */
    public function handleExportConfereeCsv($msExcel = false)
    {
        $delimiter = $msExcel ? ';' : ',';

        $allConferee = $this->confereeManager->findAll();

        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv(
            $df,
            [
                "E-mail",
                "Jméno",
                "Přihlášen",
                "Newsletter",
                "Přednášející",
                "Registrace",
                "Souhlas získán",
                "Bio",
                "Firma"
            ],
            $delimiter,
            '"'
        );

        /** @var Conferee $conferee */
        foreach ($allConferee as $conferee) {
            try {
                $extended = Json::decode($conferee->extended, Json::FORCE_ARRAY);
            } catch (JsonException $e) {
                $extended = [];
            }
            @fputcsv($df, [
                $conferee->email,
                $conferee->name,
                'Ano',
                $conferee->allowMail ? 'Ano' : 'Ne',
                count($conferee->talk) ? 'Ano' : 'Ne',
                $conferee->created->format(\DateTime::ATOM),
                $conferee->consens ? $conferee->consens->format(\DateTime::ATOM) : null,
                $conferee->bio,
                isset($extended['company']) ? $extended['company'] : null,
            ], $delimiter, '"');
        }

        fclose($df);
        $csv = ob_get_clean();

        if ($msExcel) {
            $csv = iconv("UTF-8", "WINDOWS-1250//IGNORE", $csv);
        }

        $fileDatePostfix = gmdate("Ymd.his");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");

        header("Content-Type: application/octet-stream");
        header("Content-Length: " . strlen($csv));

        header("Content-Disposition: attachment;filename=users-$fileDatePostfix.csv");
        echo $csv;

        $this->terminate();
    }


    public function renderTalks()
    {
        $this->template->count = $this->talkManager->findAll()->countStored();
    }


    /**
     * @param $name
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     * @throws \Ublaboo\DataGrid\Exception\DataGridColumnStatusException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentTalksDatagrid($name)
    {
        $categories = $this->talkManager->getCategories();
        $rooms = $this->talkManager->getRooms();


        $grid = new DataGrid($this, $name);
        DataGrid::$icon_prefix = 'glyphicon glyphicon-';

        $grid->setDataSource($this->talkManager->findAll());

        $grid->addColumnLink('title', 'Název', ':Conference:talkDetail', 'title', ['id'])
            ->setSortable()
            ->setFilterText('title');

        $grid->addColumnText('speaker', 'Jméno', 'conferee.name')
            ->setSortable();

        $grid->addColumnText('mail', 'Mail', 'conferee.email')
            ->setSortable();

        $grid->addColumnText('votes', 'Hlasy')
            ->setSortable();

        $grid->addColumnCallback('votes', function ($column, $talk) {
            /** @var Talk $talk */
            $column->setRenderer(function () use ($talk) {
                if ($talk->voteCoefficient != 0) {
                    return $talk->votes . " (koef: $talk->voteCoefficient)";
                } else {
                    return $talk->votes;

                }
            });
        });


        $grid->addColumnText('category', 'Kategorie')
            ->setReplacement($categories);

        $onStatusChange = function ($id, $status) use ($grid) {
            /** @var Talk $talk */
            $talk = $this->talkManager->getById($id);
            $talk->setValue('enabled', $status);
            $this->talkManager->save($talk);

            if ($this->isAjax()) {
                $grid->redrawItem($id);
            }
        };

        $grid->addColumnStatus('enabled', 'Aktivní')
            ->addOption(1, 'Aktivní')
            ->endOption()
            ->addOption(0, 'Zrušená')
            ->setClass('btn-danger')
            ->endOption()
            ->onChange[] = $onStatusChange;

        $grid->addAction('edit', '', 'talkEdit')
            ->setIcon('pencil')
            ->setTitle('Upravit');
    }


    /**
     * @param $id
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Utils\JsonException
     */
    public function renderTalkEdit($id)
    {
        /** @var Talk $talk */
        $talk = $this->talkManager->getById($id);

        if (!$talk) {
            $this->error('přednáška nenalezena');
        }

        $this->template->talk = $talk;
        $this->template->extended = $talk->getExpandedExtensions();

        /** @var Form $form */
        $form = $this['talkForm'];

        $values = $talk->toArray();

        $form->setDefaults($values);
    }


    public function renderEditLink($talkId, $type, $key)
    {
        $talk = $this->talkManager->getById($talkId);
        $links = $talk->getLinksByType($type);

        if (!$talk) {
            $this->error('Talk nemáme');
        }

        $this->template->talk = $talk;

        if (isset($links[$key])) {
            $link = $links[$key];
        } elseif ($key == 'new') {
            $link = [
                'name' => $talk->conferee->name . ": " . $talk->title,
                'url' => '',
            ];
        } else {
            $this->error('Odkaz nemáme');
        }

        /** @var Form $form */
        $form = $this['linkEditForm'];
        $form->setDefaults([
            'name' => $link['name'],
            'url' => $link['url'],
            'talkId' => $talkId,
            'type' => $type,
            'key' => $key,
        ]);
    }


    public function createComponentLinkEditForm()
    {
        $form = new Form();

        $form->addText('name', 'Popis')->setRequired();
        $form->addText('url', 'URL odkazu')->setRequired()->addRule(Form::URL);

        $form->addHidden('talkId');
        $form->addHidden('key');
        $form->addHidden('type');

        $form->addProtection();

        $form->addSubmit('submit', 'Uložit');

        $form->onSuccess[] = function (Form $form, $values) {
            $talk = $this->talkManager->getById($values->talkId);
            $links = $talk->getLinksByType($values->type);
            $link = [
                'name' => $values->name,
                'url' => $values->url,
            ];
            if ($values->key == 'new') {
                $links[] = $link;
            } else {
                $links[$values->key] = $link;
            }
            $talk->setLinksByType($values->type, $links);
            $this->talkManager->save($talk);
            $this->redirect('talkEdit', ['id' => $values->talkId]);
        };

        return $form;
    }


    /**
     * @secured
     * @param $talkId
     * @param $type
     * @param $key
     * @throws \Nette\Application\AbortException
     */
    public function handleDeleteLink($talkId, $type, $key)
    {
        $talk = $this->talkManager->getById($talkId);
        $links = $talk->getLinksByType($type);
        unset($links[$key]);
        $talk->setLinksByType($type, $links);
        $this->talkManager->save($talk);
        $this->redirect('talkEdit', ['id' => $talkId]);
    }


    /**
     * @return Form
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    public function createComponentTalkForm()
    {
        $form = new Form();

        $form->addHidden('id');

        $form->addText('title', 'Název');
        $form->addTextArea('description', 'Popis');
        $form->addTextArea('purpose', 'Pro koho je určena');
        $form->addSelect('category', 'Kategorie', $this->talkManager->getCategories());
        $form->addText('company', 'Firma');
        $form->addText('voteCoefficient', 'Hlasovací koeficient')
            ->setRequired('Hlasovací koeficient musí být vyplněn - zadejte 0.')
            ->addRule(Form::INTEGER, 'Hlasovací koeficient musí být číslo')
            ->setHtmlType('number');

        $form->addSubmit('submit', 'Odeslat')->setOption('primary', true);

        $form->addProtection();

        $form->onSuccess[] = [$this, 'onTalkFormSuccess'];

        return $form;
    }


    /**
     * @param Form $form
     * @param ArrayHash $values
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Application\AbortException
     * @throws \Exception
     */
    public function onTalkFormSuccess(Form $form, $values)
    {
        $id = $values->id;

        /** @var Talk $talk */
        $talk = $this->talkManager->getById($id);

        if (!$talk) {
            $this->error('Přednáška nenalezena');
        }

        foreach ($values as $key => $value) {
            if (in_array($key, ['id'])) {
                continue;
            }

            if ($value === '') {
                $value = null;
            }
            $talk->setValue($key, $value);
        }

        $this->talkManager->save($talk);
        $this->talkManager->recountVote($id);

        $this->flashMessage('Uloženo', 'success');
        $this->redirect('talks');
    }


    /**
     * @param $name
     * @throws JsonException
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentProgramDatagrid($name)
    {
        $rooms = $this->talkManager->getRooms();
        $program = $this->talkManager->findAllProgram()
            ->orderBy('room', ICollection::ASC)
            ->orderBy('time', ICollection::ASC);

        $grid = new DataGrid($this, $name);
        DataGrid::$icon_prefix = 'glyphicon glyphicon-';

        $grid->setDataSource($program);

        $grid->addToolbarButton('programEdit', 'Přidat do programu…')
            ->setClass('btn btn-xs btn-primary')
            ->setIcon('plus');

        $grid->addColumnText('type', 'Typ')
            ->setReplacement($this->talkManager->getProgramTypes());

        $grid->addColumnText('title', 'Název')->setRenderer(function ($row) {
            /** @var Program $row */
            if (empty($row->title) && isset($row->talk)) {
                return $row->talk->title;
            } else {
                return $row->title;
            }
        });

        $grid->addColumnText('speaker', 'Přednášející')->setRenderer(function ($row) {
            /** @var Program $row */
            if (empty($row->speaker) && isset($row->talk)) {
                return $row->talk->conferee->name;
            } else {
                return $row->speaker;
            }
        });

        $grid->addColumnText('room', 'Místnost')
            ->setReplacement($rooms);

        $grid->addColumnText('time', 'Čas')->setRenderer(function ($row) {
            /** @var Program $row */
            if (is_null($row->time)) {
                return null;
            } else {
                return $row->time->format('%H:%I:%S');
            }
        });

        $grid->addColumnText('duration', 'Délka [minuty]');

        $grid->addAction('edit', '', 'programEdit')
            ->setIcon('pencil')
            ->setTitle('Upravit');

        $grid->addAction('delete', null, 'deleteProgram!')
            ->setIcon('trash')
            ->setTitle('Smazat')
            ->setClass('btn btn-xs btn-danger ajax')
            ->setConfirm('Opravdu chcete smazat z programu přednášku %s?', 'title');
    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     * @secured
     */
    public function handleDeleteProgram($id)
    {
        $program = $this->talkManager->getProgramById($id);

        $this->talkManager->removeProgram($program);

        if ($this->isAjax()) {
            $this['programDatagrid']->reload();
        } else {
            $this->redirect('this');
        }
    }


    /**
     *
     * @throws JsonException
     */
    public function getMergedTalks()
    {
        $talks = $this->talkManager->findAll()->orderBy('votes', ICollection::DESC);

        $usedTalksIds = $this->getProgramListedTalksId();

        $merged = $this->talkManager->getProgramTypes();
        unset($merged['talk']);

        /** @var Talk $talk */
        foreach ($talks as $talk) {
            $id = $talk->id;

            $requestedDuration = "";
            $extended = Json::decode($talk->extended, Json::FORCE_ARRAY);
            $duration = isset($extended['requested_duration']) ? intval($extended['requested_duration']) : null;
            if ($duration) {
                $requestedDuration = " (požadováno $duration minut)";
            }

            $merged['talk|' . $id] = sprintf(
                'Přednáška: %s [%s] %s | %s%s',
                in_array($id, $usedTalksIds) ? '●' : '○',
                $talk->votes,
                $talk->conferee->name,
                $talk->title,
                $requestedDuration
            );
        }

        return $merged;
    }


    private function getProgramStyles()
    {
        return [
            null => 'Výchozí barva',
            'style0' => 'Šedá',
            'style1' => 'Modrá',
            'style2' => 'Oranžová',
            'style3' => 'Červená',
            'style4' => 'Tyrkysová',
        ];
    }


    /**
     * @return array
     */
    private function getProgramListedTalksId()
    {
        $program = $this->talkManager->findAllProgram();
        $ids = [];

        /** @var Program $item */
        foreach ($program as $item) {
            if ($item->talk) {
                $ids[] = $item->talk->id;
            }
        }

        return $ids;
    }


    /**
     * @param null $id
     * @throws \Nette\Application\BadRequestException
     */
    public function renderProgramEdit($id = null)
    {
        if ($id === null) {
            return;
        }
        /** @var Program $program */
        $program = $this->talkManager->getProgramById($id);

        if (!$program) {
            $this->error('Program nenalezen');
        }

        /** @var Form $form */
        $form = $this['programForm'];

        $values = $program->toArray();
        $values['time'] = is_null($values['time']) ? '' : $values['time']->format('%H:%I:%S');
        $values['type'] = $program->talk ? $program->type . '|' . $program->talk->id : $program->type;

        $form->setDefaults($values);
    }


    /**
     * @return Form
     * @throws JsonException
     * @throws \App\Model\InvalidEnumeratorSetException
     */
    public function createComponentProgramForm()
    {
        $durations = $this->talkManager->getDurations();
        $durations += $this->talkManager->getDurationChoice();
        $durations = array_filter($durations, 'intval', ARRAY_FILTER_USE_KEY);

        $form = new Form();

        $form->addGroup();

        $form->addHidden('id');

        $form->addSelect('type', 'Type', [null => '== Vyberte =='] + $this->getMergedTalks())
            ->setRequired()
            ->setOption('description', 'Legenda symbolů: ● - již v programu; ○ - není v programu; [123] - počet hlasů')
            ->getControlPrototype()->appendAttribute('class', 'ui search dropdown form-control');
        $form->addRadioList('room', 'Místnost', $this->talkManager->getRooms())->setRequired();
        $form->addText('time', 'Čas konání')->setType('time')->setRequired();
        $form->addRadioList('duration', 'Délka v minutách', $durations)->setRequired();

        $form->addSubmit('submit', 'Uložit')->setOption('primary', true);

        $form->addGroup('Vlastní název přednášky v programu');

        $form->addTextArea('title', 'Název')
            ->setOption(
                'description',
                'Volitelné. Zadejte jen pokud je potřeba v programu název přednášky přepsat. Povolené HTML'
            );
        $form->addText('speaker', 'Přednášející', null, 200)
            ->setOption(
                'description',
                'Volitelné. Zadejte jen pokud je potřeba v programu název speakera přepsat. Max. 200 znaků'
            );

        $form->addSelect('style', 'Styl (barva)', $this->getProgramStyles());

        $form->addGroup();

        $form->addSubmit('submit_rename', 'Uložit a přejmenovat')->setOption('primary', true);

        $form->addProtection();

        $form->onSuccess[] = [$this, 'onProgramFormSuccess'];

        return $form;
    }


    /**
     * @param Form $form
     * @param $values
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    public function onProgramFormSuccess(Form $form, $values)
    {
        $id = $values->id;

        /** @var Program $program */
        $program = $this->talkManager->getProgramById($id);

        if (!$program) {
            $program = new Program;
        }

        foreach ($values as $key => $value) {
            if (in_array($key, ['id'])) {
                continue;
            }

            if ($key === 'time') {
                if (preg_match('#^(-?)(\d+):(\d+)#', $value, $m)) {
                    $value = new DateInterval("PT{$m[2]}H{$m[3]}M");
                } else {
                    $values = null;
                }
            }

            if ($key === 'type') {
                list($type, $talkId) = array_pad(explode('|', $value, 2), 2, null);
                $program->type = $type;
                if ($talkId) {
                    $program->talk = $this->talkManager->getById($talkId);
                } else {
                    $program->talk = null;
                }
                continue;
            }

            if ($value === '') {
                $value = null;
            }
            $program->setValue($key, $value);
        }

        $this->talkManager->saveProgram($program);

        $this->flashMessage('Uloženo', 'success');
        $this->redirect('program');
    }


    /**
     * @param $conferee
     * @throws \Nette\Application\BadRequestException
     */
    private function validateConferee($conferee)
    {
        if (!$conferee instanceof Conferee) {
            $this->error('Tento účastník nebyl nalezen');
        }
    }
}
