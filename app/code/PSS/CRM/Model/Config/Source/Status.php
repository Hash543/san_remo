<?php

namespace PSS\CRM\Model\Config\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Pending')], ['value' => 1, 'label' => __('Send OK')], ['value' => 2, 'label' => __('Send ERROR')]];
    }

}
