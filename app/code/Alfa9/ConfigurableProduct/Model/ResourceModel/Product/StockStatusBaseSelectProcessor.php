<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;

class StockStatusBaseSelectProcessor implements BaseSelectProcessorInterface{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @var StockStatusResource
     */
    private $stockStatusResource;

    /**
     * @param StockConfigurationInterface $stockConfig
     * @param StockStatusResource $stockStatusResource
     */
    public function __construct(
        StockConfigurationInterface $stockConfig,
        StockStatusResource $stockStatusResource
    ) {
        $this->stockConfig = $stockConfig;
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Select $select)
    {
        if (!$this->stockConfig->isShowOutOfStock()) {
            $select->joinInner(
                ['stock_child' => $this->stockStatusResource->getMainTable()],
                sprintf(
                    'stock_child.product_id = %s.entity_id',
                    BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS
                ),
                []
            )->where(
                'stock_child.stock_status = ?',
                StockStatus::STATUS_IN_STOCK
            );
        }
        return $select;
    }
}