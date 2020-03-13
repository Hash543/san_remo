<?php

namespace Alfa9\StoreInfo\Model;

use Alfa9\StoreInfo\Model\Routing\RoutableInterface;

interface FactoryInterface
{
    /**
     * @return RoutableInterface
     */
    public function create();
}
