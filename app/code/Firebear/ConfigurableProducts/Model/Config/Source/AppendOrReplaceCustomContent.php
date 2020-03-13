<?php
namespace Firebear\ConfigurableProducts\Model\Config\Source;

class AppendOrReplaceCustomContent extends \Magento\Config\Model\Config\Source\Yesno
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => true, 'label' => __('Replace content')],
            ['value' => false, 'label' => __('Append content')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Append content'), 1 => __('Replace content')];
    }
}
