<?php
/**
 * Copyright © 2017 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Plugin\Block\Product;

use Firebear\ConfigurableProducts\Helper\Data as CpiHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Template;

class ListProduct
{
    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    private $listProductBlock;

    /**
     * @var Configurable
     */
    private $configurableProduct;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $cpiHelper;

    protected $catalogHelper;

    /**
     * ListProduct constructor.
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Firebear\ConfigurableProducts\Helper\Data $cpiHelper
     */
    public function __construct(
        Configurable $configurableProduct,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Helper\Data $catalogHelper,
        CpiHelper $cpiHelper
    ) {
        $this->configurableProduct = $configurableProduct;
        $this->pricingHelper       = $pricingHelper;
        $this->logger              = $logger;
        $this->cpiHelper           = $cpiHelper;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {

        if (Configurable::TYPE_CODE !== $product->getTypeId()) {
            return $proceed($product);
        }

        $this->listProductBlock = $subject;
        if ($this->cpiHelper->getGeneralConfig('general/price_range_category')) {
            $priceText = $this->getPriceRange($product);
        } else {
            return $proceed($product);
        }
        return $priceText;
    }


    /**
     * Get configurable product price range
     *
     * @param $product
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPriceRange($product)
    {
        $childProductPrice = [];
        $childProducts     = $this->configurableProduct->getUsedProducts($product);

        foreach ($childProducts as $child) {
            $price = number_format(
                $this->catalogHelper->getTaxPrice($child, $child->getPrice(), true),
                2,
                '.',
                ''
            );
            $finalPrice = number_format(
                $this->catalogHelper->getTaxPrice($child, $child->getFinalPrice(), true),
                2,
                '.',
                ''
            );
            $productTierPrices = $child->getTierPrices();
            if ($this->cpiHelper->getGeneralConfig('general/price_range_compatible_with_tier_price')) {
                foreach ($productTierPrices as $tierPriceItem) {
                    $childProductPrice[] = round($tierPriceItem->getValue(), 2);
                }
            }
            if ($price == $finalPrice) {
                $childProductPrice[] = $price;
            } elseif ($finalPrice < $price) {
                $childProductPrice[] = $finalPrice;
            }
        }
        $max = $this->pricingHelper->currencyByStore(max($childProductPrice));
        $min = $this->pricingHelper->currencyByStore(min($childProductPrice));
        if ($min === $max) {
            return $this->getPriceRenderChange($product, "$min", '');
        }

        if ($this->cpiHelper->getGeneralConfig('general/price_range_category_from_to_option')) {
            return $this->getPriceRenderChange($product, "From $min - $max", '');
        }

        return $this->getPriceRenderChange($product, "From $min", '');
    }

    /**
     * Price renderer
     *
     * @param $product
     * @param $price
     *
     * @param string $text
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPriceRenderChange($product, $price, $text = '')
    {
        return $this->listProductBlock->getLayout()->createBlock(Template::class)
            ->setTemplate('Firebear_ConfigurableProducts::product/price/range/price.phtml')
            ->setData('price_id', 'product-price-' . $product->getId())
            ->setData('display_label', $text)
            ->setData('product_id', $product->getId())
            ->setData('display_value', $price)->toHtml();
    }
}