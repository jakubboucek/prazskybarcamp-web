<?php

namespace App\Components\SpeakerList;

use App\Model\EventInfoProvider;
use App\Model\TalkManager;
use App\Orm\Talk;
use Nette\Application\UI\Control;

class SpeakerListControl extends Control
{

    /**
     * @var TalkManager
     */
    private $talkManager;
    /**
     * @var EventInfoProvider
     */
    private $eventInfoProvider;


    /**
     * SpeakerListControl constructor.
     * @param TalkManager $talkManager
     * @param EventInfoProvider $eventInfoProvider
     */
    public function __construct(TalkManager $talkManager, EventInfoProvider $eventInfoProvider)
    {
        $this->talkManager = $talkManager;
        $this->eventInfoProvider = $eventInfoProvider;
    }


    /**
     * @throws \Nette\Utils\JsonException
     */
    public function render()
    {
        $talks = $this->talkManager->findActive();

        if ($talks->countStored() == 0) {
            return;
        }

        $talk = new Talk();
        $talk->program->countStored();

        $this->template->setFile(__DIR__ . '/SpeakerList.latte');
        $this->template->talks = $talks;
        $this->template->isProgram = $this->eventInfoProvider->getFeatures()['program'];
        $this->template->render();
    }
}