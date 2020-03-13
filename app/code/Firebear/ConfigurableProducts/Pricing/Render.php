<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Pricing;

use Magento\Catalog\Model\Session;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Pricing\Render as PriceRender;

/**
 * Configurable Price Render
 */
class Render extends PriceRender
{
    /**
     * Catalog session.
     *
     * @var Session
     */
    private $catalogSession;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SaleableInterface
     */
    private $product;

    /**
     * Construct
     *
     * @param Template\Context           $context
     * @param Registry                   $registry
     * @param Session                    $catalogSession
     * @param ProductRepositoryInterface $productRepository
     * @param array                      $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        Session $catalogSession,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->catalogSession = $catalogSession;
        $this->productRepository = $productRepository;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Set product method.
     * Used when user change configurable product options
     * to show simple product tier prices.
     *
     * @param SaleableInterface $product
     */
    public function setProduct(SaleableInterface $product)
    {
        $this->product = $product;
    }

    /**
     * Returns product instance
     *
     * @return Product
     */
    protected function getProduct()
    {
        if (!$this->product) {
            $this->product = parent::getProduct();

            /**
             * Get simple product by selected configurable options.
             * This allow us to show simple product prices instead of configurable.
             * @see \Firebear\ConfigurableProducts\Plugin\Helper\Catalog\Product
             */
            $data = $this->registry->registry('firebear_configurableproducts');

            if (isset($data['child_id']) && $data['parent_id'] == $this->product->getId()) {
                $productId = $data['child_id'];
                $this->product = $this->productRepository->getById(
                    $productId,
                    false,
                    $this->_storeManager->getStore()->getId()
                );
            }
        }

        return $this->product;
    }
}
