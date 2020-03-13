<?php

/**
 * Product options text type block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Firebear\ConfigurableProducts\Block\Product\View\Type\Bundle\Type;

class Select extends \Magento\Catalog\Block\Product\View\Options\Type\Select
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
     * Change the options
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getValuesHtml()
    {
        $valuesHtml = parent::getValuesHtml();

        $valuesHtml = str_replace(
            [
                'name="options[' . $this->getOption()->getId() . ']"',
                'name="options[' . $this->getOption()->getId() . '][]"'
            ],
            [
                'name="bundle_custom_options[' . $this->getBundleOption() . '][' . $this->getOption()->getid() . ']"',
                'name="bundle_custom_options[' . $this->getBundleOption() . '][' . $this->getOption()->getid() . '][]"'
            ],
            $valuesHtml
        );

        $valuesHtml = str_replace(
            [
                'id="options_' . $this->getOption()->getId() . '"',
                'id="select_' . $this->getOption()->getId() . '"',
                'id="options_' . $this->getOption()->getId()
            ],
            [
                'id="bundle_custom_options_' . $this->getBundleOption() . '_' . $this->getOption()->getid() . '"',
                'id="bundle_custom_options_' . $this->getBundleOption() . '_' . $this->getOption()->getid() . '"',
                'id="bundle_custom_options_' . $this->getBundleOption() . '_' . $this->getOption()->getid()
            ],
            $valuesHtml
        );

        return $valuesHtml;
    }
}
