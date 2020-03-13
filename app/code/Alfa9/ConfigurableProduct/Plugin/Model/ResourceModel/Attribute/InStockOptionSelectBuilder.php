<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute\InStockOptionSelectBuilder as CoreInStockOptionSelectBuilder;
use Magento\Framework\DB\Select;
/**
 * Class InStockOptionSelectBuilder
 * @package Alfa9\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute
 */
class InStockOptionSelectBuilder extends CoreInStockOptionSelectBuilder {
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * InStockOptionSelectBuilder constructor
     *
     * @param Status $stockStatusResource
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        Status $stockStatusResource,
        StockConfigurationInterface $stockConfiguration
    ) {
        parent::__construct($stockStatusResource);
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Only Add In stock Filter if Show Out Of Stock Products is set to No
     *
     * @param OptionSelectBuilderInterface $subject
     * @param Select $select
     * @return Select
     */
    public function afterGetSelect(
        OptionSelectBuilderInterface $subject,
        Select $select
    ) {
        //check this
        /*if (!$this->stockConfiguration->isShowOutOfStock()) {
            return parent::afterGetSelect($subject, $select);
        }*/
        return $select;
    }
}