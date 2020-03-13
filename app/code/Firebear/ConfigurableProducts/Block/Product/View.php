<?php

namespace Firebear\ConfigurableProducts\Block\Product;

class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    public $priceCurrency;

    /**
     * @return string
     */
    public function getStoreCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * Retrieve current variation product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getVariationProduct()
    {
        $request = $this->getRequest()->getParams();
        $fullActionName = $this->getRequest()->getFullActionName();
        if($fullActionName == 'checkout_cart_configure' && isset($request['product_id'])) {
            $productId = $request['product_id'];
        } else {
            if (isset($request['productId'])) {
                $productId = $request['productId'];
            } elseif (isset($request['id'])) {
                $productId = $request['id'];
            } else {
                $productId = null;
            }

        }
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            if ($product) {
                return $product;
            }
        }
        return parent::getProduct();
    }
}