<?php

namespace App\Components\Faq;

use App\Model\EnumeratorManager;
use Nette\Application\UI\Control;

class FaqControl extends Control
{
    /**
     * @var EnumeratorManager
     */
    private $enumeratorManager;


    /**
     * FaqControl constructor.
     * @param EnumeratorManager $enumeratorManager
     */
    public function __construct(EnumeratorManager $enumeratorManager)
    {
        parent::__construct();
        $this->enumeratorManager = $enumeratorManager;
    }


    /**
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    public function render()
    {
        $faqs = $this->enumeratorManager->get(EnumeratorManager::SET_FAQS);

        if (count($faqs)) {
            $this->renderFaqs($faqs);
        } else {
            $this->renderBlank();
        }
    }


    private function renderFaqs(array $faqs)
    {
        $this->template->faqs = $faqs;
        $this->template->setFile(__DIR__ . '/Faq.latte');
        $this->template->render();
    }


    private function renderBlank()
    {
    }
}
