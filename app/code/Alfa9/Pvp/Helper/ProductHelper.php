<?php

namespace Alfa9\Pvp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class ProductHelper extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        parent::__construct($context);
        $this->productFactory = $productFactory;
    }

    public function loadByPrice($priceId)
    {
        $product = $this->productFactory->create();
        $price_arr = explode('-', $priceId);
        foreach ($price_arr as $item) {
            if (is_numeric($item)) {
                return $product->load($item);
            }
        }
        return $product;
    }


}