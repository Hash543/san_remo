<?php

namespace PSS\AddStepToCheckout\Plugin\Block\Cart;

class Sidebar
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function afterGetCheckoutUrl(
        \Magento\Checkout\Block\Cart\Sidebar $sidebar,
        $result
    ) {
        return $sidebar->getShoppingCartUrl();
    }
}
