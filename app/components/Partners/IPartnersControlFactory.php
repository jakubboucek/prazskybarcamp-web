<?php

namespace App\Components\Partners;

interface IPartnersControlFactory
{

    /**
     * @return PartnersControl
     */
    public function create();

}
