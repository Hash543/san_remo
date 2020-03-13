<?php

namespace Alfa9\Gtm\Helper;

class Data extends \WeltPixel\GoogleTagManager\Helper\Data
{
    public function addProductPageInformation()
    {
        $currentProduct = $this->getCurrentProduct();

        if (!empty($currentProduct)) {

            $productBlock = $this->blockFactory->createBlock(\WeltPixel\GoogleTagManager\Block\Product::class)
                            ->setTemplate('Alfa9_Gtm::product.phtml');

            if ($productBlock) {
                $productBlock->setCurrentProduct($currentProduct);
                $productBlock->toHtml();
            }
        }
    }

}