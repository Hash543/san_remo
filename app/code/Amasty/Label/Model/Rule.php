<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    protected $_product;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $amastySerializer;

    protected function _construct()
    {
        $this->amastySerializer = $this->getData('amastySerializer');
        if (!$this->amastySerializer) {
            $this->amastySerializer = $this->serializer;
        }
        parent::_construct();
        $this->_init('Amasty\Label\Model\ResourceModel\Labels');
        $this->setIdFieldName('entity_id');
    }

    /**
     * @param array $ids
     */
    public function setProductFilter($ids)
    {
        $this->_productsFilter = $ids;
    }

    public function getLabelMatchingProductIds() //skip aftergetLabelMatchingProductIds plugin
    {
        if ($this->_productIds === null) {

            $this->_productIds = [];
            $this->setCollectedAttributes([]);
            foreach (explode(',', $this->getStores()) as $storeId) {
                /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $productCollection = $this->_productCollectionFactory->create()
                    ->setStoreId($storeId);
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }

                $this->getConditions()->collectValidatedAttributes($productCollection);

                $this->_resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => $this->_productFactory->create(),
                        'store_id' => $storeId
                    ]
                );
            }

        }

        return $this->_productIds;
    }

    public function callbackValidateProduct($args)
    {
        $product = $args['product'];
        $storeId = $args['store_id'];
        $product->setData($args['row']);

        $product->setStoreId($storeId);
        if ($this->getConditions()->validate($product)) {
            $this->_productIds[$product->getId()][$storeId] = true;
        }
    }

    /**
     * fix fatal error after migration from 2.1 to 2.2 magento
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->unserializeConditions($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }

    public function unserializeConditions($conditions)
    {
        $resultCondition = $this->amastySerializer->unserialize($conditions);
        if ($resultCondition === false) {
            $resultCondition = $this->serializer->unserialize($conditions);
        }

        return $resultCondition;
    }
}
