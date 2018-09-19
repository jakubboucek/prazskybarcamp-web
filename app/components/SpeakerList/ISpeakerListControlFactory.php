<?php

namespace App\Components\SpeakerList;

interface ISpeakerListControlFactory
{
    /**
     * @return SpeakerListControl
     */
    public function create();
}
