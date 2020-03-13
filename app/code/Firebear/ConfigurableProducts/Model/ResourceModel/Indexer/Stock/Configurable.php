<?php

namespace Firebear\ConfigurableProducts\Model\ResourceModel\Indexer\Stock;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Full;
use Magento\Framework\App\ObjectManager;

class Configurable extends \Magento\ConfigurableProduct\Model\ResourceModel\Indexer\Stock\Configurable
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param null $connectionName
     * @param \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher|null $activeTableSwitcher
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $connectionName = null,
        \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher = null
    ) {
        parent::__construct($context, $tableStrategy, $eavConfig, $scopeConfig, $connectionName);
        $this->activeTableSwitcher = $activeTableSwitcher ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher::class
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $connection = $this->getConnection();
        $table = $this->getActionType() === Full::ACTION_TYPE
            ? $this->activeTableSwitcher->getAdditionalTableName($this->getMainTable())
            : $this->getMainTable();
        $idxTable = $usePrimaryTable ? $table : $this->getIdxTable();
        $select = parent::_getStockStatusSelect($entityIds, $usePrimaryTable);
        $linkField = $metadata->getLinkField();
        $select->reset(
            \Magento\Framework\DB\Select::COLUMNS
        )->columns(
            ['e.entity_id', 'cis.website_id', 'cis.stock_id']
        )->joinLeft(
            ['link' => $this->getTable('catalog_product_super_link')],
            'link.parent_id = e.' . $linkField,
            []
        )->join(
            ['linken' => $this->getTable('catalog_product_entity')],
            'linken.entity_id = link.product_id',
            []
        )->joinInner(
            ['cpein' => $this->getTable('catalog_product_entity_int')],
            'linken.' . $linkField . ' = cpein.' . $linkField
            . ' AND cpein.attribute_id = ' . $this->_getAttribute('status')->getId()
            . ' AND cpein.value = ' . ProductStatus::STATUS_ENABLED,
            []
        )->joinLeft(
            ['itb' => $idxTable],
            'itb.product_id = link.product_id AND cis.website_id = itb.website_id AND cis.stock_id = itb.stock_id',
            []
        )->columns(
            ['qty' => new \Zend_Db_Expr('0')]
        );
        $statusExpr = $this->getStatusExpression($connection);

        $optExpr = $connection->getCheckSql("linken.required_options >= 0", 'itb.stock_status', 0);
        $stockStatusExpr = $connection->getLeastSql(["MAX({$optExpr})", "MIN({$statusExpr})"]);

        $select->columns(['status' => $stockStatusExpr]);

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        return $select;
    }
}