<?php
/**
 * Copyright © 2017 Firebear Studio. All rights reserved.
 */
 
namespace Firebear\ConfigurableProducts\Block\Product\Configurable\Pricing;

use \Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Firebear\ConfigurableProducts\Helper\Data as icpHelper;

class Renderer extends \Magento\Framework\View\Element\Template
{
    private $registry;
    private $priceHelper;
    private $icpHelper;

    public function __construct(
        Registry $registry,
        Template\Context $context,
        PriceHelper $priceHelper,
        icpHelper $icpHelper,
        array $data = []
    ) {
        $this->priceHelper = $priceHelper;
        $this->registry = $registry;
        $this->icpHelper = $icpHelper;
        parent::__construct($context, $data);
    }
    
    public function getMaxMinProductPrices()
    {
        $priceArray  = $this->registry->registry('firebear_product_prices');
        $maxMinArray = [];
        $maxMinArray['min'] = min($priceArray);
        $maxMinArray['max'] = max($priceArray);
        $maxMinArray['min'] = $this->priceHelper->currency($maxMinArray['min'],true, false);
        $maxMinArray['max'] = $this->priceHelper->currency($maxMinArray['max'],true, false);
        return $maxMinArray;
    }
    public function getDisplayingOption()
    {
        return $this->icpHelper->getGeneralConfig('general/price_range_product_from_to_option');
    }
}