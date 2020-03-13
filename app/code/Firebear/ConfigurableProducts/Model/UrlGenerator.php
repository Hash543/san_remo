<?php
/**
 * Copyright © 2017 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Model;

class UrlGenerator extends \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator
{
    public function isGlobalScope($storeId)
    {
        return parent::isGlobalScope($storeId);
    }

    public function generateForGlobalScope($productCategories, $product = null, $rootCategoryId = null)
    {
        return parent::generateForGlobalScope(
            $productCategories,
            $product,
            $rootCategoryId
        );
    }

    public function generateForSpecificStoreView(
        $storeId,
        $productCategories,
        $product = null,
        $rootCategoryId = null
    ) {
        $objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $versionEdition         = $productMetadata->getEdition();
        $versionNumber = $productMetadata->getVersion();
        if ($versionEdition == 'Enterprise' && $versionNumber == '2.1.7') {
            return parent::generate(
                $product
            );
        } else {
            return parent::generateForSpecificStoreView(
                $storeId,
                $productCategories,
                $product,
                $rootCategoryId
            );
        }
    }
}