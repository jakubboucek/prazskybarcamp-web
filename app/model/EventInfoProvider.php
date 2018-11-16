<?php

namespace App\Model;

use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

class EventInfoProvider
{
    use SmartObject;

    const COUNTS_CONFEREE = 'counts.conferee';
    const COUNTS_TALKS = 'counts.talks';
    const COUNTS_TALKS_LIMIT = 'counts.talks.limit';
    const COUNTS_WORKSHOPS = 'counts.workshops';
    const COUNTS_WARMUPPARTY = 'counts.warmupparty';
    const COUNTS_AFTERPARTY = 'counts.afterparty';
    const DATE_TALKS = 'schedule.talks.date';
    const DATE_VOTE = 'schedule.vote.date';
    const DATE_EVENT = 'schedule.event.date';
    const DATE_PROGRAM = 'schedule.program.date';
    const DATE_REPORT = 'schedule.report.date';
    const FEATURE_CONFEREE = 'features.registerConferee.enabled';
    const FEATURE_TALK = 'features.registerTalk.enabled';
    const FEATURE_TALK_EDIT = 'features.editTalk.enabled';
    const FEATURE_TALK_ORDER = 'features.talk.order';
    const FEATURE_VOTE = 'features.voteTalk.enabled';
    const FEATURE_SHOW_VOTE = 'features.showVoteTalk.enabled';
    const FEATURE_PROGRAM = 'features.showProgram.enabled';
    const FEATURE_REPORT = 'features.showReport.enabled';
    const URL_FACEBOOK = 'url.social.facebook';
    const URL_TWITTER = 'url.social.twitter';
    const URL_YOUTUBE = 'url.social.youtube';
    const URL_INSTAGRAM = 'url.social.instagram';
    const URL_LINKEDIN = 'url.social.linkedin';
    const URL_WAY_TO_EVENT = 'url.post.howToWay';
    const URL_OG_IMAGE = 'url.igImage';

    const SCHEDULE_VISUAL_DATE_BEGIN = 'schedule.visualDate.begin';
    const SCHEDULE_VISUAL_DATE_END = 'schedule.visualDate.end';


    /**
     * @var ConfigManager
     */
    private $config;
    /**
     * @var ConfereeManager
     */
    private $confereeManager;


    /**
     * EventInfoProvider constructor.
     * @param ConfigManager $config
     */
    public function __construct(ConfigManager $config, ConfereeManager $confereeManager)
    {
        $this->config = $config;
        $this->confereeManager = $confereeManager;
    }


    /**
     * @return ArrayHash
     * @throws \Nette\Utils\JsonException
     */
    public function getDates()
    {
        return ArrayHash::from([
            'talks' => DateTime::from($this->config->get(self::DATE_TALKS)),
            'vote' => DateTime::from($this->config->get(self::DATE_VOTE)),
            'program' => DateTime::from($this->config->get(self::DATE_PROGRAM)),
            'event' => DateTime::from($this->config->get(self::DATE_EVENT)),
            'report' => DateTime::from($this->config->get(self::DATE_REPORT)),
            'scheduleBegin' => DateTime::from($this->config->get(self::SCHEDULE_VISUAL_DATE_BEGIN)),
            'scheduleEnd' => DateTime::from($this->config->get(self::SCHEDULE_VISUAL_DATE_END)),
        ]);
    }


    /**
     * @return ArrayHash
     * @throws \Nette\Utils\JsonException
     */
    public function getSocialUrls()
    {
        return ArrayHash::from([
            'facebook' => $this->config->get(self::URL_FACEBOOK),
            'twitter' => $this->config->get(self::URL_TWITTER),
            'youtube' => $this->config->get(self::URL_YOUTUBE),
            'instagram' => $this->config->get(self::URL_INSTAGRAM),
            'linkedin' => $this->config->get(self::URL_LINKEDIN),
            'way' => $this->config->get(self::URL_WAY_TO_EVENT),
            'ogImage' => $this->config->get(self::URL_OG_IMAGE),
        ]);
    }


    /**
     * @return ArrayHash
     * @throws \Nette\Utils\JsonException
     */
    public function getCounts()
    {
        $confereeLimit = $this->config->get(self::COUNTS_CONFEREE);
        $confereeCount = $this->getConfereeRegisteredCount();

        return ArrayHash::from([
            'conferee' => $confereeLimit,
            'conferee_registered' => $confereeCount,
            'conferee_left' => max(0, $confereeLimit - $confereeCount),
            'talks' => $this->config->get(self::COUNTS_TALKS),
            'talks_limit' => $this->config->get(self::COUNTS_TALKS_LIMIT),
            'workshops' => $this->config->get(self::COUNTS_WORKSHOPS),
            'warmupparty' => $this->config->get(self::COUNTS_WARMUPPARTY),
            'afterparty' => $this->config->get(self::COUNTS_AFTERPARTY),
        ]);
    }


    /**
     * @return ArrayHash
     * @throws \Nette\Utils\JsonException
     */
    public function getFeatures()
    {
        $features = ArrayHash::from([
            'conferee' => $this->config->get(self::FEATURE_CONFEREE),
            'talks' => $this->config->get(self::FEATURE_TALK),
            'talks_edit' => $this->config->get(self::FEATURE_TALK_EDIT),
            'talks_order' => $this->config->get(self::FEATURE_TALK_ORDER),
            'vote' => $this->config->get(self::FEATURE_VOTE),
            'show_vote' => $this->config->get(self::FEATURE_SHOW_VOTE),
            'program' => $this->config->get(self::FEATURE_PROGRAM),
            'report' => $this->config->get(self::FEATURE_REPORT),
        ]);

        $features['talks_show'] = $features['talks'] || $features['vote'] || $features['show_vote'];

        return $features;
    }


    public function getConfereeRegisteredCount()
    {
        return $this->confereeManager->getCount();
    }
}
