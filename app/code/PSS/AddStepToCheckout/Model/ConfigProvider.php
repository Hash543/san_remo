<?php

namespace PSS\AddStepToCheckout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /** @var LayoutInterface */
    protected $_layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->_layout = $layout;
    }

    public function getConfig()
    {
        $cmsBlock = "items_custom_cart"; // CMS Block Identifier or ID
        /** @var \PSS\PaymentPoints\Block\Checkout\Cart\EarningPoints $pointsBlock */
        $pointsBlock = $this->_layout->createBlock('PSS\PaymentPoints\Block\Checkout\Cart\EarningPoints');
        $pointsBlockHtml = $pointsBlock->setTemplate('PSS_PaymentPoints::checkout/cart/earning_points.phtml')->toHtml();
        return [
            'earning_points_block' => $pointsBlockHtml,
            'items_custom_cart' => $this->_layout->createBlock('Magento\Cms\Block\Block')->setBlockId($cmsBlock)->toHtml()
        ];
    }
}