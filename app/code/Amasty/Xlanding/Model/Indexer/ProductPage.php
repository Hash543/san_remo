<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model\Indexer;

class ProductPage extends \Amasty\Xlanding\Model\Indexer\AbstractIndexer
{
    const INDEXER_ID = 'amasty_xlanding_product_page';

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doExecuteRow($id)
    {
        $this->getIndexBuilder()->reindexByProductIds([$id]);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doExecuteList($ids)
    {
        $this->getIndexBuilder()->reindexByProductIds($ids);
        $this->getCacheContext()->registerTags($this->getIdentities());
    }
}
