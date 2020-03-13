<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Price\Model\Config\Source;

class ListViewOption implements \Magento\Framework\Option\ArrayInterface {

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'sales_order_view', 'label' => __('Order View (Admin)')],
            ['value' => 'sales_order_index', 'label' => __('Order Index (Admin)')],
            ['value' => 'catalog_product_view', 'label' => __('Catalog Product View (Frontend)') ],
            ['value' => 'catalog_category_view', 'label' => __('Catalog Product List (Frontend)') ]
        ];
    }
}