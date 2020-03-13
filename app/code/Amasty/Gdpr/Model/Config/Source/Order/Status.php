<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\Config\Source\Order;

class Status extends \Magento\Sales\Model\Config\Source\Order\Status
{
    protected $_stateStatuses = [];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $orderStatuses = parent::toOptionArray();
        unset($orderStatuses[0]);

        return $orderStatuses;
    }
}
