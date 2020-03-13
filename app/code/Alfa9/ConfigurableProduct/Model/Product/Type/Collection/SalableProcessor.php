<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Model\Product\Type\Collection;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\CatalogInventory\Model\Configuration;
/**
 * Class SalableProcessor
 * @package Alfa9\ConfigurableProduct\Model\Product\Type\Collection
 */
class SalableProcessor extends \Magento\ConfigurableProduct\Model\Product\Type\Collection\SalableProcessor
{
    /**
     * @var StatusFactory
     */
    private $stockStatusFactory;

    /**
     * @var Configuration
     */
    private $stockConfiguration;

    /**
     * SalableProcessor constructor
     *
     * @param StatusFactory $stockStatusFactory
     * @param Configuration $stockConfiguration
     */
    public function __construct(
        StatusFactory $stockStatusFactory,
        Configuration $stockConfiguration
    ) {
        parent::__construct($stockStatusFactory);
        $this->stockStatusFactory = $stockStatusFactory;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Rewritten so that if show out of stock products is set to yes
     * it wont remove out of stock products
     *
     * @param Collection $collection
     * @return Collection
     */
    public function process(Collection $collection)
    {
        $collection->addAttributeToFilter(
            ProductInterface::STATUS,
            Status::STATUS_ENABLED
        );

        $stockFlag = 'has_stock_status_filter';
        if (!$collection->hasFlag($stockFlag)) {
            $stockStatusResource = $this->stockStatusFactory->create();
            $isFilterInStock = $this->getIsFilterInStock();
            $stockStatusResource->addStockDataToCollection(
                $collection,
                $isFilterInStock
            );
            $collection->setFlag($stockFlag, true);
        }

        return $collection;
    }

    /**
     * Return Configuration value for showing Out Of Stock Products
     *
     * @return bool
     */
    private function getIsFilterInStock()
    {
        return true;
        //review this part
        $showOutOfStock = $this->stockConfiguration->isShowOutOfStock();
        return $showOutOfStock ? false : true;
    }
}
