<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Firebear\ConfigurableProducts\Ui\DataProvider\Form\Modifier;

use Firebear\ConfigurableProducts\Helper\Data;
use Firebear\ConfigurableProducts\Model\ProductOptions;
use Firebear\ConfigurableProducts\Model\ProductOptionsRepository;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;


class ProductCustomAttributes extends AbstractModifier
{
    private $fields;
    private $optionsRepository;
    private $locator;
    private $containerData;
    private $helper;

    public function __construct(
        Data $helper,
        ProductOptionsRepository $optionsRepository,
        LocatorInterface $locator
    ) {
        $this->helper            = $helper;
        $this->fields            = $helper->getFields();
        $this->containerData     = $helper->getContainerData();
        $this->optionsRepository = $optionsRepository;
        $this->locator           = $locator;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $productId   = $this->locator->getProduct()->getEntityId();
        $productData = $this->optionsRepository->getByProductId($productId)->getData();
        if (!$productData) {
            return $data;
        }
        foreach ($this->getFields() as $field) {
            $data[$productId][static::DATA_SOURCE_DEFAULT][$field] = $productData[$field];
        }
        if (isset($productData['linked_attributes']) && $productData['linked_attributes']) {
            $data[$productId][static::DATA_SOURCE_DEFAULT]['linked_attributes'] = explode(
                ',',
                $productData[ProductOptions::LINKED_ATTRIBUTE_IDS]
            );
        }
        return $data;
    }

    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $productId   = $this->locator->getProduct()
            ->getEntityId();
        $productType = $this->locator->getProduct()->getTypeId();
        if ($productType == 'grouped' || $productType == 'downloadable' || $productType == 'virtual' | $productType == 'simple') {
            return $meta;
        }
        $meta['icp_custom_attributes']['arguments']['data']['config'] = [
            'componentType' => 'fieldset',
            'label'         => __('ICP Custom Attributes'),
            'collapsible'   => true,
            'dataScope'     => 'data.product',
            'sortOrder'     => 100
        ];
        if ($productType == 'bundle') {
            $meta['icp_linked_attributes']['arguments']['data']['config'] = [
                'componentType' => 'fieldset',
                'label' => __('Linked Attributes'),
                'collapsible' => true,
                'dataScope' => 'data.product',
                'sortOrder' => 100
            ];
            $meta['icp_linked_attributes']['children']['container_linked_attributes'] = $this->getContainerData(
                'linked_attributes'
            );
        }
        foreach ($this->getFields() as $attributeCode) {
            if (array_key_exists('container_' . $attributeCode, $this->getContainerData())) {
                $meta['icp_custom_attributes']['children']['container_'
                . $attributeCode] = $this->getContainerData($attributeCode);
            }
        }

        return $meta;
    }

    public function getContainerData($attributeCode = null)
    {
        if ($attributeCode) {
            return $this->containerData['container_' . $attributeCode];
        } else {
            return $this->containerData;
        }
    }
}
