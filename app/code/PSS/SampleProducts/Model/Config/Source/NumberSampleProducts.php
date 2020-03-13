<?php
/**
 * @author Israel Yasis
 */
namespace PSS\SampleProducts\Model\Config\Source;

/**
 * Class NumberSampleProducts
 * @package PSS\SampleProducts\Model\Config\Source
 */
class NumberSampleProducts implements \Magento\Framework\Option\ArrayInterface {

    /**
     * {@inheritdoc}
     */
    public function toOptionArray() {
        return [
            ['value' => 1, 'label' => 1],
            ['value' => 2, 'label' => 2],
            ['value' => 3, 'label' => 3],
            ['value' => 4, 'label' => 4],
            ['value' => 5, 'label' => 5],
            ['value' => 6, 'label' => 6],
        ];
    }
}