<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\Config\Source;

use Amasty\Gdpr\Model\ConsentQueue;
use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => __('Pending'), 'value' => ConsentQueue::STATUS_PENDING],
            ['label' => __('Success'), 'value' => ConsentQueue::STATUS_SUCCESS],
            ['label' => __('Fail'), 'value' => ConsentQueue::STATUS_FAIL],
        ];

        return $options;
    }
}
