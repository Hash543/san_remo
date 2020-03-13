<?php

namespace Firebear\ConfigurableProducts\Model\ResourceModel;

class Product extends \Magento\Catalog\Model\ResourceModel\Product
{
    /**
     * Retrieve category ids where product is available
     *
     * @param \Magento\Catalog\Model\Product $object
     *
     * @return array
     */
    public function getAvailableInCategories($object)
    {
        $entityId = (int)$object->getEntityId();
        if (!isset($this->availableCategoryIdsCache[$entityId])) {
            $this->availableCategoryIdsCache[$entityId] = $this->getConnection()->fetchCol(
                $this->getConnection()->select()->distinct()->from(
                    $this->getTable('catalog_category_product_index'),
                    ['category_id']
                )->where(
                    'product_id = ? AND is_parent = 1',
                    $entityId
                )
            );
        }

        return $this->availableCategoryIdsCache[$entityId];
    }
}