<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Firebear\ConfigurableProducts\Plugin\Block\Swatches\Product\Renderer\Listing;

use Firebear\ConfigurableProducts\Helper\Data as FirebearHelper;
/**
 * Swatch renderer block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable
{
    private $helper;
    /**
     * Custom Swatch renderer template.
     */
    const SWATCH_RENDERER_LISTING_TEMPLATE = 'Firebear_ConfigurableProducts::product/view/listing/renderer.phtml';
    const SWATCH_RENDERER_DEFAULT = 'Magento_Swatches::product/listing/renderer.phtml';

    public function __construct(FirebearHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Swatches\Block\Product\Renderer\Configurable $subject
     * @param                                                      $template
     *
     * @return string
     */
    public function afterGetTemplate(
        \Magento\Swatches\Block\Product\Renderer\Configurable $subject,
        $template
    ) {
        if ($this->helper->getGeneralConfig('general/disable_swatches_functionallity_in_listing')) {
            return self::SWATCH_RENDERER_DEFAULT;
        } else {
            return self::SWATCH_RENDERER_LISTING_TEMPLATE;
        }
    }
}
