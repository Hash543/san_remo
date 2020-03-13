<?php

/**
 * Product options text type block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Firebear\ConfigurableProducts\Block\Product\View\Type\Bundle\Type;

class Text extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    private $bundleOption = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data           $pricingHelper
     * @param \Magento\Catalog\Helper\Data                     $catalogData ,
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        array $data = []
    ) {
        parent::__construct($context, $pricingHelper, $catalogData, $data);

        if ($this->hasData('bundle_option')) {
            $this->setBundleOption($this->getData('bundle_option'));
        }
    }

    /**
     * @return null
     */
    public function getBundleOption()
    {
        return $this->bundleOption;
    }

    /**
     * @param null $bundleOption
     */
    public function setBundleOption($bundleOption)
    {
        $this->bundleOption = $bundleOption;
    }

    /**
     * Returns default value to show in text input
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
    }
}
