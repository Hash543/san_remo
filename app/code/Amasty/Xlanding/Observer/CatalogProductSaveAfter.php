<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Observer;

use Amasty\Xlanding\Model\Indexer\ProductPage;


class CatalogProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;


    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @inheritdoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $indexer = $this->indexerRegistry->get(ProductPage::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $product = $observer->getEvent()->getProduct();
            $indexer->reindexRow($product->getId());
        }
        return $this;
    }
}
