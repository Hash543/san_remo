<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Firebear\ConfigurableProducts\Model\Bundle\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;

class Configurable extends \Magento\ConfigurableProduct\Model\Product\Type\Configurable
{

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then add Configurable specific options.
     *
     * @param \Magento\Framework\DataObject  $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string                         $processMode
     *
     * @return \Magento\Framework\Phrase|array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        if (!isset($buyRequest['only_for_checkbox_bundle'])) {
            return parent::_prepareProduct($buyRequest, $product, $processMode);
        }
        $attributes = $buyRequest->getSuperAttribute();
        if ($attributes
            || !$this->_isStrictProcessMode($processMode)
            && !isset($buyRequest['only_for_checkbox_bundle'])) {
            if (!$this->_isStrictProcessMode($processMode)) {
                if (is_array($attributes)) {
                    foreach ($attributes as $key => $attribute) {
                        foreach ($attribute as $val) {
                            if (empty($val)) {
                                unset($attributes[$key][$product->getId()]);
                            }
                        }
                    }
                } else {
                    $attributes = [];
                }
            }

            //TODO: MAGETWO-23739 get id from _POST and retrieve product from repository immediately.

            /**
             * $attributes = array($attributeId=>$attributeValue)
             */
            $subProduct = true;
            if (!$this->_isStrictProcessMode($processMode) && !isset($buyRequest['only_for_checkbox_bundle'][$product->getId()])) {
                foreach ($this->getConfigurableAttributes($product) as $attributeItem) {
                    /* @var $attributeItem \Magento\Framework\DataObject */
                    $attrId = $attributeItem->getData('attribute_id');
                    if (!isset($attributes[$attrId][$product->getId()])
                        || empty($attributes[$attrId][$product->getId()])) {
                        $subProduct = null;
                        break;
                    }
                }
            }
            if ($subProduct) {
                $subProduct = $this->getProductByAttributes($attributes, $product);
                $result     = $subProduct->getTypeInstance()->_prepareProduct($buyRequest, $product, $processMode);
            }
            if (!$result) {
                return $this->getSpecifyOptionMessage()->render();
            }
            if ($subProduct) {
                $subProductLinkFieldId = $subProduct->getId();
                $product->addCustomOption('attributes', $this->serializer->serialize($attributes));
                $product->addCustomOption('product_qty_' . $subProductLinkFieldId, 1, $subProduct);
                $product->addCustomOption('simple_product', $subProductLinkFieldId, $subProduct);

                $_result = $subProduct->getTypeInstance()->processConfiguration(
                    $buyRequest,
                    $subProduct,
                    $processMode
                );
                if (is_string($_result) && !is_array($_result)) {
                    return $_result;
                }

                if (!isset($_result[0])) {
                    return __('You can\'t add the item to shopping cart.')->render();
                }

                /**
                 * Adding parent product custom options to child product
                 * to be sure that it will be unique as its parent
                 */
                if ($optionIds = $product->getCustomOption('option_ids')) {
                    $optionIds = explode(',', $optionIds->getValue());
                    foreach ($optionIds as $optionId) {
                        if ($option = $product->getCustomOption('option_' . $optionId)) {
                            $_result[0]->addCustomOption('option_' . $optionId, $option->getValue());
                        }
                    }
                }

                $productLinkFieldId = $product->getId();
                $_result[0]->setParentProductId($productLinkFieldId)
                    ->addCustomOption('parent_product_id', $productLinkFieldId);
                if ($this->_isStrictProcessMode($processMode)) {
                    $_result[0]->setCartQty(1);
                }
                $result[] = $_result[0];

                return $result;
            } else {
                if (!$this->_isStrictProcessMode($processMode)) {
                    return $result;
                }
            }
        }

        return $result;
    }
}