<?php

namespace App\Presenters;

use App\Components\Faq\FaqControl;
use App\Components\Faq\IFaqControlFactory;
use App\Components\Feed\FeedControl;
use App\Components\Feed\FeedFactory;
use App\Components\Newsletter\NewsletterSignupControl;
use App\Components\Newsletter\NewsletterSignupFactory;
use App\Components\Partners\IPartnersControlFactory;
use App\Components\Program\IProgramControlFactory;
use App\Components\Program\ProgramControl;
use App\Components\Schedule\IScheduleControlFactory;
use App\Components\Schedule\ScheduleControl;
use App\Components\SignupButtons\SignupButtonsControl;
use App\Components\SignupButtons\SignupButtonsFactory;
use App\Components\SpeakerList\ISpeakerListControlFactory;
use App\Components\SpeakerList\SpeakerListControl;

/**
 * Class HomepagePresenter
 * @package App\Presenters
 */
class HomepagePresenter extends BasePresenter
{
    /**
     * @var IScheduleControlFactory
     */
    private $scheduleFactory;
    /**
     * @var SignupButtonsFactory
     */
    private $buttonsFactory;
    /**
     * @var NewsletterSignupFactory
     */
    private $newsletterFactory;
    /**
     * @var IFaqControlFactory
     */
    private $faqFactory;
    /**
     * @var ISpeakerListControlFactory
     */
    private $speakerListFactory;
    /**
     * @var IProgramControlFactory
     */
    private $programFactory;
    /**
     * @var IPartnersControlFactory
     */
    private $partnersControlFactory;
    /**
     * @var FeedFactory
     */
    private $feedFactory;


    /**
     * HomepagePresenter constructor.
     * @param IScheduleControlFactory $scheduleFactory
     * @param SignupButtonsFactory $buttonsFactory
     * @param NewsletterSignupFactory $newsletterFormFactory
     * @param IFaqControlFactory $faqFactory
     * @param ISpeakerListControlFactory $speakerListFactory
     * @param IProgramControlFactory $programFactory
     * @param IPartnersControlFactory $partnersControlFactory
     * @param FeedFactory $feedFactory
     */
    public function __construct(
        IScheduleControlFactory $scheduleFactory,
        SignupButtonsFactory $buttonsFactory,
        NewsletterSignupFactory $newsletterFormFactory,
        IFaqControlFactory $faqFactory,
        ISpeakerListControlFactory $speakerListFactory,
        IProgramControlFactory $programFactory,
        IPartnersControlFactory $partnersControlFactory,
        FeedFactory $feedFactory
    ) {
        parent::__construct();
        $this->scheduleFactory = $scheduleFactory;
        $this->buttonsFactory = $buttonsFactory;
        $this->newsletterFactory = $newsletterFormFactory;
        $this->faqFactory = $faqFactory;
        $this->speakerListFactory = $speakerListFactory;
        $this->programFactory = $programFactory;
        $this->partnersControlFactory = $partnersControlFactory;
        $this->feedFactory = $feedFactory;
    }


    /**
     *
     * @throws \Nette\Utils\JsonException
     */
    public function renderDefault()
    {
        $this->template->isHp = true;
        $this->template->counts = $this->eventInfo->getCounts();
    }


    /**
     * @return ScheduleControl
     */
    protected function createComponentSchedule(): ScheduleControl
    {
        return $this->scheduleFactory->create();
    }


    /**
     * @return SignupButtonsControl
     */
    protected function createComponentSignupButtons(): SignupButtonsControl
    {
        return $this->buttonsFactory->create();
    }


    /**
     * @return NewsletterSignupControl
     */
    protected function createComponentNewsletterForm(): NewsletterSignupControl
    {
        return $this->newsletterFactory->create();
    }


    /**
     * @return FaqControl
     */
    protected function createComponentFaq(): FaqControl
    {
        return $this->faqFactory->create();
    }


    /**
     * @return SpeakerListControl
     */
    protected function createComponentSpeakerList(): SpeakerListControl
    {
        return $this->speakerListFactory->create();
    }


    /**
     * @return ProgramControl
     */
    public function createComponentProgram(): ProgramControl
    {
        return $this->programFactory->create();
    }


    /**
     * @return \App\Components\Partners\PartnersControl
     */
    protected function createComponentPartners(): \App\Components\Partners\PartnersControl
    {
        return $this->partnersControlFactory->create();
    }

    /**
     * @return FeedControl
     */
    protected function createComponentFeed(): FeedControl
    {
        return $this->feedFactory->create();
    }

}
