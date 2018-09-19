<?php

namespace App\Orm;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int $id                                {primary}
 * @property Conferee|null $conferee                {m:1 Conferee::$talk}
 * @property OneHasMany|Program[]|null $program     {1:m Program::$talk}
 * @property string $title
 * @property string|null $description
 * @property string|null $purpose
 * @property int $enabled                           {default 1}
 * @property int $votes                             {default 0}
 * @property int $voteCoefficient                   {default 0}
 * @property string|null $category
 * @property string|null $company
 * @property string|null $notes
 * @property string|null $extended
 * @property \DateTimeImmutable $created            {default now}
 * @property array $expandedExtenstions
 */
class Talk extends Entity
{

    /**
     * @param string|null $item
     * @param mixed|null $default
     * @return mixed|null
     * @throws JsonException
     */
    public function getExpandedExtensions($item = null, $default = null)
    {
        $extended = Json::decode($this->extended, Json::FORCE_ARRAY);
        if (is_null($item)) {
            return $extended;
        } else {
            return isset($extended[$item]) ? $extended[$item] : $default;
        }
    }


    /**
     * @param mixed $value
     * @param string|null $item
     * @throws JsonException
     */
    public function setExpandedExtenstios($value, $item = null)
    {
        if (is_null($item)) {
            $extended = $value;
        } else {
            $extended = $this->getExpandedExtensions();
            $extended[$item] = $value;
        }

        $this->extended = Json::encode($extended);
    }


    /**
     * @return array
     * @throws JsonException
     */
    private function getLinks()
    {
        return $this->getExpandedExtensions('links', []);
    }


    /**
     * @param array $links
     * @throws JsonException
     */
    private function setLinks(array $links)
    {
        $this->setExpandedExtenstios($links, 'links');
    }


    public function getLinksByType($type)
    {
        $links = $this->getLinks();
        return isset($links[$type]) ? $links[$type] : [];
    }


    public function setLinksByType($type, array $items)
    {
        $links = $this->getLinks();
        $links[$type] = $items;
        $this->setLinks($links);
    }

}
