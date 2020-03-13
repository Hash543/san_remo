<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Plugin\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

use Firebear\ConfigurableProducts\Model\Product\Defaults;
use Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix as MagentoMatrix;

class Matrix
{
    /**
     * @var Defaults
     */
    private $productDefaults;

    /**
     * Matrix constructor.
     *
     * @param Defaults $productDefaults
     */
    public function __construct(
        Defaults $productDefaults
    ) {
        $this->productDefaults = $productDefaults;
    }

    /**
     * @param MagentoMatrix $subject
     * @param               $result
     *
     * @return mixed
     */
    public function afterGetProductMatrix(
        MagentoMatrix $subject,
        $result
    ) {
        $usedProductId = $this->productDefaults->getDefaultProductId($subject->getProduct());

        if ($usedProductId) {
            foreach ($result as $i => $product) {
                if ($usedProductId == $product['productId']) {
                    $result[$i]['default'] = true;
                }
            }
        }

        return $result;
    }
}
