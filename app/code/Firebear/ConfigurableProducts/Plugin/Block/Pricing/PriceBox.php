<?php
/**
 * Copyright © 2017 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Plugin\Block\Pricing;

use Firebear\ConfigurableProducts\Block\Product\Configurable\Pricing\Renderer;
use Firebear\ConfigurableProducts\Helper\Data as CpiHelper;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProductObject;

class PriceBox
{
    /** @var \Magento\Catalog\Helper\Data  */
    protected $catalogHelper;
    /** @var \Magento\Catalog\Model\ProductRepository  */
    private $productRepository;
    /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable  */
    private $configurableProductObject;
    /** @var \Magento\Framework\View\LayoutFactory  */
    private $layoutFactory;
    /** @var \Firebear\ConfigurableProducts\Helper\Data  */
    private $cpiHelper;
    /** @var \Magento\Framework\Registry  */
    private $registry;

    /**
     * PriceBox constructor.
     *
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProductObject
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Firebear\ConfigurableProducts\Helper\Data $cpiHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param array $data
     */
    public function __construct(
        ProductRepository $productRepository,
        ConfigurableProductObject $configurableProductObject,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        CpiHelper $cpiHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogHelper,
        array $data = []
    ) {
        $this->configurableProductObject = $configurableProductObject;
        $this->productRepository         = $productRepository;
        $this->layoutFactory             = $layoutFactory;
        $this->cpiHelper                 = $cpiHelper;
        $this->registry                  = $registry;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @param \Magento\Framework\Pricing\Render\PriceBox $subject
     * @param $result
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterRenderAmount(\Magento\Framework\Pricing\Render\PriceBox $subject, $result)
    {
        $registerKey = 'firebear_display_first_price';
        if ($this->registry->registry($registerKey)) {
            $oldValue = $this->registry->registry($registerKey);
            $this->registry->unregister($registerKey);
            $this->registry->register($registerKey, $oldValue + 1);
        } else {
            $this->registry->register($registerKey, 1);
        }
        if ($this->registry->registry($registerKey) == 1) {
            if ($this->cpiHelper->getGeneralConfig('general/price_range_product')
                && $this->registry->registry(
                    'current_product'
                )) {
                $productId = $subject->getRequest()->getParam('id');
                if ($productId != $subject->getSaleableItem()->getId())
                    return $result;
                if ($productId) {
                    $product       = $this->productRepository->getById($productId);
                    $parentIds     = $this->configurableProductObject->getParentIdsByChild($productId);
                    $parentId      = array_shift($parentIds);
                    $parentProduct = null;
                    if ($parentId) {
                        $parentProduct = $this->productRepository->getById($parentId);
                    }

                    if ($product->getTypeId() == 'configurable'
                        || ($parentProduct
                            && $parentProduct->getTypeId() == 'configurable' && $parentProduct->isSaleable())) {
                        $product             = $this->registry->registry('current_product');
                        $productTypeInstance = $product->getTypeInstance();
                        $usedProducts        = $productTypeInstance->getUsedProducts($product);
                        $priceArray          = [];

                        foreach ($usedProducts as $child) {
                            if ($child->isSaleable()) {
                                $priceArray[] =
                                    $this->catalogHelper->getTaxPrice($child, $child->getFinalPrice(), true);
                            }
                        }

                        if ($this->cpiHelper->getGeneralConfig('general/price_range_compatible_with_tier_price')) {
                            foreach ($usedProducts as $child) {
                                if (!$child->isSaleable()) {
                                    continue;
                                }
                                $productTierPrices = $child->getTierPrices();
                                foreach ($productTierPrices as $tierPriceItem) {
                                    $priceArray[] = round($tierPriceItem->getValue(), 2);
                                }
                            }
                        }
                        $this->registry->unregister('firebear_product_prices');
                        $this->registry->register('firebear_product_prices', $priceArray);

                        $layout = $this->layoutFactory->create();
                        $block  = $layout
                            ->createBlock(Renderer::class)
                            ->setTemplate('Firebear_ConfigurableProducts::product/configurable/pricing/renderer.phtml')
                            ->toHtml();

                        if ($this->cpiHelper->getGeneralConfig('general/price_range_product_original')) {
                            $block .= $result;
                        }

                        return $block;
                    }
                }

                return $result;
            }

            return $result;
        }

        return $result;
    }
}
