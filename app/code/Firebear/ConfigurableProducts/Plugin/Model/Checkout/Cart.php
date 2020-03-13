<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Plugin\Model\Checkout;

use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Firebear\ConfigurableProducts\Helper\Data as FirebearCPIHelper;

class Cart
{
    /**
     * @var Configurable
     */
    private $configurableModel;

    private $productFactory;

    private $firebearCPIHelper;

    private $messageManager;

    /**
     * Cart constructor.
     *
     * @param Configurable   $configurableModel
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Configurable $configurableModel,
        ProductFactory $productFactory,
        FirebearCPIHelper $firebearCPIHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->configurableModel = $configurableModel;
        $this->productFactory    = $productFactory;
        $this->firebearCPIHelper = $firebearCPIHelper;
        $this->messageManager    = $messageManager;
    }

    /**
     * @param CustomerCart $subject
     * @param              $productInfo
     * @param null         $requestInfo
     *
     * @return array
     */
    public function beforeAddProduct(CustomerCart $subject, $productInfo, $requestInfo = null)
    {
        if (isset($requestInfo['super_attribute']) && $productInfo->getTypeId() != 'bundle') {
            $newProductInfo = $this->configurableModel
                ->getProductByAttributes($requestInfo['super_attribute'], $productInfo);
            if ($newProductInfo) {
                $productInfo->setPrice($newProductInfo->getPrice());
                $productInfo->setName($newProductInfo->getName());
            }
        }

        return [$productInfo, $requestInfo];
    }
}
