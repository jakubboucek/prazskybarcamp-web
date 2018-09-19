<?php

namespace App\Components\Partners;

use App\Model\PartnersManager;
use Nette\Application\UI\Control;

class PartnersControl extends Control
{
    /**
     * @var PartnersManager
     */
    private $partnerManager;


    /**
     * PartnersControl constructor.
     * @param PartnersManager $partnersManager
     */
    public function __construct(PartnersManager $partnersManager)
    {
        parent::__construct();
        $this->partnerManager = $partnersManager;
    }


    public function render()
    {
        $report = $this->partnerManager->getReport();

        $this->template->setFile(__DIR__ . '/Partners.latte');
        $this->template->report = $report;
        $this->template->render();
    }
}
