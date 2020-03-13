<?php

namespace Wyomind\MassStockUpdate\Model\ResourceModel\Profiles;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Wyomind\MassStockUpdate\Model\Profiles', 'Wyomind\MassStockUpdate\Model\ResourceModel\Profiles');
    }

    public function getList($profilesIds)
    {
        if (!empty($profilesIds)) {
            $this->getSelect()->where("id IN (" . implode(',', $profilesIds) . ")");
        }
        return $this;
    }
}
