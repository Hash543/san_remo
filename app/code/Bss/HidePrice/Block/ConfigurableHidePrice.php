<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category  BSS
 * @package   Bss_HidePrice
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * This Block causes double renderer of the swatch please review
 * Swatch renderer block
 * @old \Magento\Swatches\Block\Product\Renderer\Configurable
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableHidePrice extends \Magento\Catalog\Block\Product\View\AbstractView
{
    const BSS_SWATCH_RENDERER_TEMPLATE = 'Bss_HidePrice::configurable.phtml';

    protected $hidePriceHelper;
    protected $jsonEncoder;

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        \Bss\HidePrice\Helper\Data $hidePriceHelper,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->hidePriceHelper = $hidePriceHelper;
        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     * Bss_commerce
     * Get child product data
     */
    public function getJsonChildProductData()
    {
        return $this->jsonEncoder->encode(
            $this->hidePriceHelper->getAllData(
                $this->getProduct()->getEntityId()
            )
        );
    }
    
    /**
     * Get Key for caching block content
     *
     * @return string
     */
    public function getRendererTemplate()
    {
        return self::BSS_SWATCH_RENDERER_TEMPLATE;
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     * @since 100.2.0
     */
    public function toHtml()
    {
        $this->setTemplate(
            $this->getRendererTemplate()
        );

        return parent::toHtml();
    }

}
