<?php

namespace Alfa9\Treatment\Model\ResourceModel\Treatment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Alfa9\Treatment\Model\Treatment;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Treatment::class, \Alfa9\Treatment\Model\ResourceModel\Treatment::class);
    }
}

