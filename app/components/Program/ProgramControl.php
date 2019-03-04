<?php

namespace App\Components\Program;

use App\Model\EventInfoProvider;
use App\Model\TalkManager;
use App\Orm\Program;
use Nette\Application\UI\Control;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

class ProgramControl extends Control
{

    /**
     * @var EventInfoProvider
     */
    private $infoProvider;
    /**
     * @var TalkManager
     */
    private $talkManager;


    /**
     * ProgramControl constructor.
     * @param EventInfoProvider $infoProvider
     * @param TalkManager $talkManager
     */
    public function __construct(EventInfoProvider $infoProvider, TalkManager $talkManager)
    {

        $this->infoProvider = $infoProvider;
        $this->talkManager = $talkManager;
    }


    /**
     * @throws \Nette\Utils\JsonException
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Exception
     */
    public function render(): void
    {
        if($this->infoProvider->getFeatures()['program'] !== true) {
            $this->renderClosed();
            return;
        }

        $this->template->setFile(__DIR__ . '/Program.latte');

        $this->template->dates = $this->infoProvider->getDates();

        $sortedItems = $this->getSortedItems();
        $minMaxBorder = $this->getMinMaxBorder($sortedItems);

        $this->template->items = $this->getRenderableItems($sortedItems, $minMaxBorder);

        $this->template->timeRows = $this->getTimeRows($minMaxBorder);

        $this->template->categories = $this->talkManager->getCategories();
        $this->template->rooms = $this->talkManager->getRooms();

        $this->template->render();
    }


    /**
     * @throws \Nette\Utils\JsonException
     */
    protected function renderClosed(): void
    {
        $this->template->setFile(__DIR__ . '/ProgramClosed.latte');

        $this->template->dates = $this->infoProvider->getDates();

        $this->template->render();
    }


    /**
     * @return array
     */
    private function getSortedItems()
    {
        $program = $this->talkManager->findAllProgram();

        $rooms = [];

        /** @var Program $programItem */
        foreach ($program as $programItem) {
            if (!isset($rooms[$programItem->room])) {
                $rooms[$programItem->room] = [];
            }

            $minutes = $this->dateIntervalToMinutes($programItem->time);
            $internalProgramEnvelope = new InternalProgramEnvelope($programItem);
            $internalProgramEnvelope->setDefaultTitle($this->talkManager->getProgramTypes()[$programItem->type]);

            $rooms[$programItem->room][$minutes] = $internalProgramEnvelope;
        }

        foreach ($rooms as $key => $cat) {
            ksort($rooms[$key]);
        }

        return $rooms;
    }


    /**
     * Get min-max minute mantilels of all exists program items.
     * For spped is using array key (that represent minutes delay of item begin from midnight).
     * @param array $sortedItems
     * @param bool $roundWholeHour
     * @return ArrayHash
     * @throws \Exception
     */
    private function getMinMaxBorder(array $sortedItems, $roundWholeHour = true)
    {
        $max = $min = null;

        foreach ($sortedItems as $room) {
            $min = is_null($min) ? min(array_keys($room)) : min(array_merge([$min], array_keys($room)));


            /** @var InternalProgramEnvelope $maxItem */
            $maxKey = max(array_keys($room));
            $maxItem = $room[$maxKey];
            $maxItemValue = $maxItem->getDuration() + $maxKey;
            $max = is_null($max) ? $maxItemValue : max($max, $maxItemValue);
        }

        if ($roundWholeHour) {
            $min = $min - ($min % 60);
            $max = $max + 60 - (($max % 60) ?: 60);
        }

        return ArrayHash::from([
            'min' => $this->minutesToDateInterval($min),
            'max' => $this->minutesToDateInterval($max),
        ]);
    }


    /**
     * @param array $sortedItems
     * @param ArrayHash $minMaxBorder
     * @return array
     * @throws \Exception
     */
    private function getRenderableItems(array $sortedItems, ArrayHash $minMaxBorder)
    {
        $renderableItems = [];
        foreach ($sortedItems as $roomKey => $roomItems) {
            $renderableItems[$roomKey] = [];
            $items = [];
            $prevEnd = $minMaxBorder->min;

            /** @var InternalProgramEnvelope $program */
            foreach ($roomItems as $program) {
                $spaceMinutes = $program->computePreviousSpaceMinutes($prevEnd);

                if ($spaceMinutes < 0) {
                    Debugger::log(
                        sprintf(
                            'Talk "%s" nelze vykreslit, překrývá se s jiným (nebo je před začátkem)',
                            $program->title
                        ),
                        ILogger::WARNING
                    );
                    continue;
                }

                if ($spaceMinutes > 0) {
                    while ($spaceMinutes) {
                        $items[] = $spacer = $this->getSpacer($prevEnd, min($spaceMinutes, 60));
                        $prevEnd = $spacer->getEndTime();
                        $spaceMinutes = $program->computePreviousSpaceMinutes($prevEnd);
                    }
                }

                $items[] = $program;
                $prevEnd = $program->getEndTime();

            }
            $renderableItems[$roomKey] = $items;
        }

        return $renderableItems;
    }


    private function getTimeRows(ArrayHash $minMaxBorder)
    {
        $rows = [];

        $time = new \DateInterval('PT0H');
        $time->h = $minMaxBorder->min->h;
        $time->i = $minMaxBorder->min->i;

        do {
            $rows[] = $this->formatClock($time);
            $time->h++;
        } while ($minMaxBorder->max->h >= $time->h);

        return $rows;
    }


    /**
     * @param \DateInterval $start
     * @param int $minutes
     * @return InternalProgramVirtual
     */
    private function getSpacer(\DateInterval $start, $minutes)
    {
        return new InternalProgramVirtual('space', $start, $minutes);
    }


    /**
     * @param \DateInterval $dateInterval
     * @return int
     */
    private function dateIntervalToMinutes(\DateInterval $dateInterval)
    {
        $hours = intval($dateInterval->h);

        $minutes = intval($dateInterval->i);

        $minutes += max(0, $hours) * 60;

        return $minutes;
    }


    /**
     * @param int $minutes
     * @return \DateInterval
     * @throws \Exception
     */
    private function minutesToDateInterval($minutes)
    {
        $dateInterval = new \DateInterval('PT0H');
        $h = 0;
        $i = $minutes;

        while ($i >= 60) {
            $i -= 60;
            $h++;
        }

        $dateInterval->i = $i;
        $dateInterval->h = $h;
        return $dateInterval;
    }


    private function formatClock(\DateInterval $dateInterval)
    {
        return $dateInterval->format('%H:%I');
    }
}
