<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel\ActionLog;

use Amasty\Gdpr\Model\ActionLog;
use Amasty\Gdpr\Model\ResourceModel\ActionLog as ActionLogResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method ActionLog[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(ActionLog::class, ActionLogResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
