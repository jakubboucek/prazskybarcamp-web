<?php

namespace App\Model;

use App\Orm\Conferee;
use App\Orm\ConfereeRepository;
use App\Orm\Orm;

class ConfereeManager
{
    /**
     * @var ConfereeRepository
     */
    private $confereeRepository;


    /**
     * ConfereeManager constructor.
     * @param Orm $orm
     */
    public function __construct(Orm $orm)
    {
        $this->confereeRepository = $orm->conferee;
    }


    /**
     * @param Conferee $conferee
     */
    public function save(Conferee $conferee)
    {
        $this->confereeRepository->persistAndFlush($conferee);
    }


    /**
     * @param Conferee $conferee
     */
    public function remove(Conferee $conferee)
    {
        $this->confereeRepository->removeAndFlush($conferee);
    }


    /**
     * @return \Nextras\Orm\Collection\ICollection
     */
    public function findAll()
    {
        return $this->confereeRepository->findAll();
    }


    /**
     * @param int $id
     * @return Conferee|null
     */
    public function getById($id)
    {
        return $this->confereeRepository->getById($id);
    }


    /**
     * @return int
     */
    public function getCount()
    {
        return $this->findAll()->countStored();
    }
}
