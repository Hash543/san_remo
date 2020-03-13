<?php

namespace Firebear\ConfigurableProducts\Plugin\Block\Product\View\Options;

class AbstractOptions
{
    private $registry;

    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    public function aroundGetFormattedPrice(
        \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject,
        callable $proceed
    ) {
        if (!$this->registry->registry('firebear_configurable_products_abstract_plugin')) {
            return $proceed();
        }

        if ($option = $subject->getOption()) {
            $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
            $currencyModel  = $objectManager->create(
                'Magento\Directory\Model\Currency'
            );
            $price          = $option->getPrice($option->getPriceType());
            $currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $currencyCode   = $currencysymbol->getStore()->getCurrentCurrencyCode();
            $currencySymbol = $currencyModel->load($currencyCode)->getCurrencySymbol();
            $precision      = 2;
            $formattedPrice = $currencyModel->format(
                $price,
                ['symbol' => $currencySymbol, 'precision' => $precision],
                false,
                false
            );

            $priceStr = $formattedPrice;

            return $priceStr;
        }

        return '';
    }
}
