<?php

namespace Alfa9\Treatment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Treatment extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('a9_treatment_days', 'treatment_id');
    }
}
