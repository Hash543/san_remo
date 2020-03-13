<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Profiles;

class Collection extends \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\Collection
{

    protected function _construct()
    {
        $this->_init('Wyomind\MassProductImport\Model\Profiles', 'Wyomind\MassProductImport\Model\ResourceModel\Profiles');
    }
}
