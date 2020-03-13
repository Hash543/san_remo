<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Firebear\ConfigurableProducts\Plugin\Block\Swatches\Product\Renderer;

/**
 * Swatch renderer block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable
{
    /**
     * Custom Swatch renderer template.
     */
    const SWATCH_RENDERER_TEMPLATE = 'Firebear_ConfigurableProducts::product/view/renderer.phtml';
    const SWATCH_RENDERER_MATRIX_TEMPLATE = 'Firebear_ConfigurableProducts::product/view/renderer_matrix.phtml';

    private $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Swatches\Block\Product\Renderer\Configurable $subject
     * @param                                                       $template
     *
     * @return string
     */
    public function afterGetTemplate(
        \Magento\Swatches\Block\Product\Renderer\Configurable $subject,
        $template
    ) {
        if ($this->scopeConfig->getValue('firebear_configurableproducts/matrix/matrix_swatch') == 1) {
            return self::SWATCH_RENDERER_MATRIX_TEMPLATE;
        } else {
            return self::SWATCH_RENDERER_TEMPLATE;
        }
    }
}
