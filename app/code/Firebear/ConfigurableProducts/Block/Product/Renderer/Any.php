<?php

namespace Firebear\ConfigurableProducts\Block\Product\Renderer;

use Magento\Catalog\Model\Product;

class Any extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * Produce and return block's html output
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function toHtml()
    {
        $this->setTemplate('Firebear_ConfigurableProducts::product/view/type/options/default.phtml');

        return parent::toHtml();
    }

    /**
     * Retrieve current store
     *
     * @return \Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Replace ',' on '.' for js
     *
     * @param float $price
     *
     * @return string
     */
    protected function _registerJsPrice($price)
    {
        return str_replace(',', '.', $price);
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $store = $this->getCurrentStore();

        $regularPrice = $this->getProduct()->getPriceInfo()->getPrice('regular_price');
        $finalPrice   = $this->getProduct()->getPriceInfo()->getPrice('final_price');

        $config = [
            'template'   => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'prices'     => [
                'oldPrice'   => [
                    'amount' => $this->_registerJsPrice($regularPrice->getAmount()->getValue()),
                ],
                'basePrice'  => [
                    'amount' => $this->_registerJsPrice(
                        $finalPrice->getAmount()->getBaseAmount()
                    ),
                ],
                'finalPrice' => [
                    'amount' => $this->_registerJsPrice($finalPrice->getAmount()->getValue()),
                ],
            ],
            'productId'  => $this->getProduct()->getId(),
            'chooseText' => __('Choose an Option...'),
            'images'     => [],
            'index'      => [],
        ];

        if ($this->getProduct()->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        return json_encode($config);
    }

    public function getOptionBlock()
    {
        $optionBlock = $this->getChildBlock('configurableOptions' . $this->getOption()->getId());
        $optionBlock->setProduct($this->getProduct());

        return $optionBlock;
    }
}
