<?php

namespace Firebear\ConfigurableProducts\Model\Product;

use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\BasePrice;

/**
 * Catalog product option model
 */
class Option extends \Magento\Catalog\Model\Product\Option
{

    /**
     * @var Product
     */
    protected $product;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        Product\Option\Value $productOptionValue,
        \Magento\Catalog\Model\Product\Option\Type\Factory $optionFactory,
        \Magento\Framework\Stdlib\StringUtils $string,
        Product\Option\Validator\Pool $validatorPool,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        $customOptionValuesFactory = null,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->product = $product;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productOptionValue,
            $optionFactory,
            $string,
            $validatorPool,
            $resource,
            $resourceCollection,
            $data,
            $customOptionValuesFactory
        );
    }

    public function getPrice($flag = false)
    {
        if ($flag && $this->getPriceType() == self::$typePercent) {
            if (!$this->getProduct()) {
                $product = $this->product->load($this->_getData('product_id'));
                $this->setProduct($product);
            }
            $basePrice = $this->getProduct()->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getValue();
            $price = $basePrice * ($this->_getData(self::KEY_PRICE) / 100);
            return $price;
        }
        return $this->_getData(self::KEY_PRICE);
    }
}
