<?php

namespace Firebear\ConfigurableProducts\Plugin\Block\Adminhtml\Sales\Order\View\Items;

class Renderer
{
    protected $eavAttribute;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute)
    {
        $this->eavAttribute = $eavAttribute;
    }

    public function afterGetOrderOptions(\Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items\Renderer $subject, $result)
    {
        $configurableProductOptions = [];
        $options = $subject->getItem()->getProductOptions();

        if (isset($options['info_buyRequest']) && isset($options['info_buyRequest']['super_attribute'])) {
            foreach ($options['info_buyRequest']['super_attribute'] as $optionId => $attributeIds) {
                foreach ($attributeIds as $attributeId => $attributeOptionId) {
                    $attribute = $this->eavAttribute->load($attributeId);
                    $attributeCode = $attribute->getAttributeCode();
                    $optionCode = $attribute->getSource()->getOptionText($attributeOptionId);

                    $configurableProductOptions[] = ['label' => ucfirst($attributeCode), 'value' => $optionCode];
                }
            }
            $result = $configurableProductOptions;
        }
        return $result;
    }
}
