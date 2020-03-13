<?php

namespace PSS\Checkout\CustomerData;


class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem {
    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;
    /**
     * DefaultItem constructor.
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Escaper|null $escaper
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Escaper $escaper = null
    ) {
        $this->stockConfiguration = $stockConfiguration;
        parent::__construct($imageHelper, $msrpHelper, $urlBuilder, $configurationPool, $checkoutHelper, $escaper);
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetItemData() {
        $response = parent::doGetItemData();
        /*$product = $this->getProduct();
        $product->load($res['product_id']);

        $resource = $product->getResource();
        $label = $resource->getAttribute('volumen')->getFrontend()->getValue($product);
        $res['volumen'] = $label;*/
        $response['max_qty'] = $this->stockConfiguration->getMaxSaleQty();
        return $response;
    }
}
